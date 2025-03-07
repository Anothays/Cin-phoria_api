<?php

namespace App\Tests;

use App\Service\StripePayment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use \Symfony\Component\BrowserKit\AbstractBrowser;

/**
 * Load before testing otherwise it would be failed ===> php bin/console d:f:l --env=test
 */
class StripePaymentTest extends KernelTestCase
{

    /** @var AbstractDatabaseTool */
    private $databaseTool;
    private $parameterBag;
    /** @var AbstractBrowser $client  */
    private $client;
    private $container;
    /** @var EntityManagerInterface $em */
    private $em;
    /** @var DocumentManager $em */
    private DocumentManager $dm;
    /** @var StripePayment $stripe */
    private StripePayment $stripe;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = static::getContainer();
        $this->databaseTool = $this->container->get(DatabaseToolCollection::class)->get();
        $this->parameterBag = $this->container->get(ParameterBagInterface::class);
        $this->em = $this->container->get('doctrine')->getManager();
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->stripe = new StripePayment($this->parameterBag->get('stripe_secret_key'), $this->parameterBag->get('stripe_secret_webhook'), $this->em, $this->dm);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }
}
