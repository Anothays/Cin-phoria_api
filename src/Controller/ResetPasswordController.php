<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBagInterface
    ) {
    }

    /**
     * Display & process form to request a password reset.
     */
    #[Route('', name: 'app_forgot_password_request', methods: ['POST'])]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $data = json_decode($request->getContent(), true);

        return $this->processSendingPasswordResetEmail(
            $data['email'],
            $mailer,
            $translator
        );

    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, string $token = null): Response
    {
        $data = json_decode($request->getContent(), true);
        $newPassword = $data['password'];
        $confirmPassword = $data['confirmPassword'];
        if ($newPassword !== $confirmPassword) throw new Exception('Les mots de passe ne correspondent pas', 400);

        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(['message' => $e->getReason()]);
        }

        // The token is valid; allow the user to change their password.
        // A password reset token should be used only once, remove it.
        $this->resetPasswordHelper->removeResetRequest($token);
        // Encode(hash) the plain password, and set it.
        $encodedPassword = $passwordHasher->hashPassword(
            $user,
            $newPassword
        );
        $user->setPassword($encodedPassword);
        $this->entityManager->flush();
        // The session is cleaned up after the password has been changed.
        $this->cleanSessionAfterReset();
        return $this->json(['message' => "Mot de passe réinitialisé avec succès"]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->json(['message' => "Un lien de réinitialisation à été envoyé à l'adresse : {$emailFormData}"]);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(['message' => "Un lien de réinitialisation à été envoyé à l'adresse : {$emailFormData}"]);
            // return $this->json(['message' => $e->getReason()]);
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->parameterBagInterface->get('email'), 'cinephoria'))
            // ->to($user->getEmail())
            ->to('jeremy.snnk@gmail.com')
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('email/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->json(['message' => "Un lien de réinitialisation à été envoyé à l'adresse : {$emailFormData}"]);
        // return $this->redirectToRoute('app_check_email');
    }
}
