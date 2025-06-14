<?php

namespace App\Models\Tasks\Tasks;

use App\Core\Database\Database;
use App\Models\Projects\Project;
use App\Models\TaskStatus\TaskStatus;
use App\Models\Users\User;
use DateTime;
use LogicException;
use PDO;

class Task
{
    private \PDO $conn;
    private string $task_name;
    private int $task_owner;
    private int $task_status_id;

    private ?int $project;
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
        string   $task_name = '',
        int      $task_owner = 0,
        int      $task_status_id = 0,
        ?int $project = null,
        string   $task_description = "",
        string   $time_spent = "",
        string   $task_priority = "p0",
        DateTime $due_date = new DateTime(),
        DateTime $start_date = new DateTime(),
        DateTime $finish_date = new DateTime(),
        DateTime $timestamp = new DateTime(),
        DateTime $created_at = new DateTime(),
        DateTime $updated_at = new DateTime()
    ) {
        $this->task_name = $task_name;
        $this->task_owner = $task_owner;
        $this->task_status_id = $task_status_id;
        $this->project = $project;
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

    /**
     * Devolve o estado certo da tarefa. Caso não exista, por algum motivo, devolve um array vazio.
     * @return array
     */
    public function getTaskStatus(): array
    {
        // relacionamento 1-1
        $taskStatus = new TaskStatus()->read($this->task_status_id, false, false);

        if (empty($taskStatus)) {
            return [];
        }

        return $taskStatus;
    }

    public function setTaskStatus(int $task_status_id): void
    {
        $this->task_status_id = $task_status_id;
    }

    /**
     * @param int|null $project
     */
    public function setProject(?int $project): void
    {
        $this->project = $project;
    }

    public function getProject(): int
    {
        return $this->project;
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
        $stmt = $conn->prepare('INSERT INTO tasks (id, task_name, task_owner, task_status_id, project_id, task_description, task_priority, due_date, start_date, finish_date, time_spent, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        $optionalProjectId = ($this->getProject() == 0) ? null : $this->getProject();

        return $stmt->execute([
           null,
           $this->getTaskName(),
           $this->getTaskOwner(),
           $this->task_status_id,
           $optionalProjectId,
           $this->getTaskDescription(),
           $this->getTaskPriority(),
           $this->getDueDate()->format('Y-m-d H:i:s'),
           $this->getStartDate()->format('Y-m-d H:i:s'),
           $this->getFinishDate()->format('Y-m-d H:i:s'),
           $this->getTimeSpent(),
           $this->getCreatedAt()->format('Y-m-d H:i:s'),
            $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Lê todas (ou só uma) as tarefas
     * @param int $id ID da tarefa, caso só precisar de uma
     * @param bool $all Caso queira todas as tarefas
     * @return array Um array com a(s) tarefa(s), ou um array vazio caso não exista(m) tarefa(s)
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

        $result = $all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
        $single = !$all;

        if (is_bool($result)) {
            return [];
        }


        $this->injectRelationships($result, $single);
        return $result;
    }

    /**
     * Verifica se a tarefa existe.
     * Atalho/alternativa para o método read().
     * @param $taskId
     * @return bool Sucesso da operação. Devolve True se a tarefa existe.
     */
    public function exists($taskId): bool
    {
        return !empty($this->read($taskId, false));
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
            'project_id',
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

    /**
     * Injeta relacionamentos do Modelo nos dados devolvidos.
     * @param array $resultSet Referência aos resultados
     * @param bool $single Declara que tipo de objeto estamos a injetar: vários, ou só um?
     * @return void Nada porque os resultados originais são alterados.
     */
    protected function injectRelationships(array &$resultSet, bool $single): void
    {
        $user = new User();
        $taskStatus = new TaskStatus();
        $project = new Project();

        if (!$single) {
            foreach ($resultSet as &$item) {
                $this->addRelationships($item, $user, $project, $taskStatus);
            }
        } else {
            $this->addRelationships($resultSet, $user, $project, $taskStatus);
        }

    }

    /**
     * Adiciona os relacionamentos ao $item, por referência. Não deve ser usado diretamente.
     * @param array $item O $item a injetar. Ter em atenção que só é possível injetar um $item de cada vez.
     * @param User $user Instância dos utilizadores.
     * @param TaskStatus $taskStatus Instância dos estados de tarefa.
     */
    private function addRelationships(array &$item, User $user, Project $project, TaskStatus $taskStatus): void
    {
        $curTaskOwner = $item['task_owner'];
        $curTaskStatus = $item['task_status_id'];

        $curUser = $user->getUserById($curTaskOwner);
        $curStatus = $taskStatus->exists($curTaskStatus);

        // nullable; pode ser nulo da base de dados. Caso for, definir para zero (tarefas flutuantes).
        $curProject = $item['project_id'] ?? 0;


        if (!empty($curUser) && $curStatus) {
            $item['rel']['task_owner'] = $curUser;
            $item['rel']['task_status_id'] = $taskStatus->read($curTaskStatus, false, false);
            $item['rel']['project_id'] = ($curProject == 0) ? [] : $project->get_project($curProject);
        } else {
            throw new LogicException('Erro fatal de validação de integridade referêncial: o utilizador ou o estado atribuído não existe.');
        }
    }
}