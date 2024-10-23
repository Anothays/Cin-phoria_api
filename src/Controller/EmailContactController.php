<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class EmailContactController extends AbstractController
{

    public function __construct(private MailerInterface $mailer, private ParameterBagInterface $parameterBag) {}

    #[Route('/email-contact', name: 'app_email_contact', methods:['POST'])]
    public function index(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);
        
        if (!isset($content['username'], $content['email'], $content['message'], $content['object'])) {
            return $this->json([
                "error" => "Les champs 'username', 'email' et 'message' sont requis."
            ], 400);
        }

        $username = htmlspecialchars($content['username']);
        $emailAdress = htmlspecialchars($content['email']);
        $message = htmlspecialchars($content['message']);
        $object = htmlspecialchars($content['object']);

        $email = (new Email())
        ->from($this->parameterBag->get('email'))
        ->to($this->parameterBag->get('email'))
        ->subject("[CONTACT] : {$object}")
        ->html("<p>$username</p><p>{$emailAdress}</p><p>$message</p>")
        ;

        // return $this->json([
        //     'username' => $username,
        //     'emailAdress' => $emailAdress,
        //     'message' => $message,
        //     'object' => $object,
        // ]);

        try {
            $this->mailer->send($email);
            return $this->json([
                'message' => "Votre message a bien été envoyé",
            ]);
    

        } catch (\Throwable $th) {
            return $this->json([
                "message" => "Erreur lors de l'envoi du message"
            ], 500);
        }
    }


    

    #[Route('/forgot-password', name:'app_forgot_password', methods: ['POST'])]
    public function resetPassword(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);
        $email = $content['email'];
         return $this->json([
            "message" => "Un lien de réinitialisation à été envoyé à l'adresse : {$email}",
        ]);
    }
}
