<?php
namespace App\Models\Projects;

require_once realpath(__DIR__ . '/../../app/bootstrap.php');

use App\Core\Database\DataLayer;
use App\Core\Traits\MonitorsHistory;
use App\Models\ProjectStatus\ProjectStatus;
use App\Core\Database\Database;
use App\Models\Users\User;
use LogicException;
use PDO;

/**
 * Classe de utilidade (CRUD) para projetos. Pseudo-modelo.
 */
class Project {

    use MonitorsHistory;

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
            $this->status_id = ($this->status->status_exists($status_id)) ? $status_id : 0;
        }

    /**
     * Devolve o utilizador associado ao projeto.
     * @return array Array com o utilizador associado ao projeto.
     * @throws LogicException Caso ocorra erro de integridade referêncial
     */
        public function getAssignedTo(): array
        {
            $user = new User();
            if ($user->getUserById($this->assigned_to)) {
                return $user->getUserById($this->assigned_to);
            }

            // nunca deverá acontecer
            throw new LogicException('Erro fatal de validação da integridade referêncial: o utilizador da chave estrangeira não existe!');
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
        $result = [$stmt->execute([
           $this->assigned_to,
           $this->name,
           $this->description,
           $this->start_date,
           $this->end_date,
           $this->status_id,
        ]), $dbc->lastInsertId()];

        $this->publishEvent("O projeto {$this->name} foi criado.", 'create', 'project', $dbc->lastInsertId());

        return $result;

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
        $resultSet = ($all) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
        $single = !$all;

        $this->injectRelationships($resultSet, $single);
        return $resultSet;
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
            $result = $stmt->execute([$project_id]);

            if ($result) {
                $this->publishEvent("O projeto com ID {$project_id} foi eliminado.", "delete", "project", $project_id);
            }

            return $result;

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
            'assigned_to',
            'name',
            'description',
            'start_date',
            'end_date',
            'status_id'
        ];

        // atualizar sessão com projeto atualizado
        if (!empty($this->getActiveProject())) {
            $this->activateProject($project_id);
        }

        $update = DataLayer::updateTableData('projects', ['id', $project_id], $fillable, $fields);

        // apenas publicar se existir confirmação da atualização e deixar o "caller" lidar com o resultado
        if ($update) {
            $this->publishEvent("O projeto {$project_id} foi atualizado (campos desconhecidos).", 'update', 'project', $project_id);
        }
        return $update;
    }

    // TODO: Mover esta lógica para um traço (ou extrair de acordo com a sugestão do phpstorm)
    /**
     * Injeta relacionamentos do Modelo nos dados devolvidos.
     * @param array $resultSet Referência aos resultados
     * @param bool $single Declara que tipo de objeto estamos a injetar: vários, ou só um?
     * @return void Nada porque os resultados originais são alterados.
     */
    protected function injectRelationships(array &$resultSet, bool $single): void
    {
        $user = new User();
        $projectStatus = new ProjectStatus();

        if (!$single) {
            foreach ($resultSet as &$item) {
                $curId = $item['assigned_to'];
                $curUser = $user->getUserById($curId);

                $curStatusId = $item['status_id'];
                $curStatus = $projectStatus->get_status($curStatusId, false, false);

                if (!empty($curUser) && !empty($curStatus)) {
                    $item['rel']['assigned_to'] = $curUser;
                    $item['rel']['status_id'] = $curStatus;
                }
                else {
                    throw new LogicException('Erro fatal de validação de integridade referêncial: o utilizador ou o estado atribuído não existe.');
                }
            }
        } else {
            $curId = $resultSet['assigned_to'];
            $curUser = $user->getUserById($curId);

            $curStatusId = $resultSet['status_id'];
            $curStatus = $projectStatus->get_status($curStatusId, false, false);

            if (!empty($curUser) && !empty($curStatus)) {
                $resultSet['rel']['assigned_to'] = $curUser;
                $resultSet['rel']['status_id'] = $curStatus;
            }
            else {
                throw new LogicException('Erro fatal de validação de integridade referêncial: o utilizador ou o estado atribuído não existe.');
            }
        }

    }

    /**
     * Ativa o projeto, guardando os detalhes na sessão. Por padrão, novas tarefas serão criadas neste projeto.
     * Os dados são sempre escritos por cima, então não é necessário "desativar" um projeto antes.
     * @param int $projectId O ID do projeto a ativar
     * @return void
     */
    public function activateProject(int $projectId): void
    {
        $project = $this->get_project($projectId);

        // Sempre inicializar os dados
        if (!isset($_SESSION['active_project'])) {
            $_SESSION['active_project'] = [];
        }

        $_SESSION['active_project']['title'] = $project['name'];
        $_SESSION['active_project']['description'] = $project['description'];
        $_SESSION['active_project']['deadline'] = $project['end_date'];
        $_SESSION['active_project']['status_name'] = $project['rel']['status_id']['name'];

        $_SESSION['active_project']['project_id'] = $project['id'];

        $this->publishEvent(current_username() . " selecionou o projeto {$project['name']} para criar novas tarefas.", 'update', 'project', $projectId);

    }

    /**
     * Devolve o projeto atualmente ativo.
     * @return array
     */
    public function getActiveProject(): array
    {
        if (isset($_SESSION['active_project']))
        {
            return $_SESSION['active_project'];
        }

        return [];
    }

    /**
     * Limpa o projeto atualmente ativo.
     * @return void
     */
    public function clearActiveProject(): void
    {
        unset($_SESSION['active_project']);
    }

}