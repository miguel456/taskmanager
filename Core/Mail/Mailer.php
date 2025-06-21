<?php

namespace App\Core\Mail;

use App\Core\Spec\MailerDriver;
use App\Models\Users\User;
use Exception;
use LogicException;

class Mailer
{
    protected MailerDriver $driver;
    protected ?string $defaultSenderEmail = null;
    protected ?string $defaultSenderName = null;

    public function __construct(MailerDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Define os parâmetros padrão do remetente.
     */
    public function setSender(string $email, ?string $name = null): self
    {
        $this->defaultSenderEmail = $email;
        $this->defaultSenderName = $name;
        return $this;
    }

    /**
     * Envia email para o utilizador.
     * @param int|string $userIdOrEmail O ID ou email do utilizador. Apenas pode ser um ao mesmo tempo!
     * @param string $subject
     * @param string $body
     * @param bool $isHtml
     * @param array $cc
     * @param array $bcc
     * @param array $headers
     * @return bool
     * @throws Exception
     */
    public function sendToUser(
        int|string $userIdOrEmail,
        string $subject,
        string $body,
        bool $isHtml = false,
        array $cc = [],
        array $bcc = [],
        array $headers = []
    ): bool {
        $userModel = new User();

        if (is_int($userIdOrEmail)) {
            $userData = $userModel->getUserById($userIdOrEmail);
        } else {
            $userData = $userModel->getUser($userIdOrEmail);
        }

        if (empty($userData) || empty($userData['email'])) {
            throw new LogicException('Utilizador não encontrado!');
        }

        $senderEmail = $this->defaultSenderEmail ?? 'no-reply@projectary.io';
        $senderName = $this->defaultSenderName ?? null;

        $this->driver
            ->setSender($senderEmail, $senderName)
            ->setReceiver($userData['email'], $userData['nome'] ?? null)
            ->setContent($subject, $body, $isHtml)
            ->setCc($cc)
            ->setBcc($bcc)
            ->setHeaders($headers);

        // Verificar se o driver suporta enviar diretamente
        return method_exists($this->driver, 'send')
            ? $this->driver->send()
            : false;
    }

    /**
     * Expõe o driver para utilização avançada.
     */
    public function getDriver(): MailerDriver
    {
        return $this->driver;
    }
}