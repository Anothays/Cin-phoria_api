<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class AuthenticationListener
{

    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[AsEventListener(event: AuthenticationSuccessEvent::class)]
    public function onAuthenticationSuccessEvent(AuthenticationSuccessEvent $event): void
    {
        $user = $this->serializer->serialize($event->getUser(), 'jsonld', ['groups' => ['user']]);
        $userDataArray = json_decode($user, true);
        // dump($userDataArray);
        $event->setData(['user' => $userDataArray, ...$event->getData() ]);
    }
}
