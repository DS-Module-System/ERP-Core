<?php

namespace App\Service\Core;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailService
{

    public function __construct(private readonly MailerInterface $mailer, private readonly KernelInterface $kernel) {}

    /**
     * @param Address[] $addresses
     *
     * @throws \Exception
     */
    private function checkEmailDomain(array $addresses): bool
    {
        foreach ($addresses as $toEmailAddress) {
            [$name, $domain] = explode('@', (string) $toEmailAddress->getAddress(), 2);
            if ($domain !== 'devscale.bg') {
                throw new \Exception('ima razlichen email ot dsmodulesystem.com');
            }
        }

        return true;
    }

    public function sendSafeMail(Email $email): void
    {

        if ($this->kernel->getEnvironment() == 'dev') {
            $this->checkEmailDomain($email->getTo());
            $this->checkEmailDomain($email->getCc());
            $this->checkEmailDomain($email->getBcc());
        }

        $this->mailer->send($email);
    }
}
