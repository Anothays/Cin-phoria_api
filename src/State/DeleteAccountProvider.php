<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class DeleteAccountProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?User
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('Vous devez être connecté pour effectuer cette action.');
        }

        return $user;
    }
}
