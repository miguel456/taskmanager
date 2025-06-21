<?php

namespace App\Core\Spec;

interface MailerDriver
{
    /**
     * Describes the driver and the supported system architectures.
     * @return mixed
     */
    public function supports();

    /**
     * Sets the sender's email address and optional name.
     * @param string $email
     * @param string|null $name
     * @return self
     */
    public function setSender(string $email, ?string $name = null): self;

    /**
     * Sets the receiver's email address and optional name.
     * @param string $email
     * @param string|null $name
     * @return self
     */
    public function setReceiver(string $email, ?string $name = null): self;

    /**
     * Sets the message content.
     * @param string $subject
     * @param string $body
     * @param bool $isHtml
     * @return self
     */
    public function setContent(string $subject, string $body, bool $isHtml = false): self;

    /**
     * Sets the CC recipients.
     * @param array $emails
     * @return self
     */
    public function setCc(array $emails): self;

    /**
     * Sets the BCC recipients.
     * @param array $emails
     * @return self
     */
    public function setBcc(array $emails): self;

    /**
     * Sets additional headers.
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self;

    public function send();
}