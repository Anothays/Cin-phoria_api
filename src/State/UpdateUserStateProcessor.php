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
     * @param User $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {

        if ($operation instanceof Patch) {

            /** @var User $currentUser */
            $currentUser = $this->security->getUser();
            
            // Vérifier que l'utilisateur modifie son propre profil
            if ($currentUser->getId() !== $data->getId()) {
                throw new AccessDeniedHttpException('Vous ne pouvez pas modifier les informations d\'un autre utilisateur.');
            }

            // Seuls le prénom et le nom peuvent être modifiés
            $allowedFields = ['firstname', 'lastname'];
            $requestData = json_decode($context['request']->getContent(), true);

            $data->setFirstname($requestData['firstname']);
            $data->setLastname($requestData['lastname']);

            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        return $data;
    }
} 