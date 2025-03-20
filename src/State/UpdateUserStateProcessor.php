<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UpdateUserStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private Security $security
    ) {}

    /**
     * @param UpdateUserDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if ($operation instanceof Patch) {

            $currentUser->setFirstname($data->firstname);
            $currentUser->setLastname($data->lastname);

            return $this->persistProcessor->process($currentUser, $operation, $uriVariables, $context);
        }

        return $currentUser;
    }
} 