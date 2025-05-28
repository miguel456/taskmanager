<?php

namespace App\Models\ProjectStatus;

use PDO;

/**
 * Pseudo modelo do estado do projeto
 */
class ProjectStatus
{
    private string $name, $description;

    private bool $status;

    private PDO $conn;

    public function __construct(string $name = 'Projeto exemplo', string $description = 'Descrição do projeto', bool $status = true)
    {
        $this->conn = \App\Core\Database\Database::getConnection();

        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
    }

    /**
     * Guarda os valores do modelo na base de dados.
     * @return bool
     */
    public function save(): bool
    {
        $stmt = $this->conn->prepare('INSERT INTO project_status (id, name, description, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
        return $stmt->execute([
            null,
            $this->name,
            $this->description,
            $this->status,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Verifica se um estado existe.
     * @param int $status_id
     * @return bool
     */
    public function status_exists(int $status_id): bool
    {
        $stmt = $this->conn->prepare('SELECT * FROM project_status WHERE id = ?');
        $stmt->execute([$status_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Retorna o estado pedido.
     * @param int $status_id
     * @param bool $all Se pretende devolver todos os registos; caso contrário devolve o registo referente ao $status_id
     * @return array
     */
    public function get_status(int $status_id, bool $all = false): array
    {
        $db = $this->conn;

        if ($all) {
            $stmt = $db->prepare('SELECT * FROM project_status');
            $stmt->execute();
        }
        else {
            $stmt = $db->prepare('SELECT * FROM project_status WHERE id = ?');
            $stmt->execute([$status_id]);
        }

        return ($all) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $status_id O ID do estado.
     * @param array $fields associativo de compos e dados a atualizar
     * @throws \Exception
     */
    public function update_status(int $status_id, array $fields): bool
    {
        $fillable = [
            'name',
            'description',
            'status'
        ];

        if ($this->status_exists($status_id)) {
            return update_table_data('project_status', ['id', $status_id], $fillable, $fields);
        }

        return false;
    }

    /**
     * Apaga o estado especificado.
     * @param int $status_id
     * @return bool
     */
    public function delete_status(int $status_id): bool
    {
        return $this->conn->prepare('DELETE FROM project_status WHERE id = ?')->execute([$status_id]);
    }
}