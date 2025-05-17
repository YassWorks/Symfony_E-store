<?php

namespace App\MailModule\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment as TwigEnvironment;


class MailService implements MailServiceInterface
{
    private MailerInterface $mailer;
    private TwigEnvironment $twig;
    private string $fromAddress;

    public function __construct(MailerInterface $mailer, TwigEnvironment $twig, string $fromAddress)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->fromAddress = $fromAddress;
    }

    public function send(string $to, string $subject, string $template, array $context = []): void
    {
        // $template should be relative to templates/ directory, e.g. 'emails/registration.html.twig'
        $body = $this->twig->render($template, $context);

        $email = (new Email())
            ->from($this->fromAddress)
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }
}

?>