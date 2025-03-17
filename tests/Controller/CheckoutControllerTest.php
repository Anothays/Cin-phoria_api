<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\AppFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use \Symfony\Component\BrowserKit\AbstractBrowser;

/**
 * Load before testing otherwise it would be failed ===> php bin/console d:f:l --env=test
 */
class CheckoutControllerTest extends ApiTestCase
{

    /** @var AbstractDatabaseTool */
    private $databaseTool;
    private $parameterBag;
    /** @var AbstractBrowser $client  */
    private $client;
    private $container;
    /** @var EntityManagerInterface $em */
    private $em;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->container = static::getContainer();
        $this->databaseTool = $this->container->get(DatabaseToolCollection::class)->get();
        $this->parameterBag = $this->container->get(ParameterBagInterface::class);
        $this->em = $this->container->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }

    // LOGIN USER AND RETURN ACCESS_TOKEN
    public function loginUserCustomer()
    {
        $rootDirPath = $this->parameterBag->get("root_dir");
        $userCredentials = json_decode(file_get_contents($rootDirPath . '/src/DataFixtures/users.json'), true);
        if (empty($userCredentials)) return;
        $user = $userCredentials[0];
        $this->client->request(
            'POST', 
            "/api/login_check", 
            ['json' =>  ["username" => $user['email'], "password" => $user['password']]]);
        $response = $this->client->getResponse()->toArray();
        $this->assertResponseIsSuccessful("User is authenticated");
        $this->assertArrayHasKey('token', $response);
        return $response['token'];
    }

    // public function testCheckoutController()
    // {
    //     $this->databaseTool->loadFixtures([AppFixtures::class]);
    //     $accessToken = $this->loginUserCustomer();   
    //     $reservations = $this->em->createQuery("select r from App\Entity\Reservation r where r.isPaid = false")->getResult();
    //     $ticketCategories = $this->em->createQuery("select c from App\Entity\TicketCategory c")->getResult();
    //     $ticketCategory = $ticketCategories[0];
    //     $timeout = (new \DateTime())->modify("-5 minutes");
    //     foreach ($reservations as $reservation) {
    //         $reservationId = $reservation->getId();
    //         $ticketCount = $reservation->getSeats()->count();
    //         $payload = [
    //             'reservationId' => $reservationId, 
    //             'tickets' => [[ 
    //                 "id" => $ticketCategory->getId(), 
    //                 "category" => $ticketCategory->getCategoryName(), 
    //                 'count' => $ticketCount
    //             ]]
    //         ];
    //         $this->client->request('POST', "/api/reservations/checkout/{$reservationId}", [
    //             'json' => $payload,
    //             'headers' => [
    //                 'Content-Type' => 'application/ld+json',
    //                 'Authorization' => "Bearer {$accessToken}"
    //                 ]
    //             ]);
    //         if ($reservation->getCreatedAt() > $timeout) {
    //             $this->assertResponseIsSuccessful();        
    //             $content = json_decode($this->client->getResponse()->getContent(), true);
    //             $this->assertIsString($content['url']);
    //         } else {
    //             $statusCode = $this->getClient()->getResponse()->getStatusCode();
    //             $this->assertTrue($statusCode >= 400 && $statusCode < 500);
    //         }
    //     }
    // }
}
