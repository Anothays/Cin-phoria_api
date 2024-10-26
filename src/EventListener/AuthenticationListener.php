<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class AuthenticationListener
{



    public function __construct(private SerializerInterface $serializer, private EntityManagerInterface $em) {}

    #[AsEventListener(event: AuthenticationSuccessEvent::class)]
    public function onAuthenticationSuccessEvent(AuthenticationSuccessEvent $event): void
    {   
        $userEmail = $event->getUser()->getUserIdentifier();
        $query = $this->em->createQueryBuilder()
        ->select('u.isVerified')
        ->from(User::class, 'u')
        ->where('u.email = :email')
        ->setParameter('email', $userEmail)
        ->getQuery();

        $isVerified = $query->getSingleScalarResult();

        if (!$isVerified) {
            throw new \Exception("Vous n'avez pas confirmé votre email.");
        }


        $user = $this->serializer->serialize($event->getUser(), 'jsonld', ['groups' => ['user']]);

        $userDataArray = json_decode($user, true);
        
        // dump($userDataArray);
        $event->setData(['user' => $userDataArray, ...$event->getData() ]);
    }
}
