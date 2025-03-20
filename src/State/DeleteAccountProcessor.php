<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DeleteAccountProcessor implements ProcessorInterface
{

    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager
    ) {}


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {

        // Supprimer les relations si nécessaire
        foreach ($data->getReservations() as $reservation) {
            // $this->entityManager->remove($reservation);
            $reservation->setUser(null);
        }
        
        /** @var User $data */
        foreach ($data->getComments() as $comment) {
            $this->entityManager->remove($comment);
        }

        // Supprimer l'utilisateur
        $this->entityManager->remove($data);
        $this->entityManager->flush();

    }
}
