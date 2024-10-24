<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier, private ParameterBagInterface $parameterBag)
    {
    }

    // #[Route('/register', name: 'app_register')]
    // public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    // {
    //     $user = new User();
    //     $form = $this->createForm(RegistrationFormType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         // encode the plain password
    //         $user->setPassword(
    //             $userPasswordHasher->hashPassword(
    //                 $user,
    //                 $form->get('plainPassword')->getData()
    //             )
    //         );

    //         $entityManager->persist($user);
    //         $entityManager->flush();

    //         // generate a signed url and email it to the user
    //         $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
    //             (new TemplatedEmail())
    //                 ->from(new Address('cinephoria@jeremysnnk.ovh', 'cinephoria'))
    //                 ->to($user->getEmail())
    //                 ->subject('Please Confirm your Email')
    //                 ->htmlTemplate('registration/confirmation_email.html.twig')
    //         );

    //         // do anything else you need here, like send an email

    //         return $security->login($user, 'json_login', 'login');
    //     }

    //     return $this->render('registration/register.html.twig', [
    //         'registrationForm' => $form,
    //     ]);
    // }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {

        $id = $request->query->get('id'); // retrieve the user id from the url

        if (null === $id) {
            $url = $this->parameterBag->get("base_url_front") . '/signup/error';
            return $this->redirect($url);
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            $url = $this->parameterBag->get("base_url_front") . '/signup/error';
            return $this->redirect($url);
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $url = $this->parameterBag->get("base_url_front") . '/signup/error';
            return $this->redirect($url);
        }

        $url = $this->parameterBag->get("base_url_front") . '/signup/success';
        return $this->redirect($url);
    }
}
