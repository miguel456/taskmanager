<?php

namespace App\Core\Mail\Drivers;

use App\Core\Spec\MailerDriver;

class NullMailDriver implements MailerDriver
{
    protected ?string $senderEmail = null;
    protected ?string $senderName = null;
    protected ?string $receiverEmail = null;
    protected ?string $receiverName = null;
    protected ?string $subject = null;
    protected ?string $body = null;
    protected bool $isHtml = false;
    protected array $cc = [];
    protected array $bcc = [];
    protected array $headers = [];

    public function supports()
    {
        return [
            'driver' => 'null_mail',
            'description' => 'Não envia emails. Regista todos os emails num ficheiro logs/mail.log.',
            'supported_os' => ['Any'],
        ];
    }

    public function setSender(string $email, ?string $name = null): MailerDriver
    {
        $this->senderEmail = $email;
        $this->senderName = $name;
        return $this;
    }

    public function setReceiver(string $email, ?string $name = null): MailerDriver
    {
        $this->receiverEmail = $email;
        $this->receiverName = $name;
        return $this;
    }

    public function setContent(string $subject, string $body, bool $isHtml = false): MailerDriver
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->isHtml = $isHtml;
        return $this;
    }

    public function setCc(array $emails): MailerDriver
    {
        $this->cc = $emails;
        return $this;
    }

    public function setBcc(array $emails): MailerDriver
    {
        $this->bcc = $emails;
        return $this;
    }

    public function setHeaders(array $headers): MailerDriver
    {
        $this->headers = $headers;
        return $this;
    }

    public function send(): bool
    {
        $logEntry = [
            'timestamp' => date('c'),
            'from' => $this->senderName
                ? "{$this->senderName} <{$this->senderEmail}>"
                : $this->senderEmail,
            'to' => $this->receiverName
                ? "{$this->receiverName} <{$this->receiverEmail}>"
                : $this->receiverEmail,
            'subject' => $this->subject,
            'body' => $this->body,
            'is_html' => $this->isHtml,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'headers' => $this->headers,
        ];

        $logDir = dirname(__DIR__, 3) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/mail.log';
        file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

        return true;
    }
}