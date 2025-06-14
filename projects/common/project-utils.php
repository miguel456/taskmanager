<?php

use App\Models\ProjectStatus\ProjectStatus;
use App\Models\Users\User;

require_once realpath(__DIR__ . '/../../app/bootstrap.php');

/**
 * Valida os inputs de um projeto. Escreve mensagens no saco de mensagens em caso de erro.
 * @param ProjectStatus $status Um objeto de estado do projeto.
 * @param int $assignedTo
 * @param mixed $status_code O ID do estado do projeto
 * @param mixed $project_name O nome do projeto
 * @param mixed $description A descrição do projeto
 * @param string $start_date Dt. de inicio, yyyy-mm-dd hh:mm:ss
 * @param string $end_date Dt. de fim
 * @return void
 */
function validate_project_data(ProjectStatus $status, int $assignedTo,  mixed $status_code, mixed $project_name, mixed $description, string $start_date, string $end_date): void
{
    $user = new User();

    if (!$status->status_exists($status_code)) {
        flash_message('Dados inválidos', 'O "Estado" selecionado não é válido para o projeto atual ou encontra-se inativo.', 'error');
    }

    $user = $user->getUserById($assignedTo);
    if (empty($assignedTo) && empty($user)) {
        flash_message('Dados inválidos', 'O utilizador atribuído não é válido ou não existe.', 'error');
    }

    if ($user['estado'] !== 1) {
        flash_message('Utilizador não elegível', 'O utilizador atribuído não está ativo e consequentemente não pode ser atribuído a projetos.', 'error');
    }

    if (empty($project_name)) {
        flash_message('Campo obrigatório', 'O nome do projeto é obrigatório.', 'error');
    }

    if (empty($description)) {
        flash_message('Campo obrigatório', 'A descrição do projeto é obrigatória.', 'error');
    }
    // strtotime irá tornar estes dois em FALSE, gerando este erro
    if (empty($start_date) || empty($end_date)) {
        flash_message('Campo obrigatório', 'A data prevista de inicio e fim é obrigatória e tem de corresponder a um formato válido, ex. dd/mm/yyy.', 'error');
    }

    if (empty($status_code) && !$status->status_exists($status_code)) {
        flash_message('Campo obrigatório', 'O estado do projeto é obrigatório e tem de ser um estado existente e aceitável.', 'error');
    }
}