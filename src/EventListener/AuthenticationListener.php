<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\UserStaff;
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
        $user = $event->getUser();
        // Check if email is verified for customer user
        if ($user instanceof User) {
            $userEmail = $user->getUserIdentifier();
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
        }
        
        
        $userData = $this->serializer->serialize($user, 'jsonld', ['groups' => ['user']]);
        $userDataArray = json_decode($userData, true);
        
        $event->setData(['user' => $userDataArray, ...$event->getData() ]);

    }
}
