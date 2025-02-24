<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em, 
    ) {}

    // #[IsGranted('ROLE_USER')]
    #[Route('/api/test', name: 'app_test')]
    public function index(): Response
    {
        $lol = file_get_contents('sessionCheckout');
        dd(unserialize($lol));
        // $reservationRepo = $this->em->getRepository(Reservation::class);
        return $this->json([
            'hello'=>'world',
            // 'user' => $user->getFullName()
        ]);
    }

}
