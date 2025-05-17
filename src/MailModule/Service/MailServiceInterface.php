<?php

namespace App\MailModule\Service;

interface MailServiceInterface
{
    /**
     * Send an email based on a Twig template
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $template Twig template path (e.g. 'emails/registration.html.twig')
     * @param array  $context Context variables for rendering
     *
     * @return void
     */
    public function send(string $to, string $subject, string $template, array $context = []): void;
}

?>