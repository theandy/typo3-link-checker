<?php

namespace LinkChecker\Infrastructure;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use LinkChecker\Config\Config;

class Mailer
{

    private array $config;
    private Logger $logger;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config->get('mail');
        $this->logger = $logger;
    }

    public function send(array $pages): void
    {

        try {

            $mail = new PHPMailer(true);

            /*
             * SMTP Debug ins Log
             */

            $mail->SMTPDebug = 2;

            $mail->Debugoutput = function ($str) {
                $this->logger->log("SMTP: " . trim($str));
            };

            /*
             * SMTP Konfiguration
             */

            $smtp = $this->config['smtp'];

            $mail->isSMTP();
            $mail->Host = $smtp['host'];
            $mail->Port = $smtp['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp['username'];
            $mail->Password = $smtp['password'];

            if (!empty($smtp['encryption'])) {
                $mail->SMTPSecure = $smtp['encryption'];
            }

            $this->logger->log("SMTP server: " . $smtp['host'] . ":" . $smtp['port']);
            $this->logger->log("SMTP user: " . $smtp['username']);

            /*
             * Absender
             */

            $mail->setFrom(
                $this->config['from']['address'],
                $this->config['from']['name']
            );

            $this->logger->log(
                "Mail from: " .
                $this->config['from']['address'] .
                " (" .
                $this->config['from']['name'] .
                ")"
            );

            /*
             * Empfänger
             */

            $mail->addAddress($this->config['to']);

            $this->logger->log("Mail to: " . $this->config['to']);

            /*
             * Inhalt
             */

            $mail->Subject = 'TYPO3 Navigation Fehler gefunden';

            $body = "Folgende Seiten enthalten leere navigation-link-href:\n\n";

            foreach ($pages as $p) {
                $body .= $p . "\n";
            }

            $mail->Body = $body;

            $mail->send();

            $this->logger->log("Mail successfully sent");

        } catch (Exception $e) {

            $this->logger->log("MAIL ERROR: " . $e->getMessage());

        }

    }

}