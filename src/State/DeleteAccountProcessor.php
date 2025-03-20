<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
        
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        
        if (!$currentUser instanceof User) {
            throw new UnauthorizedHttpException('Vous devez être connecté pour effectuer cette action.');
        }

        if ($currentUser->getId() !== $data->getId()) {
            throw new AccessDeniedHttpException('Vous ne pouvez pas modifier les informations d\'un autre utilisateur.');
        }

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
