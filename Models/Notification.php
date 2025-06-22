<?php

namespace App\Models;

use App\Core\Database\DataLayer;
use App\Models\Users\User;
use DateTime;
use Exception;
use PDO;

class Notification
{
    private ?int $id;
    private string $content;
    private int $notifyee;
    private bool $mailable;
    private string $status;

    private ?int $task = null;
    private bool $sent = false;
    private ?DateTime $sentAt = null;
    private ?DateTime $scheduledAt = null;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    private PDO $conn;

    protected array $fillable = [
        'content',
        'notifyee',
        'mailable',
        'status',
        'task',
        'sent',
        'sent_at',
        'scheduled_at'
    ];

    protected array $dirty = [];

    protected array $loadRelationships = [
        'notifyeeUser' => 'getForeignNotifyee'
    ];

    private const array STATUSES = ['READ', 'UNREAD'];

    public function __construct(
        string $content,
        int $notifyee,
        bool $mailable = false,
        string $status = 'UNREAD',
        ?int $task = null,
        bool $sent = false,
        ?DateTime $sentAt = null,
        ?DateTime $scheduledAt = null
    ) {
        $this->setContent($content);
        $this->setNotifyee($notifyee);
        $this->setMailable($mailable);
        $this->setStatus($status);
        $this->setTask($task);
        $this->setSent($sent);
        $this->setSentAt($sentAt);
        $this->setScheduledAt($scheduledAt);
        $now = new DateTime();
        $this->setCreatedAt($now);
        $this->setUpdatedAt($now);
        $this->conn = DataLayer::getConnection();
    }

    protected function setDirty(string $field): void
    {
        if (in_array($field, $this->fillable) && !in_array($field, $this->dirty)) {
            $this->dirty[] = $field;
        }
    }

    public function getId(): ?int { return $this->id; }
    protected function setId(int $id): Notification { $this->id = $id; return $this; }

    public function getContent(): string { return base64_decode($this->content); }
    public function setContent(string $content): Notification {
        if (empty(trim($content))) {
            throw new \InvalidArgumentException('Conteúdo não pode estar vazio.');
        }
        $this->content = base64_encode($content);
        $this->setDirty('content');
        return $this;
    }

    public function getNotifyee(): int { return $this->notifyee; }
    public function setNotifyee(int $notifyee): Notification {
        if ($notifyee <= 0) {
            throw new \InvalidArgumentException('Invalid notifyee user ID.');
        }
        $this->notifyee = $notifyee;
        $this->setDirty('notifyee');
        return $this;
    }

    public function isMailable(): bool { return $this->mailable; }
    public function setMailable(bool $mailable): Notification {
        $this->mailable = $mailable;
        $this->setDirty('mailable');
        return $this;
    }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): Notification {
        if (!in_array($status, self::STATUSES)) {
            throw new \InvalidArgumentException('Invalid status.');
        }
        $this->status = $status;
        $this->setDirty('status');
        return $this;
    }

    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function setCreatedAt(DateTime $createdAt): Notification {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): string { return $this->updatedAt->format('Y-m-d H:i:s'); }
    public function setUpdatedAt(DateTime $updatedAt): Notification {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // Add getters and setters
    public function getTask(): ?int { return $this->task; }
    public function setTask(?int $task): Notification {
        $this->task = $task;
        $this->setDirty('task');
        return $this;
    }

    public function isSent(): bool { return $this->sent; }
    public function setSent(bool $sent): Notification {
        $this->sent = $sent;
        $this->setDirty('sent');
        return $this;
    }

    public function getSentAt(): ?string {
        return $this->sentAt?->format('Y-m-d H:i:s');
    }
    public function setSentAt(?DateTime $sentAt): Notification {
        $this->sentAt = $sentAt;
        $this->setDirty('sent_at');
        return $this;
    }

    public function getScheduledAt(): ?string {
        return $this->scheduledAt?->format('Y-m-d H:i:s');
    }
    public function setScheduledAt(?DateTime $scheduledAt): Notification {
        $this->scheduledAt = $scheduledAt;
        $this->setDirty('scheduled_at');
        return $this;
    }

    protected function getForeignNotifyee(): array
    {
        return new User()->getUserById($this->getNotifyee());
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->loadRelationships)) {
            $method = $this->loadRelationships[$name];
            if (method_exists($this, $method)) {
                return $this->{$method}();
            }
        }
        trigger_error("Tentativa de acesso a propriedade inexistente: " . static::class . "::$name");
        return null;
    }

    private static function create($payload): Notification
    {
        $notification = new Notification(
            $payload['content'],
            $payload['notifyee'],
            (bool)$payload['mailable'],
            $payload['status'],
            $payload['task'] ?? null,
            (bool) ($payload['sent'] ?? false),
            isset($payload['sent_at']) ? DateTime::createFromFormat("Y-m-d H:i:s", $payload['sent_at']) : null,
            isset($payload['scheduled_at']) ? DateTime::createFromFormat("Y-m-d H:i:s", $payload['scheduled_at']) : null
        );
        if (isset($payload['created_at'])) {
            $notification->setCreatedAt(DateTime::createFromFormat("Y-m-d H:i:s", $payload['created_at']));
        }
        if (isset($payload['updated_at'])) {
            $notification->setUpdatedAt(DateTime::createFromFormat("Y-m-d H:i:s", $payload['updated_at']));
        }
        return $notification;
    }

    /**
     * Função de conveniência para criar objetos com base nos resultados
     * @param false|\PDOStatement $stmt
     * @param PDO $conn
     * @return array
     */
    private static function buildResponsePayload(false|\PDOStatement $stmt, PDO $conn): array
    {
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $notifications = [];
        foreach ($res as $row) {
            $notifications[] = self::create($row)
                ->setId($row['id']);
        }
        return $notifications;
    }

    /**
     * Guarda as alterações do modelo na db.
     * @return bool O resultado.
     * @throws Exception
     */
    public function save(): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO notifications (content, notifyee, mailable, status, task, sent, sent_at, scheduled_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $status = $stmt->execute([
            $this->getContent(),
            $this->getNotifyee(),
            (int) $this->isMailable(),
            $this->getStatus(),
            $this->getTask(),
            (int) $this->isSent(),
            $this->getSentAt(),
            $this->getScheduledAt(),
            $this->getCreatedAt()->format('Y-m-d H:i:s'),
            $this->getUpdatedAt()
        ]);
        if ($status) {
            $this->id = $this->conn->lastInsertId();
            return true;
        } else {
            throw new Exception('Não foi possível guardar a notificação.');
        }
    }

    /**
     * Tenta encontrar a notificação ou dá erro
     * @param int $id
     * @return Notification
     * @throws Exception
     */
    public static function findByIdOrFail(int $id): Notification
    {
        $conn = DataLayer::getConnection();
        $stmt = $conn->prepare('SELECT * FROM notifications WHERE id = ?');
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            return self::create($res)->setId($res['id']);
        }
        throw new Exception('Notificação não encontrada.');
    }

    /**
     * Devolve todas as notificações registadas
     * @param string $order Ordenação, DESC ou ASC
     * @return array Resultados
     * @throws Exception
     */
    public static function all(string $order = 'DESC'): array
    {
        $conn = DataLayer::getConnection();
        $validOrders = ['ASC', 'DESC'];
        if (!in_array($order, $validOrders)) {
            throw new \InvalidArgumentException('Ordenação inválida (DESC, ASC).');
        }
        $stmt = $conn->prepare('SELECT * FROM notifications ORDER BY created_at ' . $order);
        $stmt->execute();
        return self::buildResponsePayload($stmt, $conn);
    }

    /**
     * Devolve todas as notificações de um utilizador.
     * @param int $iduser O ID a verificar
     * @param bool $onlyUnread Se quer só notificações não lidas
     * @return array Array de objetos de notificação, ou, se não houver, array vazio
     * @throws Exception
     */
    public static function allForUser(int $iduser, bool $onlyUnread = true)
    {
        $conn = DataLayer::getConnection();
        $user = new User();

        if (empty($user->getUserById($iduser))) {
            throw new Exception("Erro ao obter notificações: utilizador não existe.");
        }

        if ($onlyUnread) {
            $stmt = $conn->prepare('SELECT * FROM notifications WHERE notifyee = ? AND status = ?');
            $res = $stmt->execute([$iduser, 'UNREAD']);
        } else {
            $stmt = $conn->prepare('SELECT * FROM notifications WHERE notifyee = ?');
            $res = $stmt->execute([$iduser]);
        }

        if ($res) {
            return self::buildResponsePayload($stmt, $conn);
        } else {
            return [];
        }
    }

    /**
     * Verifica se a tarefa já foi notificada, mantendo a idempotência
     * @param int $taskId
     * @return bool
     */
    public static function isTaskNotified(int $taskId): bool
    {
        $conn = DataLayer::getConnection();

        $qry = $conn->prepare('SELECT * FROM notifications WHERE task = ? AND status = ?');
        $qry->execute([$taskId, 'UNREAD']);

        $res = $qry->fetchAll(PDO::FETCH_ASSOC);

        return !empty($res);
    }

    /**
     * Função de utilidade para marcar uma notificação como lida.
     * @param Notification $notification
     * @return bool Resultado da operação.
     * @throws Exception
     */
    public static function markRead(Notification $notification): bool
    {
        return $notification->setStatus('READ')->update();
    }

    /**
     * @return bool Atualiza o modelo e guarda as alterações na DB.
     */
    public function update(): bool
    {
        $this->setUpdatedAt(new DateTime());
        $fields = [];
        foreach ($this->dirty as $field) {
            if (property_exists($this, $field)) {
                $fields[$field] = $this->$field;
            }
        }
        $fields['updated_at'] = $this->getUpdatedAt();
        return DataLayer::updateTableData('notifications', ['id' => $this->getId()], array_merge($this->fillable, ['updated_at']), $fields);
    }

    public function delete(): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM notifications WHERE id = ?');
        return $stmt->execute([$this->getId()]);
    }
}