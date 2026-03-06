<?php

namespace LinkChecker\Infrastructure;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use LinkChecker\Config\Config;

class Mailer
{

    private array $config;
    private array $typo3Mail = [];
    private Logger $logger;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config->get('mail');
        $this->logger = $logger;
        $this->typo3Mail = $this->loadTypo3MailConfig($config);
    }

    private function loadTypo3MailConfig(Config $config): array
    {

        $root = rtrim($config->get('typo3')['root_path'], '/');

        $possibleFiles = [
            $root . '/config/system/settings.php',
            $root . '/typo3conf/system/settings.php',
            $root . '/typo3conf/LocalConfiguration.php'
        ];

        foreach ($possibleFiles as $file) {

            if (file_exists($file)) {

                $this->logger->log("Loading TYPO3 mail config from: " . $file);

                $settings = require $file;

                if (isset($settings['MAIL'])) {
                    return $settings['MAIL'];
                }

            }

        }

        $this->logger->log("No TYPO3 mail configuration found.");

        return [];

    }

    public function send(array $pages): void
    {

        try {

            $mail = new PHPMailer(true);

            /*
             * Debug Output von PHPMailer ins Log
             */
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str) {
                $this->logger->log("SMTP: " . trim($str));
            };

            /*
             * SMTP Konfiguration
             */

            if (($this->typo3Mail['transport'] ?? '') === 'smtp') {

                $mail->isSMTP();

                $server = $this->typo3Mail['transport_smtp_server'] ?? '';

                if (strpos($server, ':') !== false) {
                    [$host, $port] = explode(':', $server);
                } else {
                    $host = $server;
                    $port = 25;
                }

                $this->logger->log("SMTP server: $host:$port");

                $mail->Host = $host;
                $mail->Port = (int)$port;

                $mail->SMTPAuth = true;
                $mail->Username = $this->typo3Mail['transport_smtp_username'] ?? '';
                $mail->Password = $this->typo3Mail['transport_smtp_password'] ?? '';

                $this->logger->log("SMTP user: " . ($mail->Username ?: 'none'));

            } else {

                $this->logger->log("Using PHP mail() transport");

            }

            /*
             * Absender
             */

            $fromAddress = $this->typo3Mail['defaultMailFromAddress'] ?? 'noreply@example.com';
            $fromName = $this->typo3Mail['defaultMailFromName'] ?? 'TYPO3';

            $this->logger->log("Mail from: $fromAddress ($fromName)");

            $mail->setFrom($fromAddress, $fromName);

            /*
             * Empfänger
             */

            $this->logger->log("Mail to: " . $this->config['to']);

            $mail->addAddress($this->config['to']);

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