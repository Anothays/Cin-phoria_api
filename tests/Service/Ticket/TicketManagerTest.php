<?php

namespace App\Tests\Service\Ticket;

use App\DataFixtures\AppFixtures;
use App\Entity\Reservation;
use App\Entity\Ticket;
use App\Entity\TicketCategory;
use App\Exception\TicketCreationException;
use App\Service\Ticket\TicketManager;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TicketManagerTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private AbstractDatabaseTool $databaseTool;
    private ContainerInterface $container;
    private TicketManager $ticketManager;
    // private Reservation $reservation;
    // private ProjectionEvent $projectionEvent;
    // private Movie $movie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = static::getContainer();
        $this->databaseTool = $this->container->get(DatabaseToolCollection::class)->get();
        $this->em = $this->container->get(EntityManagerInterface::class);
        $this->ticketManager = new TicketManager($this->em);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }


    public function testCreateSqlTicketsFailed()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        $reservation = $this->em->getRepository(Reservation::class)->findOneBy(['isPaid' => false]);
        $this->assertNotNull($reservation);
        $allCategories = $this->em->getRepository(TicketCategory::class)->findAll();
        $clientSelectedCategories = [];

        // Make client selected categories count different from reservation seats count
        for ($i = 0; $i < $reservation->getSeats()->count() + 1; $i++) {
            $clientSelectedCategories[] = $allCategories[0]->getCategoryName();
        }

        // Execute test
        $this->expectException(TicketCreationException::class);
        $tickets = $this->ticketManager->createSqlTickets($reservation, $clientSelectedCategories);

    }


    public function testCreateSqlTicketsSuccess()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        $reservation = $this->em->getRepository(Reservation::class)->findOneBy(['isPaid' => false]);
        $this->assertNotNull($reservation);
        $allCategories = $this->em->getRepository(TicketCategory::class)->findAll();
        $clientSelectedCategories = [];

        // Make client selected categories count different from reservation seats count
        foreach ($reservation->getSeats() as $seat) {
            $clientSelectedCategories[] = $allCategories[0]->getCategoryName();
        }

        // Execute test
        $tickets = $this->ticketManager->createSqlTickets($reservation, $clientSelectedCategories);

        // dump($tickets);
        // Assertions
        $this->assertCount(count($reservation->getSeats()), $tickets);
        foreach ($tickets as $ticket) {
            $this->assertInstanceOf(Ticket::class, $ticket);
        }
    }
} 