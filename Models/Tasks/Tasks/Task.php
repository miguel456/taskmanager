<?php

namespace App\Models\Tasks\Tasks;

use App\Core\Database\Database;
use DateTime;
use PDO;

class Task
{
    private \PDO $conn;
    private string $task_name;
    private int $task_owner;
    private int $task_status_id;
    private string $task_description;
    private string $time_spent;
    private string $task_priority;
    private DateTime $due_date;
    private DateTime $start_date;
    private DateTime $finish_date;
    private DateTime $timestamp;
    private DateTime $created_at;
    private DateTime $updated_at;

    public function __construct(
        string $task_name,
        int $task_owner,
        int $task_status_id,
        string $task_description,
        string $time_spent,
        string $task_priority,
        DateTime $due_date,
        DateTime $start_date,
        DateTime $finish_date,
        DateTime $timestamp,
        DateTime $created_at,
        DateTime $updated_at
    ) {
        $this->task_name = $task_name;
        $this->task_owner = $task_owner;
        $this->task_status_id = $task_status_id;
        $this->task_description = $task_description;
        $this->time_spent = $time_spent;
        $this->task_priority = $task_priority;
        $this->due_date = $due_date;
        $this->start_date = $start_date;
        $this->finish_date = $finish_date;
        $this->timestamp = $timestamp;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->conn = Database::getConnection();
    }

    public function getTaskName(): string
    {
        return $this->task_name;
    }

    public function setTaskName(string $task_name): void
    {
        $this->task_name = $task_name;
    }

    /**
     * @param int $task_owner
     */
    public function setTaskOwner(int $task_owner): void
    {
        $this->task_owner = $task_owner;
    }

    /**
     * @return int
     */
    public function getTaskOwner(): int
    {
        return $this->task_owner;
    }

    public function getTaskStatusId(): int
    {
        return $this->task_status_id;
    }

    public function setTaskStatusId(int $task_status_id): void
    {
        $this->task_status_id = $task_status_id;
    }

    public function getTaskDescription(): string
    {
        return $this->task_description;
    }

    public function setTaskDescription(string $task_description): void
    {
        $this->task_description = $task_description;
    }

    /**
     * @param string $task_priority
     */
    public function setTaskPriority(string $task_priority): void
    {
        $this->task_priority = $task_priority;
    }

    /**
     * @return string
     */
    public function getTaskPriority(): string
    {
        return $this->task_priority;
    }

    public function setTimeSpent($time_spent): void
    {
        $this->time_spent = $time_spent;
    }

    public function getTimeSpent(): string
    {
        return $this->time_spent;
    }

    public function getDueDate(): DateTime
    {
        return $this->due_date;
    }

    public function setDueDate(DateTime $due_date): void
    {
        $this->due_date = $due_date;
    }

    public function getStartDate(): DateTime
    {
        return $this->start_date;
    }

    public function setStartDate(DateTime $start_date): void
    {
        $this->start_date = $start_date;
    }

    public function getFinishDate(): DateTime
    {
        return $this->finish_date;
    }

    public function setFinishDate(DateTime $finish_date): void
    {
        $this->finish_date = $finish_date;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    // CRUD methods

    public function create(): bool
    {
        $conn = $this->conn;
        $stmt = $conn->prepare('INSERT INTO tasks (id, task_name, task_status_id, task_description, task_priority, due_date, start_date, finish_date, time_spent, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        return $stmt->execute([
           null,
           $this->getTaskName(),
           $this->getTaskStatusId(),
           $this->getTaskDescription(),
           $this->getTaskPriority(),
           $this->getDueDate(),
           $this->getStartDate(),
           $this->getFinishDate(),
           $this->getTimeSpent(),
           $this->getCreatedAt()->format('Y-m-d H:i:s'),
            $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Lê todas (ou só uma) as tarefas
     * @param int $id ID da tarefa, caso só precisar de uma
     * @param bool $all Caso queira todas as tarefas
     * @return array
     */
    public function read(int $id = 0, bool $all = true): array
    {
        $conn = $this->conn;
        if ($all) {
            $sql = 'SELECT * FROM tasks;';
        } else {
            $sql = 'SELECT * FROM tasks WHERE id = ?';
        }

        $stmt = $conn->prepare($sql);
        $all ? $stmt->execute() : $stmt->execute([$id]);

        return $all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Atualizar a tarefa $task_id
     * @param int $task_id
     * @param array $fields
     * @return bool Sucesso da operação
     * @throws \Exception
     */
    public function update(int $task_id, array $fields): bool
    {
        $fillable = [
            'task_name',
            'task_owner',
            'task_status_id',
            'task_description',
            'task_priority',
            'due_date',
            'start_date',
            'finish_date',
            'time_spent'
        ];

        $fields['updated_at'] = new DateTime()->format('Y-m-d H:i:s');
        return update_table_data('tasks', ['id', $task_id], $fillable, $fields);
    }

    /**
     * Apaga a tarefa selecionada
     * @param int $task_id
     * @return bool Sucesso da operação
     */
    public function delete(int $task_id): bool
    {
        return $this->conn->prepare('DELETE FROM tasks WHERE id = ?')->execute([$task_id]);
    }
}