<?php

namespace App\Core\Mail\Drivers;

use App\Core\Spec\MailerDriver;

class NativeMailDriver implements MailerDriver
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
            'driver' => 'native_mail',
            'description' => 'Utiliza a função mail() com o sendmail no Linux. Não suporta Windows.',
            'supported_os' => ['Linux', 'Unix', 'BSD', 'macOS'],
            'unsupported_os' => ['Windows'],
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
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            throw new \RuntimeException('Este driver não suporta Windows.');
        }

        $to = $this->receiverName
            ? "{$this->receiverName} <{$this->receiverEmail}>"
            : $this->receiverEmail;

        $headers = [];

        if ($this->senderEmail) {
            $from = $this->senderName
                ? "{$this->senderName} <{$this->senderEmail}>"
                : $this->senderEmail;
            $headers[] = "From: $from";
        }

        if (!empty($this->cc)) {
            $headers[] = 'Cc: ' . implode(', ', $this->cc);
        }

        if (!empty($this->bcc)) {
            $headers[] = 'Bcc: ' . implode(', ', $this->bcc);
        }

        if ($this->isHtml) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
        }

        foreach ($this->headers as $key => $value) {
            $headers[] = "$key: $value";
        }

        $headersString = implode("\r\n", $headers);

        return mail(
            $to,
            $this->subject ?? '',
            $this->body ?? '',
            $headersString
        );
    }
}