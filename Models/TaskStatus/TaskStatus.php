<?php

namespace App\Models\TaskStatus;

use App\Core\Database\Database;
use App\Core\Database\DataLayer;
use PDO;

class TaskStatus
{
    private string $name;
    private string $description;
    private int $status;
    private int $final;
    private $conn;
    private \DateTime $created_at;
    private \DateTime $updated_at;

    public function __construct(
        string $name = '',
        string $description = '',
        int $status = 1,
        int $final = 0,
        \DateTime $created_at = new \DateTime(),
        \DateTime $updated_at = new \DateTime()
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->final = $final;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->conn = DataLayer::getConnection();


    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getFinal(): int
    {
        return $this->final;
    }

    public function setFinal(int $final): bool|null
    {
        if (!$this->finalExists()) {
            $this->final = $final;
            return true;
        } else {
            throw new \InvalidArgumentException('Já existe uma tarefa finalizadora.');
        }
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    /**
     * Cria um estado de tarefa.
     * @return bool Sucesso da operação
     */
    public function create(): bool
    {

        if ($this->getFinal() == 1 && $this->finalExists()) {
            throw new \InvalidArgumentException('Já existe um estado de tarefa final');
        }

        $stmt = $this->conn->prepare('INSERT INTO task_status (name, description, status, final, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
        return $stmt->execute([
           $this->getName(),
           $this->getDescription(),
           $this->getStatus(),
            !$this->getFinal() ? 0 : 1,
           $this->getCreatedAt()->format('Y-m-d H:i:s'),
           $this->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Seletivamente ler o estado da tarefa
     * @param int $taskStatusId
     * @param bool $all Ler tudo?
     * @param bool $onlyValid Apenas devolver estados válidos?
     * @return array|mixed Resultados
     */
    public function read(int $taskStatusId, bool $all, bool $onlyValid): mixed
    {
        $conn = $this->conn;

        if ($all) {

            if ($onlyValid) {
                $stmt = $conn->prepare('SELECT * FROM task_status WHERE status = 1');
            } else {
                $stmt = $conn->prepare('SELECT * FROM task_status');
            }

            $stmt->execute();
        }
        else {

            if ($onlyValid) {
                $stmt = $conn->prepare('SELECT * FROM task_status WHERE id = ? AND status = 1');
            } else {
                $stmt = $conn->prepare('SELECT * FROM task_status WHERE id = ?');
            }

            $stmt->execute([$taskStatusId]);
        }

        return ($all) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);

    }

    /**
     * Verifica se um estado de tarefa existe com base no método read.
     * @param $taskId int ID da tarefa.
     * @return bool Estado da operação.
     */
    public function exists(int $taskId): bool
    {
        return !empty($this->read($taskId, false, false));
    }

    /**
     * Atualizar o estado da tarefa
     * @param $taskStatusId
     * @param $fieldsToUpdate
     * @return bool Sucesso da operação
     */
    public function update($taskStatusId, $fieldsToUpdate): bool
    {
       $fillable = [
           'name',
           'description',
           'status',
           'final'
       ];
       return DataLayer::updateTableData('task_status', ['id', $taskStatusId], $fillable, $fieldsToUpdate);
    }

    /**
     * Apagar o estado da tarefa
     * @param $taskStatusId
     * @return bool
     */
    public function delete($taskStatusId): bool
    {
        return $this->conn->prepare('DELETE FROM task_status WHERE id = ?')->execute([$taskStatusId]);
    }

    /**
     * Verifica se existe algum estado marcado como final.
     * @return bool
     */
    public function finalExists(): bool
    {
        $conn = $this->conn;
        $qry = $conn->prepare('SELECT * from task_status WHERE final = 1');
        $qry->execute();

        if (!empty($qry->fetch(PDO::FETCH_ASSOC))) {
            return true;
        }

        return false;
    }

    /**
     * Devolve o estado finalizador
     * @return array
     */
    public function getFinalStatus(): array
    {
        $conn = $this->conn;
        $qry = $conn->prepare('SELECT * FROM task_status WHERE final = 1');

        $qry->execute();

        if (!empty($qry->fetch(PDO::FETCH_ASSOC))) {
            return $qry->fetch(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * Marca um estado como podendo finalizar uma tarefa.
     * @param $statusId
     * @return bool
     * @throws \InvalidArgumentException Caso já exista algum estado finalizador.
     */
    public function markFinal($statusId): bool
    {
        if ($this->finalExists()) {
            throw new \InvalidArgumentException('Já existe um estado marcado como final.');
        }

        return $this->update($statusId, ['final' => 1]);
    }

}