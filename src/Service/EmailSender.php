<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailSender
{
  public function __construct(
    private ParameterBagInterface $parameterBag,
    private MailerInterface $mailer
  ){}


    public function makeAndSendEmail(string $to, string $subject, string $template, array $context = [], string $attachment = null)
    {
      $email = (new TemplatedEmail())
      ->from(new Address($this->parameterBag->get('email'), 'cinephoria'))
      ->to($to)
      ->subject($subject)
      ->htmlTemplate($template)
      ->context($context)
      ->attachFromPath('assets/logos/cinephoria_logo.png')
      ->attach($attachment, 'tickets.pdf', 'application/pdf');
      ;
      try {
        $this->mailer->send($email);
      } catch (\Throwable $th) {
          return $th->getMessage();
      }
    }

}