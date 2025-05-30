<?php
namespace App\Models\Projects;

require_once realpath(__DIR__ . '/../../app/bootstrap.php');

use App\Models\ProjectStatus\ProjectStatus;
use App\Core\Database\Database;
use PDO;

/**
 * Classe de utilidade (CRUD) para projetos. Pseudo-modelo.
 */
class Project {

        private PDO $connection;

        private ProjectStatus $status;

        private int $assigned_to;
        private string $name;
        private string $description;
        private string $start_date;
        private string $end_date;
        private int $status_id;

        public function __construct(string $name = '', int $assigned_to = 1, string $description = '', string $start_date = '', string $end_date = '', int $status_id = 1)
        {
            $this->connection = Database::getConnection();
            $this->status = new ProjectStatus();

            $this->assigned_to = $assigned_to;
            $this->name = $name;
            $this->description = $description;
            $this->start_date = $start_date;
            $this->end_date = $end_date;
            $this->status_id = ($this->status->status_exists($status_id)) ? $status_id : null;
        }

        public function getAssignedTo(): int
        {
            return $this->assigned_to;
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function setName(string $name): Project
        {
            $this->name = $name;
            return $this;
        }

        public function getDescription(): string
        {
            return $this->description;
        }

        public function setDescription(string $description): Project
        {
            $this->description = $description;
            return $this;
        }

        public function getStartDate(): string
        {
            return $this->start_date;
        }

        public function setStartDate(string $start_date): Project
        {
            $this->start_date = $start_date;
            return $this;
        }

        public function getEndDate(): string
        {
            return $this->end_date;
        }

        public function setEndDate(string $end_date): Project
        {
            $this->end_date = $end_date;
            return $this;
        }

        public function getStatusId(): int
        {
            return $this->status_id;
        }

        public function setStatusId(int $status_id): Project
        {
            $this->status_id = $status_id;
            return $this;
        }

        public function setAssignedTo(int $user_id): Project
        {
            $this->assigned_to = $user_id;
            return $this;
        }

    /**
     * Guarda os registos na base de dados, criando um Projeto novo.
     * @return array [resultado da operação, último ID]
     */
    public function save(): array
    {

        $dbc = $this->connection;

        $stmt = $dbc->prepare('INSERT INTO projects (assigned_to, name, description, start_date, end_date, status_id) VALUES (?, ?, ?, ?, ?, ?)');
        return [$stmt->execute([
           $this->assigned_to,
           $this->name,
           $this->description,
           $this->start_date,
           $this->end_date,
           $this->status_id,
        ]), $dbc->lastInsertId()];

    }

    /**
     * Verifica a existência de um projeto
     * @param int $project_id Projeto a verificar
     * @return bool
     */
    public function project_exists(int $project_id): bool
    {
        $dbc = $this->connection;


        $project_count = $dbc->prepare('SELECT COUNT(*) FROM projects WHERE id = ?');
        $project_count->execute([$project_id]);

        return $project_count->fetchColumn() >= 1;
    }

    /**
     * Devolve o projeto associado.
     *
     * @param int $project_id
     * @return mixed
     */
    public function get_project(int $project_id, bool $all = false): mixed
    {
        $db = $this->connection;

        if ($all) {
            $stmt = $db->prepare('SELECT * FROM projects');
            $stmt->execute();
        }
        else {
            $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
            $stmt->execute([$project_id]);
        }

        return ($all) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);

    }

    /**
     * Elimina o projeto indicado.
     * @param int $project_id
     * @return bool
     */
    public function delete_project(int $project_id): bool
    {
        if ($this->project_exists($project_id)) {
            $stmt = $this->connection->prepare('DELETE FROM projects WHERE id = ?');
            return $stmt->execute([$project_id]);
        }

        return false;
    }

    /**
     * Atualiza um projeto com base em campos permitidos
     * @param int $project_id ID do projeto
     * @param array $fields Campos a atualizar
     * @return bool Sucesso da operação
     * @throws \Exception
     */
    public function update_project(int $project_id, array $fields): bool
    {
        $fillable = [
            'name',
            'description',
            'start_date',
            'end_date',
            'status_id'
        ];

        return update_table_data('projects', ['id', $project_id], $fillable, $fields);
    }
}