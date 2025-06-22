<?php

namespace App\Core\Services;

use App\Core\Mail\Mailer;
use App\Core\Spec\MailerDriver;
use App\Models\Notification;
use App\Models\Tasks\Tasks\Task;
use App\Models\Users\User;

/**
 * A cola entre o serviço de email, notificações e utilizadores.
 * Utilizado por um serviço cron CLI ou webcron como backup.
 */
class NotificationService
{
    /**
     * @var Mailer O driver para enviar emails.
     */
    protected Mailer $robustMailer;

    /**
     * @var User Modelo do utilizador notificar
     */
    protected User $userModel;


    /**
     * @var array Dados do utilizador em questão. Tem de ser com duas variáveis devido ao design do modelo.
     */
    protected array $user;


    /**
     * @var bool Define se esta notificação deve ser entregue por e-mail também
     */
    private bool $mailable = false;


    /**
     * @var bool Define se o utilizador pode marcar como lida
     */
    private bool $dismissable = true;


    /**
     * @var string O título e a mensagem da notificação
     */
    private string $title, $message;

    public function __construct()
    {
        $this->decideDriver();
        $this->userModel = new User();
    }

    /**
     * @param int $userId ID do utilizador a notificar.
     */
    public function setUser(int $userId): NotificationService
    {
        $user = $this->userModel->getUserById($userId);

        if (!empty($user)) {
            $this->user = $user;
            return $this;
        }

        throw new \InvalidArgumentException('O utilizador não existe.');
    }

    /**
     * @return array
     */
    public function getUser(): array
    {
        return $this->user;
    }

    /**
     * Define se o utilizador pode ignorar a notificação e marcá-la como lida
     * @param bool $dismissable
     * @return NotificationService
     */
    public function setDismissable(bool $dismissable): NotificationService
    {
        $this->dismissable = $dismissable;
        return $this;
    }

    /**
     * Define se esta notificação pode ser enviada como email
     * @param NotificationService $mailable
     */
    public function setMailable(bool $mailable): NotificationService
    {
        $this->mailable = $mailable;
        return $this;
    }


    /**
     * @param string $message
     * @return NotificationService
     */
    public function setMessage(string $message): NotificationService
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param string $title
     * @return NotificationService
     */
    public function setTitle(string $title): NotificationService
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDismissable(): bool
    {
        return $this->dismissable;
    }

    /**
     * @return bool
     */
    public function isMailable(): bool
    {
        return $this->mailable;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Decide o driver a utilizar com base na configuração fornecida.
     * @return void
     */
    protected function decideDriver(): void
    {
        $driver = config('Driver', 'mail');

        if (class_exists($driver)) {
            $this->robustMailer = new Mailer(new $driver);
        } else {
            throw new \LogicException('Driver ou configuração inválida. Verifique a configuração app.ini e forneça um Driver::class válido');
        }
    }

    /**
     * Processa a notificação construída.
     * Atenção: não utilize isto para enviar emails que não sejam notificações; isto é só para criar notificações e para enviá-las por email.
     * @see Mailer Utilize o Mailer diretamente para enviar outros emails não relacionados.
     * @return bool Resultado da operação
     * @throws \Exception
     */
    public function notify(bool $forTask = false, int $taskId = 0): bool
    {
        // algo que repete os dados já na tabela de notifs, mas pode ser útil para outros serviços de notifs no futuro
        $notificationContent = [
            'title' => $this->getTitle(),
            'content' => $this->getMessage(),
            'ui-meta' => [
                'mailable' => $this->isMailable(),
                'dismissable' => $this->isDismissable(),
                'created_at' => new \DateTime('now')->format('Y-m-d H:i:s'),
                'updated_at ' => new \DateTime('now')->format('Y-m-d H:i:s')
            ]
        ];

        // Como esta operação é idempotente, temos de verificar se já existe uma notificação antes de a tentar introduzir
        if ($forTask && Notification::isTaskNotified($taskId)) {
            return false;
        }

        if ($forTask) {

            return new Notification(json_encode($notificationContent), $this->user['iduser'], $this->isMailable(), 'UNREAD', $taskId)->save();

        } else {

            $notif = new Notification(json_encode($notificationContent), $this->user['iduser'], $this->isMailable())->save();

        }

        if ($notif) {
            if ($this->isMailable()) {

                $subject = '[Projectary] Tem uma nova notificação à sua espera (' . $this->getTitle() . ')';
                $content = <<<HTML
            <!DOCTYPE html>
            <html lang="pt-PT">
            <head>
                <meta charset="UTF-8">
                <title>{$notificationContent['title']}</title>
            </head>
            <body>
                <h2>{$notificationContent['title']}</h2>
                <p>{$notificationContent['content']}</p>
                <p><a href="#">Inicie sessão para marcar como lida</a></p>
                <hr>
                <small>Notificaçõ do Projectary</small>
            </body>
            </html>
            HTML;

                return $this->robustMailer
                    ->setSender('example@projectary.com')
                    ->sendToUser($this->user['iduser'], $subject, $subject, true);
            }

            return true;
        }

        throw new \RuntimeException('Não foi possível processar a notificação.');
    }
}