<?php

namespace LinkChecker\Infrastructure;

use PHPMailer\PHPMailer\PHPMailer;
use LinkChecker\Config\Config;

class Mailer
{

    private array $config;
    private array $typo3Mail = [];

    public function __construct(Config $config)
    {
        $this->config = $config->get('mail');
        $this->typo3Mail = $this->loadTypo3MailConfig($config);
    }

    private function loadTypo3MailConfig(Config $config): array
    {

        $root = rtrim($config->get('typo3')['root_path'], '/');

        $possibleFiles = [

            $root . '/config/system/settings.php',           // TYPO3 v12+
            $root . '/typo3conf/system/settings.php',        // ältere composer installs
            $root . '/typo3conf/LocalConfiguration.php'      // klassische installs

        ];

        foreach ($possibleFiles as $file) {

            if (file_exists($file)) {

                $settings = require $file;

                if (isset($settings['MAIL'])) {
                    return $settings['MAIL'];
                }

            }

        }

        /*
         * Falls keine TYPO3 Config gefunden wurde,
         * einfach leere Konfiguration zurückgeben
         */

        return [];

    }

    public function send(array $pages): void
    {

        $mail = new PHPMailer(true);

        /*
         * SMTP Konfiguration aus TYPO3
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

            $mail->Host = $host;
            $mail->Port = (int)$port;

            $mail->SMTPAuth = true;
            $mail->Username = $this->typo3Mail['transport_smtp_username'] ?? '';
            $mail->Password = $this->typo3Mail['transport_smtp_password'] ?? '';
        }

        /*
         * Absender aus TYPO3
         */

        $mail->setFrom(
            $this->typo3Mail['defaultMailFromAddress'] ?? 'noreply@example.com',
            $this->typo3Mail['defaultMailFromName'] ?? 'TYPO3'
        );

        /*
         * Empfänger aus Tool Config
         */

        $mail->addAddress($this->config['to']);

        $mail->Subject = 'TYPO3 Navigation Fehler gefunden';

        $body = "Folgende Seiten enthalten leere navigation-link-href:\n\n";

        foreach ($pages as $p) {
            $body .= $p . "\n";
        }

        $mail->Body = $body;

        $mail->send();

    }

}