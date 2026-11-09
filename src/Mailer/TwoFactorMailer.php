<?php


namespace App\Mailer;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TwoFactorMailer implements AuthCodeMailerInterface
{

    public function __construct(private readonly MailerInterface $mailer)
    {
    }


    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $authCode = $user->getEmailAuthCode();

        $message = new Email();
        $message
            ->to($user->getEmailAuthRecipient())
            ->from($_ENV['NO_REPLY_MAIL'])
            ->subject('Authentication Code - ' . $_ENV['SITE_NAME'])
            ->html(
<<<EOD
<p>Здравейте, </p>
<p>Имейлът съдържа код, който трябва да го въведете в администратирния панел, за да се иденфицитирате.</p>

<p>Код: <span style="font-size: 16px; color: darkblue;"><strong>{$user->getEmailAuthCode()}</strong></span></p>

Поздрави, <br/>
{$_ENV['SITE_NAME']}
EOD
            );

        $this->mailer->send($message);

    }
}