<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailConfirmationService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private MailerInterface $mailer,
        private string $secret
    ) {}

    public function sendConfirmationEmail(User $user): void
    {
        $userId = $user->getId();
        $expires = time() + 3600; // посилання дійсне 1 година
        $hash = hash_hmac('sha256', "$userId|$expires", $this->secret);

        $verifyUrl = $this->urlGenerator->generate('app_verify_email', [
            'id' => $userId,
            'expires' => $expires,
            'hash' => $hash,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Confirm your email')
            ->html("<p>Click to verify your email: <a href='$verifyUrl'>$verifyUrl</a></p>");

        $this->mailer->send($email);
    }
}
