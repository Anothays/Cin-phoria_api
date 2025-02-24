<?php

namespace App\Controller;

use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use App\Constant\ErrorMessages;
use App\Entity\Reservation;
use App\Entity\TicketCategory;
use App\Entity\ProjectionEvent;
use App\Entity\User;
use App\Service\EmailSender;
use App\Service\PdfMaker;
use App\Service\StripePayment;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Stripe\Webhook;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;


class CheckoutController extends AbstractController
{

    public function __construct(
        private ParameterBagInterface $params, 
        private EntityManagerInterface $em, 
        private DocumentManager $dm, 
        private ParameterBagInterface $parameterBag,
        private EmailSender $emailSender,
        private PdfMaker $pdfMaker
    ) {}

    public function __invoke(Reservation $reservation, #[CurrentUser] User $user, Request $request)
    {
        try {
            // Check reservation owner
            if ($reservation->getUser() !== $user) 
                throw new AccessDeniedException(ErrorMessages::CURRENT_USER_IS_NOT_RESERVATION_OWNER);
    
            // Check if reservation is already paid
            if ($reservation->isPaid()) 
                throw new BadRequestException(ErrorMessages::RESERVATION_IS_ALREADY_PAID, 400);
    
            // Check reservation time
            $limitDate = new \DateTime();
            $limitDate->modify('-5 minutes');
            if ($reservation->getCreatedAt() < $limitDate) {
                $this->em->remove($reservation);
                $this->em->flush();
                throw new NotFoundHttpException(ErrorMessages::RESERVATION_TIMEOUT, null, 404);
            }
            
            $content = json_decode($request->getContent(), true);
            $tickets = $content["tickets"];
            $ticketsCount = array_reduce($tickets, fn ($total, $item) => $total += $item['count'], 0);
            $reservedSeat = $reservation->getSeats()->count();
    
            // Check reservedSeat match ticketsCount
            if ($reservedSeat === 0 || $reservedSeat !== $ticketsCount) 
                throw new BadRequestException(ErrorMessages::TICKETS_COUNT_DOES_NOT_MATCH_RESERVED_SEATS_COUNT, 400);
            
            /** @var ProjectionEvent $projectionEvent  */
            $projectionEvent =  $reservation->getProjectionEvent();
            $ticketCategoryRepo  = $this->em->getRepository(TicketCategory::class);
            $lineItems = [];
            foreach($tickets as $value) {
                $category = $ticketCategoryRepo->find($value['id']);
                if (!$category) throw new NotFoundHttpException(ErrorMessages::TICKET_CATEGORY_NOT_FOUND, null, 404);
                $extraCharge = $projectionEvent->getFormat()->getExtraCharge();
                if ($value['count'] <= 0) continue;
                array_push($lineItems, [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $category->getCategoryName(),
                        ],
                        'unit_amount' => $category->getPrice() + $extraCharge ?? 0, 
                    ],
                    'quantity' => $value['count'],
                ]);
            }
            
            // set Stripe secrets and create stripe checkout session
            $stripeSecretKey = $this->params->get('stripe_secret_key');
            \Stripe\Stripe::setApiKey($stripeSecretKey);
            $success_url = "{$this->params->get('base_url_front')}/payment_status/success?reservation_id={$reservation->getId()}";
            $checkout_session = \Stripe\Checkout\Session::create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $success_url,
                'metadata' => [
                    'movie_title' => $reservation->getProjectionEvent()->getMovie()->getTitle(),
                    'cinema_name' => $reservation->getProjectionEvent()->getMovieTheater()->getTheaterName(),
                    'room_number' => $reservation->getProjectionEvent()->getProjectionRoom(),
                    'session_date' => $reservation->getProjectionEvent()->getDate()->format('Y-m-d H:i:s'),
                    'reservation' => $reservation->getId(),
                ]
            ]);
            return $this->json(['url' => $checkout_session->url]);
        } catch (\Throwable $th) {
            return $this->json($th->getMessage(), $th->getCode());
        }
            
    }

    // #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    // public function index(Request $request)
    // {
        
    // }

    #[Route('/payment-webhook', name: 'payment_webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        
        $webhookSecret = $this->params->get('stripe_secret_webhook');
        $stripeApiKey = $this->params->get('stripe_secret_key');
        $stripe = new StripePayment($stripeApiKey, $webhookSecret, $this->em, $this->dm);
        
        try {
            $signature = $request->headers->get('stripe-signature');
            if (!$signature) throw new UnauthorizedHttpException("Error Processing Request", 1);
            $body = $request->getContent();
            $event = Webhook::constructEvent($body, $signature, $webhookSecret);
            if ($event->type === 'checkout.session.completed') {
                // Generate Tickets
                $data = $event->data->object;
                $result = $stripe->fulfillCheckout($data['id']);
                if (!$result) return new Response('Erreur dans la prodécure de réalisation', 500);
                // Send email with tickets
                $reservationId = $data->metadata['reservation'];
                $this->makeAndSendEmailFromReservation($reservationId);
                return new Response('Transaction et réalisation effectuée avec succès');
            }
        } catch (\Throwable $th) {
            return new Response('Erreur dans la prodécure de réalisation', 500);
        }
    }

    public function makeAndSendEmailFromReservation($reservationId)
    {
        /** @var Reservation $reservation */
        $reservation = $this->em->getRepository(Reservation::class)->find($reservationId);
        $to = $reservation->getUser()->getEmail();
        $subject = "Votre achat";
        $template = "email/email_tickets.html.twig";
        $context = [ 'resa' => $reservation ];
        $this->emailSender->makeAndSendEmail($to, $subject, $template, $context,  $this->pdfMaker->makeTicketsPdfFile($reservation) ?? null,);
    }

}
