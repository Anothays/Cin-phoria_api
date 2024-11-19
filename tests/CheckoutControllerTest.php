<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\AppFixtures;
use App\Entity\ProjectionEvent;
use App\Entity\ProjectionFormat;
use App\Entity\ProjectionRoomSeat;
use App\Entity\Reservation;
use App\Entity\TicketCategory;
use App\Entity\User;
use App\Repository\ReservationRepository;
use App\Repository\TicketCategoryRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;


use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\once;

/**
 * Load before testing otherwise it would be failed ===> php bin/console d:f:l --env=test
 */
class CheckoutControllerTest extends ApiTestCase
{

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }

    public function testCheckoutController()
    {

        $client = static::createClient();
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);


        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $reservations = $em->createQuery("select r from App\Entity\Reservation r where r.isPaid = false")->getResult();        

        // if (empty($reservations)) return; // show yellow warning if return 
        $timeout = (new \DateTime())->modify("-5 minutes");

        foreach ($reservations as $reservation) {
            
            /** @var Reservation $reservation */

            $payload = ['reservationId' => $reservation->getId(), 'tickets' => [["category" => 'Tarif Normal', 'count' => 1]]];
            $client->request('POST', '/checkout', ['json' => $payload]);
            if ($reservation->getCreatedAt() > $timeout) {
                $this->assertResponseIsSuccessful();        
                $content = json_decode($client->getResponse()->getContent(), true);
                $this->assertIsString($content['url']);
            } else {
                $this->assertResponseStatusCodeSame(410);
            }
        }

    }

    // public function testCheckoutControllerSuccess()
    // {
    //     $client = static::createClient();
    //     $container = static::getContainer();

    //     /** @var EntityManagerInterface $em */
    //     $em = $container->get('doctrine')->getManager();

    //     $timeout = (new \DateTime())->modify("-5 minutes");
    //     /** @var Reservation $reservation */
    //     $reservations = $em->createQuery("select r from App\Entity\Reservation r where r.isPaid = false and r.createdAt > :timeout ")
    //     ->setParameter(':timeout', $timeout)
    //     ->getResult();        

    //     $payload = json_encode([
    //         'reservationId' => $reservations[0]->getId(),
    //         'tickets' => [["category" => 'Tarif Normal', 'count' => 1]]]
    //     );
        
    //     $client->request('POST', '/checkout', ['body' => $payload]);
    //     $this->assertResponseIsSuccessful();        
    //     $content = json_decode($client->getResponse()->getContent(), true);
    //     $this->assertIsString($content['url']);
    // }



    // public function testCheckoutControllerTimeout()
    // {
    //     $client = static::createClient();
    //     $container = static::getContainer();

    //     /** @var EntityManagerInterface $em */
    //     $em = $container->get('doctrine')->getManager();

    //     $timeout = (new \DateTime())->modify("-5 minutes");
    //     /** @var Reservation $reservation */
    //     $reservations = $em->createQuery("select r from App\Entity\Reservation r where r.isPaid = false and r.createdAt < :timeout ")
    //     ->setParameter(':timeout', $timeout)
    //     ->getResult();    
        
    //     $payload = json_encode([
    //         'reservationId' => $reservations[0]->getId(),
    //         'tickets' => [["category" => 'Tarif Normal', 'count' => 1]]
    //     ]);
        
    //     $client->request('POST', '/checkout', ['body' => $payload]);
    //     $this->assertResponseStatusCodeSame(410);
    // }












        // /** @var User $user */
        // $user = $em->find(User::class, 1);
        // /** @var ProjectionEvent $projection */
        // $projection = $em->find(ProjectionEvent::class, 424);
        // $seat = $projection->getAvailableSeats()[1];
        
        // $reservation = (new Reservation())
        // ->setCreatedAt(new \DateTimeImmutable())
        // ->setUser($user)
        // ->setProjectionEvent((new ProjectionEvent()))
        // ->addSeat($seat)
        // ->setCreatedAt(new \DateTimeImmutable())
        // ->setId(42);
        
        // $projection->addReservation($reservation);
        // $em->persist($reservation);
        // $em->persist($projection);
        // $em->flush();
        

    // public function testCheckout(): void
    // {

    //     $client = static::createClient();
    //     $container = static::getContainer();


    //     $mockedEntityManager = $this->createMock(EntityManagerInterface::class);
    //     $mockedReservationRepo = $this->createMock(ReservationRepository::class);
    //     $mockedReservation = $this->createMock(Reservation::class);
    //     $mockedTicketCategory = $this->createMock(TicketCategory::class);
    //     $mockedTicketCategoryRepo = $this->createMock(TicketCategoryRepository::class);
    //     $mockedProjectionEvent = $this->createMock(ProjectionEvent::class);
    //     $mockedProjectionFormat = $this->createMock(ProjectionFormat::class);


    //     // Configurez les comportements attendus
    //     $mockedEntityManager->expects($this->exactly(2))
    //     ->method('getRepository')
    //     ->willReturnCallback(fn($class) =>  match ($class) {
    //         Reservation::class => $mockedReservationRepo,
    //         TicketCategory::class => $mockedTicketCategoryRepo,
    //     });

    //     $mockedReservationRepo->expects($this->once())
    //     ->method('findOneBy')
    //     ->with(['id' => 42])
    //     ->willReturn($mockedReservation);

    //     $mockedReservation->expects($this->any())
    //     ->method('getProjectionEvent')
    //     ->willReturn($mockedProjectionEvent);

    //     $mockedTicketCategoryRepo->expects($this->any())
    //     ->method('findOneBy')
    //     ->with(['categoryName' => 'Tarif Normal'])
    //     ->willReturn($mockedTicketCategory);

    //     $mockedProjectionEvent->expects($this->once())
    //     ->method('getFormat')
    //     ->willReturn($mockedProjectionFormat);

    //     $mockedProjectionFormat->expects($this->once())
    //     ->method('getExtraCharge')
    //     ->willReturn(0);

    //     $mockedTicketCategory
    //     ->method('getCategoryName')
    //     ->willReturn('Tarif Normal');

    //     $mockedTicketCategory
    //     ->method('getPrice')
    //     ->willReturn(3000);

    //     $mockedReservation
    //     ->method('isPaid')
    //     ->willReturn(false);

    //     $mockedReservation
    //     ->method('getCreatedAt')
    //     ->willReturn(new \DateTimeImmutable());

        
    //     // 4. Injectez le mock dans le conteneur de tests
    //     $container->set(EntityManagerInterface::class, $mockedEntityManager);

        
        
    //     // Corps de la requête simulée
    //     $payload = json_encode([
    //         'reservationId' => 42,
    //         'tickets' => [
    //             ["category" => 'Tarif Normal', 'count' => 2]
    //         ]
    //     ]);
        
    //     $client->request('POST', '/checkout', [
    //         'body' => $payload
    //     ] );
        
    //     // 6. Vérifiez les réponses
    //     $this->assertResponseIsSuccessful();


    // }
}
