<?php

namespace App\State;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\UserCreatedAccount;
use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;


class UserStateProcessor implements ProcessorInterface
{

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private MailerInterface $mailer,
        private UserPasswordHasherInterface $passwordHasher,
        private EmailVerifier $emailVerifier,
        private ParameterBagInterface $parameterBag,
        private UrlGeneratorInterface $urlGeneratorInterface,
    )
    {
    }

    /**
     * @param User $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation instanceof Post) {
            $hashedPasword = $this->passwordHasher->hashPassword($data, $data->getPassword());
            $data->setPassword($hashedPasword);
            $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);
            $this->sendEmailConfirmation($result);

            return $result;
        }
    }

    public function sendEmailConfirmation(User $user)
    {
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address($this->parameterBag->get('email'), 'cinephoria'))
                    ->to($user->getEmail())
                    ->subject('Lien de confirmation')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
    }
}
