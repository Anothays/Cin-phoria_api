<?php

namespace App\Tests\Service;

use App\DataFixtures\AppFixtures;
use App\Entity\Reservation;
use App\Entity\Ticket;
use App\Entity\TicketCategory;
use App\Exception\TicketCreationException;
use App\Repository\ReservationRepository;
use App\Service\StripePayment;
use App\Service\Ticket\TicketManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Stripe\Checkout\Session as StripeSession;


class StripePaymentTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private DocumentManager $dm;
    private AbstractDatabaseTool $databaseTool;
    private ContainerInterface $container;
    private TicketManager $ticketManager;
    private ParameterBagInterface $params;
    private ReservationRepository $reservationRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = static::getContainer();
        $this->databaseTool = $this->container->get(DatabaseToolCollection::class)->get();
        $this->em = $this->container->get(EntityManagerInterface::class);
        $this->dm = $this->container->get(DocumentManager::class);
        $this->ticketManager = new TicketManager($this->em);
        $this->params = $this->container->get(ParameterBagInterface::class);
        $this->reservationRepository = $this->container->get(ReservationRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }

    public function testFulfillCheckoutSuccess()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        $stripeClientMock = $this->getMockBuilder(StripePayment::class)
        ->setConstructorArgs([
            $this->container->get(ParameterBagInterface::class),
            $this->em,
            $this->dm,
            $this->ticketManager,
            $this->reservationRepository,
        ])
        ->onlyMethods(['validateStripeSession', 'extractTicketCategories', 'getReservation'])
        ->getMock();


        // Création d'un objet mock pour `metadata`
        $metadataMock = new \stdClass();
        $metadataMock->reservation = 'no_matter_value';
        
        $checkoutSessionMock = $this->getMockBuilder(StripeSession::class)
        ->disableOriginalConstructor()
        ->getMock();

        $checkoutSessionMock->method('__get')
        ->with('metadata')
        ->willReturn($metadataMock);        

        $reservation = $this->em->getRepository(Reservation::class)->findAll()[0];

        $categories = ['Tarif Normal'];

        $stripeClientMock->method('validateStripeSession')->willReturn($checkoutSessionMock);
        $stripeClientMock->method('extractTicketCategories')->willReturn($categories);
        $stripeClientMock->method('getReservation')->willReturn($reservation);
        

        $this->assertTrue($stripeClientMock->fulfillCheckout('no_matter_id'));
    }

}
