<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\TicketCategory;
use App\Repository\TicketCategoryRepository;
use App\Service\EmailSender;
use App\Service\PdfMaker;
use App\Service\StripePayment;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Component\Mime\Address;
use Stripe\Webhook;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Serializer;

class CheckoutController extends AbstractController
{

    public function __construct(
        private ParameterBagInterface $params, 
        private EntityManagerInterface $em, 
        private ParameterBagInterface $parameterBag,
        private EmailSender $emailSender,
        private PdfMaker $pdfMaker
        ) {}

    #[Route('/checkout', name: 'app_checkout', methods: ['POST', 'GET'])]
    public function index(Request $request)
    {
        
        $stripeSecretKey = $this->params->get('stripe_secret_key');
        \Stripe\Stripe::setApiKey($stripeSecretKey);
        
        $lineItems = [];
        $content = json_decode($request->getContent(), true);
        $ticketCategoryRepo  = $this->em->getRepository(TicketCategory::class);
        foreach($content['tickets'] as $value) {
            $category = $ticketCategoryRepo->findOneBy(["categoryName" => $value['category']]);
            if (!$category) throw new NotFoundHttpException("Aucune categorie " . $value['category'] . " trouvée");
            if ($value['count'] <= 0) continue;
            array_push($lineItems, [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $category->getCategoryName(),
                    ],
                    'unit_amount' => $category->getPrice(), 
                ],
                'quantity' => $value['count'],
            ]);
        }
        
        $reservationRepo = $this->em->getRepository(Reservation::class);
        $reservation = $reservationRepo->findOneBy(['id' => $content['reservationId']]);
        if (!$reservation) throw new NotFoundHttpException("Aucune réservation trouvée");
        
        // Check timeout ==> 5 minutes reached
        $limitDate = new \DateTime();
        $limitDate->modify('-5 minutes');
        if (!$reservation->isPaid() && $reservation->getCreatedAt() < $limitDate) {
            $this->em->remove($reservation);
            $this->em->flush();
            return $this->json(["message" => 'Votre réservation a été supprimée car vous avez dépassé les 5 minutes'], 410);
        }
        
        $metadata = [
            'movie_title' => $reservation->getProjectionEvent()->getMovie()->getTitle(),
            'cinema_name' => $reservation->getProjectionEvent()->getMovieTheater()->getTheaterName(),
            'room_number' => $reservation->getProjectionEvent()->getProjectionRoom(),
            'session_date' => $reservation->getProjectionEvent()->getDate()->format('Y-m-d H:i:s'),
            'reservation' => $reservation->getId(),
        ];

        $checkout_session = \Stripe\Checkout\Session::create([
            // 'line_items' => [[
            //   # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
            //   'price' => 'price_1PDUreB43NRM64kP8HamCA6n',
            //   'quantity' => 1,
            // ]],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => "http://localhost:3000/payment_status/true",
            'cancel_url' => "http://localhost:3000/payment_status/false",
            'metadata' => $metadata,
        ]);

        return $this->json(['url' => $checkout_session->url]);
        // return $this->redirect($checkout_session->url, 303);
    }

    #[Route('/payment-webhook', name: 'payment_webhook', methods: ['GET', 'POST'])]
    public function webhook(Request $request): Response
    {
        $webhookSecret = $this->params->get('stripe_secret_webhook');
        $stripeApiKey = $this->params->get('stripe_secret_key');
        $stripe = new StripePayment($stripeApiKey, $webhookSecret, $this->em);
        
        try {
            $signature = $request->headers->get('stripe-signature');
            $body = $request->getContent();
            $event = Webhook::constructEvent($body, $signature, $webhookSecret);
            if ($event->type === 'checkout.session.completed') {
                
                $data = $event->data->object;
                // $result = $stripe->fulfill_checkout($data['id']);
                // if (!$result) return new Response('Erreur dans la prodécure de réalisation', 500);
                
                // //ENVOYER BILLETS PAR EMAIL
                // $reservationId = $data->metadata['reservation'];
                // /** @var Reservation $reservation */
                // $reservation = $this->em->getRepository(Reservation::class)->find($reservationId);
                // $to = "jeremy.snnk@gmail.com";
                // // $to = $reservation->getUser()->getEmail();
                // $subject = "Votre achat";
                // $template = "email/email_tickets.html.twig";
                // $context = [
                //     'projection' => $reservation->getProjectionEvent(),
                //     'movie' => $reservation->getProjectionEvent()->getMovie(),
                //     'tickets' => $reservation->getTickets(),
                // ];
                // // $this->emailSender->makeAndSendEmail($to, $subject, $template, $context);
                $this->pdfMaker->makePdfFile();
                

                return new Response('Transaction et réalisation effectuée avec succès');
            }
        } catch (\Throwable $th) {
            dump($th);
            return new Response('Erreur dans la prodécure de réalisation', 500);
        }
    }

    #[Route('/payment', name: 'payment', methods: ['GET', 'POST'])]
    public function payment(Request $request)
    {
        return $this->json(["success" => $request->getQueryString()]);
    }

}
