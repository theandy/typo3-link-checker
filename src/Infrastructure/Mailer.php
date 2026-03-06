<?php

namespace LinkChecker\Infrastructure;

use PHPMailer\PHPMailer\PHPMailer;
use LinkChecker\Config\Config;

class Mailer
{

    private array $mailConfig;

    public function __construct(Config $config)
    {
        $this->mailConfig = $config->get('mail');
    }

    public function send(array $pages): void
    {

        $mail = new PHPMailer();

        $mail->setFrom($this->mailConfig['from']);

        $mail->addAddress($this->mailConfig['to']);

        $mail->Subject = 'TYPO3 Navigation Fehler';

        $body = "Folgende Seiten enthalten leere Navigation Links:\n\n";

        foreach ($pages as $p) {
            $body .= $p . "\n";
        }

        $mail->Body = $body;

        $mail->send();

    }

}