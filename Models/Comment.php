<?php

namespace App\Models;

use App\Core\Database\DataLayer;
use App\Core\Exceptions\CommentNotFoundException;
use Exception;
use http\Exception\InvalidArgumentException;
use PDO;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * Modelo com design melhorado, usado por Comentários de Tarefa e Comentários de Projeto. Tem algumas alterações pontuais para ser compatível com os outros modelos.
 */
class Comment
{
    private ?int $id;
    private int $commenterId;
    private bool $visibility;
    private string $content;
    private ?string $type;
    private ?int $typeId;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    private PDO $conn;

    /**
     * Não é possível editar diretamente campos que não estejam nesta lista.
     * @var array Mantém uma lista de campos de possível edição.
     */
    protected array $fillable = [
       'visibility',
       'content'
    ];

    /**
     * Apenas considera campos "fillable".
     * @var array Mantém uma lista, atualizada dinâmicamente, de campos que foram sujeitos a alteração após a criação do objeto, ditando assim que campos serão atualizados pelo update().
     */
    protected array $dirty = [];

    public function __construct(
        int $commenterId,
        bool $visibility,
        string $content,
        ?string $type = null,
        ?int $typeId = null
    ) {
        $this->setCommenterId($commenterId);
        $this->setVisibility($visibility);
        $this->setContent($content);
        $this->setType($type);
        $this->setTypeId($typeId);
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->conn = DataLayer::getConnection();
    }

    /**
     * Com base na consulta fornecida, constrói e devolve um array de objetos de Comentário. Não deve ser utilizado fora do modelo.
     * @param false|\PDOStatement $stmt Consulta, preferêncialmente uma JOIN
     * @param PDO $conn Ligação à bd
     * @return array Array de objetos de Comentário
     */
    private static function buildResponsePayload(false|\PDOStatement $stmt, PDO $conn): array
    {
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $comments = [];
        foreach ($res as $comment) {

            $payload = [
                'commenter' => $comment['commenter'],
                'visibility' => $comment['visibility'],
                'content' => $comment['content']
            ];

            $comments[] = self::create($payload)->setTypeId($comment['commentable_id'])->setType($comment['commentable_type'])->setId($comment['id']);
        }

        return $comments;
    }

    /**
     * Define um atributo como modificado.
     * @param string $field O atributo em questão
     * @return void Sem efeito caso o atributo não seja autorizado de modificação
     */
    protected function setDirty(string $field): void
    {
        if (in_array($field, $this->fillable)) {
            if (!in_array($field, $this->dirty)) {
                $this->dirty[] = $field;
            }
        }
    }

    public function getCommenterId(): int
    {
        return $this->commenterId;
    }

    public function setCommenterId(int $commenterId): Comment
    {
        if ($commenterId <= 0) {
            throw new \InvalidArgumentException('Invalid user ID.');
        }
        $this->commenterId = $commenterId;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): Comment
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): Comment
    {
        if (empty(trim($content))) {
            throw new \InvalidArgumentException('Content cannot be empty.');
        }
        $this->content = $content;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Define o tipo de comentário; de tarefa ou de projeto.
     * @param string|null $type "project" ou "task", obrigatóriamente (ou apenas nulo se não for conhecido)
     * @return $this
     */
    public function setType(?string $type): Comment
    {
        if (!is_null($type) && !in_array($type, ['task', 'project'])) {
            throw new \InvalidArgumentException('Tipo de comentável inválido ou não reconhecido');
        }
        $this->type = $type;
        return $this;
    }


    protected function setId(int $id): Comment
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function setTypeId(?int $typeId): Comment
    {
        if (is_null($typeId)) {
            $this->typeId = null;
            return $this;
        }

        if ($typeId <= 0) {
            throw new \InvalidArgumentException('ID de comentável inválido.');
        }
        $this->typeId = $typeId;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): Comment
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt->format('Y-m-d H:i:s');
    }

    public function setUpdatedAt(\DateTime $updatedAt): Comment
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    /** Cria uma instância do comentário
     * @return Comment O novo comentário
     */
    private static function create($payload): Comment
    {
        return new Comment(
            $payload['commenter'],
            $payload['visibility'],
            $payload['content'],
        );
    }

    /**
     * Persiste os dados do modelo na DB. **NÃO SUPORTA** alterações pontuais num objeto existente (demasiado avançado)! Para isso deve utilizar a função update.
     * @return bool
     * @throws Exception
     */
    public function save(): bool
    {
        $stmtComment = $this->conn->prepare('INSERT INTO comments (commenter, visibility, content, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');

        $commentStatus = $stmtComment->execute([
            $this->getCommenterId(),
            $this->isVisible(),
            $this->getContent(),
            $this->getCreatedAt(),
            $this->getUpdatedAt()
        ]);

        if ($commentStatus) {
            $stmtRel = $this->conn->prepare('INSERT INTO commentables (comment_id, commentable_type, commentable_id) VALUES (?, ?, ?)');
            $relStatus = $stmtRel->execute([
               $this->conn->lastInsertId(),
               $this->getType(),
               $this->getTypeId()
            ]);

        } else {
            throw new Exception('Não foi possível guardar o comentário. Tente novamente mais tarde.');
        }

        $this->id = $this->conn->lastInsertId();
        return $relStatus;
    }

    /**
     * Verifica se o comentário existe
     * @param int $id o ID do comentário
     * @return bool o sucesso da operação
     * @throws Exception
     */
    public static function exists(int $id): bool
    {
        $conn = DataLayer::getConnection();

        $stmt = $conn->prepare('SELECT COUNT(*) FROM comments WHERE id = ?');
        $stmt->execute([$id]);

        return (int) $stmt->fetchColumn() >= 1;
    }

    /**
     * Devolve um objeto de Comentário com base no “ID” apresentado
     * @param int $id ID do comentável
     * @param string $type [task, project]
     * @return Comment Comentário
     * @throws CommentNotFoundException Caso não seja encontrado
     * @throws Exception
     */
    public static function findByIdOrFail(int $id, string $type): Comment
    {
        $conn = DataLayer::getConnection();

        $stmt = $conn->prepare('SELECT comments.*, commentables.commentable_type FROM comments JOIN commentables ON commentables.commentable_id = comments.id WHERE comments.id = ?');
        $stmt->execute([$id]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastId = $res['id'];

        if (!empty($res)) {
            return self::create($res)->setType($type)->setTypeId($res['commentable_id'])->setId($lastId);
        } else {
            throw new CommentNotFoundException();
        }
    }


    /**
     * Devolve uma lista de objetos pertinentes
     * @param string $order A ordenação dos objetos (DESC, ASC)
     * @return array Lista de Comments
     * @throws Exception
     */
    public static function all(string $order = 'DESC'): array
    {
        $conn = DataLayer::getConnection();
        $validOrders = [
            'ASC',
            'DESC'
        ];

        if (!in_array($order, $validOrders)) {
            throw new InvalidArgumentException($order . ' não é um valor válido para esta operação.');
        }


        $stmt = $conn->prepare('SELECT comments.*, commentables.commentable_type FROM comments JOIN commentables ON commentables.commentable_id = comments.id ORDER BY comments.created_at ' . $order);
        $stmt->execute();

        return self::buildResponsePayload($stmt, $conn);
    }

    /**
     * Remove o comentário atual e os relacionamentos respetivos.
     * @return bool
     */
    public function delete(): bool
    {
        $conn = $this->conn;

        $sqlRel = 'DELETE FROM commentables WHERE comment_id = ?';
        $sqlComm = 'DELETE FROM comments WHERE id = ?';

        // Probably not necessary bc of cascade delete, double-check
        $stmtRel = $conn->prepare($sqlRel)->execute([$this->getId()]);
        $commRel = $conn->prepare($sqlComm)->execute([$this->getId()]);

        return $stmtRel && $commRel;
    }


    public function update(): bool
    {
        $fields = [];
        foreach ($this->dirty as $field) {
            if (property_exists($this, $field)) {
                $fields[$field] = $this->$field;
            }
        }
        return DataLayer::updateTableData('comments', ['id' => $this->getId()], $this->fillable, $fields);
    }

    // Métodos para comentários de Entidade; Talvez esta lógica pudesse ser delegada ao modelo relevante

    /**
     * Devolve todos os comentários relacionados a tarefas
     * @param int|null $id Opcionalmente, o ID da tarefa para a qual obter comentários
     * @return array Um array de objetos de comentário
     * @throws Exception
     */
    public static function getTaskComments(?int $id = null): array
    {
        $conn = DataLayer::getConnection();

        $sql = 'SELECT c.*, cb.commentable_type, cb.commentable_id FROM comments c JOIN commentables cb on c.id = cb.comment_id WHERE cb.commentable_type = ?';
        $params = ['task'];

        if (!is_null($id)) {
            $sql .= ' AND cb.commentable_id = ?';
            $params[] = $id;
        }

        $sql .= ' ORDER BY c.created_at DESC';

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return self::buildResponsePayload($stmt, $conn);
    }

    /**
     * Devolve todos os comentários relacionados a projetos
     * @param int|null $id Opcionlmente, o ID do projeto para o qual obter comentários
     * @return array Um array de objetos de comentário
     * @throws Exception
     */
    public static function getProjectComments(?int $id = null): array
    {
        $conn = DataLayer::getConnection();

        $sql = 'SELECT comments.*, commentables.commentable_type, commentables.commentable_id FROM comments JOIN commentables ON commentables.commentable_id = comments.id WHERE commentables.commentable_type = ?';
        $params = ['project'];

        if (!is_null($id)) {
            $sql .= ' AND commentables.commentable_id = ?';
            $params[] = $id;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return self::buildResponsePayload($stmt, $conn);
    }

}