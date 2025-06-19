<?php

namespace App\Models;

use App\Core\Database\DataLayer;
use App\Models\Projects\Project;
use App\Models\Tasks\Tasks\Task;
use App\Models\Users\User;
use DateTime;
use Exception;
use PDO;

class History
{
    private ?int $id;
    private string $action;
    private string $type;
    private string $description;
    private int $author;
    private int $target;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    private PDO $conn;

    /**
     * @var array|string[] Lista de campos que permitem edição.
     */
    protected array $fillable = [
        'action',
        'type',
        'description',
        'author',
        'target'
    ];

    /**
     * @var array Lista de campos editados.
     */
    protected array $dirty = [];

    /**
     * @var array|string[] Relacionamentos a carregar. ['campo' => 'função que o obtém']
     */
    protected array $loadRelationships = [
        'authorUser' => 'getForeignAuthor',
        'target' => 'getForeignTarget'
    ];

    /**
     * Lista estática de ações válidas
     */
    private const ACTIONS = ['create', 'update', 'delete'];

    /**
     * Lista estática de tipos válidos. Para suportar mais um tipo, basta adicionar aqui.
     */
    private const TYPES = ['task', 'project'];

    public function __construct(
        string $action,
        string $type,
        string $description,
        int $author,
        int $target
    ) {
        $this->setAction($action);
        $this->setType($type);
        $this->setDescription($description);
        $this->setAuthor($author);
        $this->setTarget($target);
        $now = new DateTime();
        $this->setCreatedAt($now);
        $this->setUpdatedAt($now);
        $this->conn = DataLayer::getConnection();
    }

    /**
     * Fábrica de históricos rudimentar com base nos resultados da query à db.
     * @param false|\PDOStatement $stmt
     * @param PDO $conn
     * @return array
     */
    private static function buildResponsePayload(false|\PDOStatement $stmt, PDO $conn): array
    {
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $histories = [];
        foreach ($res as $row) {
            $histories[] = self::create($row)
                ->setId($row['id'])
                ->setCreatedAt(DateTime::createFromFormat("Y-m-d H:i:s", $row['created_at']))
                ->setUpdatedAt(DateTime::createFromFormat("Y-m-d H:i:s", $row['updated_at']));
        }
        return $histories;
    }

    /**
     * Marca um campo como modificado para poder ser refletido na db
     * @param string $field O campo mudado
     * @return void
     */
    protected function setDirty(string $field): void
    {
        if (in_array($field, $this->fillable) && !in_array($field, $this->dirty)) {
            $this->dirty[] = $field;
        }
    }

    public function getId(): ?int { return $this->id; }
    protected function setId(int $id): History { $this->id = $id; return $this; }

    public function getAction(): string { return $this->action; }
    public function setAction(string $action): History {
        if (!in_array($action, self::ACTIONS)) {
            throw new \InvalidArgumentException('Ação inválida. Tem de ser "create", "update", "delete".');
        }
        $this->action = $action;
        $this->setDirty('action');
        return $this;
    }

    public function getType(): string { return $this->type; }
    public function setType(string $type): History {
        if (!in_array($type, self::TYPES)) {
            throw new \InvalidArgumentException('Tipo inválido. Tem de ser "task" ou "project". ');
        }
        $this->type = $type;
        $this->setDirty('type');
        return $this;
    }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): History {
        if (empty(trim($description))) {
            throw new \InvalidArgumentException('Descrição da mudança não pode estar vazia.');
        }
        $this->description = $description;
        $this->setDirty('description');
        return $this;
    }

    public function getAuthor(): int { return $this->author; }
    public function setAuthor(int $author): History {
        if ($author <= 0) {
            throw new \InvalidArgumentException('ID do autor inválido.');
        }
        $this->author = $author;
        $this->setDirty('author');
        return $this;
    }

    public function getTarget(): int { return $this->target; }
    public function setTarget(int $target): History {
        if ($target <= 0) {
            throw new \InvalidArgumentException('ID do alvo inválido.');
        }
        $this->target = $target;
        $this->setDirty('target');
        return $this;
    }

    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function setCreatedAt(DateTime $createdAt): History {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): string { return $this->updatedAt->format('Y-m-d H:i:s'); }
    public function setUpdatedAt(DateTime $updatedAt): History {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Devolve o utilizador associado da chave estrangeira
     * @return array O utilizador associado
     */
    protected function getForeignAuthor(): array
    {
        return (new User())->getUserById($this->getAuthor());
    }

    /**
     * Devolve uma tabela extra n-n. Retorna o "relacionamento" com base no tipo de objeto (O MySQL não suporta chaves condicionais);
     * Assim poupamos linhas de código.
     * @return array O relacionamento, ou array vazio caso não exista ou não corresponda ao tipo real
     */
    protected function getForeignTarget(): array
    {
        // Não vai existir
        if ($this->action == 'delete') {
            return [];
        }

        return match ($this->getType()) {
            'task' => new Task()->read($this->getTarget(), false),
            'project' => new Project()->get_project($this->getTarget()),
            default => [],
        };
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->loadRelationships)) {
            $method = $this->loadRelationships[$name];
            if (method_exists($this, $method)) {
                return $this->{$method}();
            }
        }
        trigger_error("Tentativa de acesso a propriedade inexistente:  " . static::class . "::$name");
        return null;
    }

    private static function create($payload): History
    {
        $history = new History(
            $payload['action'],
            $payload['type'],
            $payload['description'],
            $payload['author'],
            $payload['target']
        );
        if (isset($payload['created_at'])) {
            $history->setCreatedAt(DateTime::createFromFormat("Y-m-d H:i:s", $payload['created_at']));
        }
        if (isset($payload['updated_at'])) {
            $history->setUpdatedAt(DateTime::createFromFormat("Y-m-d H:i:s", $payload['updated_at']));
        }
        return $history;
    }

    public function save(): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO history (action, type, description, author, target, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $status = $stmt->execute([
            $this->getAction(),
            $this->getType(),
            $this->getDescription(),
            $this->getAuthor(),
            $this->getTarget(),
            $this->getCreatedAt()->format('Y-m-d H:i:s'),
            $this->getUpdatedAt()
        ]);
        if ($status) {
            $this->id = $this->conn->lastInsertId();
        } else {
            throw new Exception('Não foi possível guardar o histórico.');
        }
        return $status;
    }

    public static function findByIdOrFail(int $id): History
    {
        $conn = DataLayer::getConnection();
        $stmt = $conn->prepare('SELECT * FROM history WHERE id = ?');
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            return self::create($res)
                ->setId($res['id']);
        }
        throw new Exception('History record not found.');
    }

    public static function all(string $order = 'DESC'): array
    {
        $conn = DataLayer::getConnection();
        $validOrders = ['ASC', 'DESC'];
        if (!in_array($order, $validOrders)) {
            throw new \InvalidArgumentException('Invalid order.');
        }
        $stmt = $conn->prepare('SELECT * FROM history ORDER BY created_at ' . $order);
        $stmt->execute();
        return self::buildResponsePayload($stmt, $conn);
    }

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
        return DataLayer::updateTableData('history', ['id' => $this->getId()], array_merge($this->fillable, ['updated_at']), $fields);
    }

    public function delete(): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM history WHERE id = ?');
        return $stmt->execute([$this->getId()]);
    }
}