<?php

use App\Models\Projects\Project;
use App\Models\ProjectStatus\ProjectStatus;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

if (!is_logged_in()) {
    response('/login.php', 'Unauthorized', [], 401);
    die;
}

$project_name = $_POST['project_name'];
$description = $_POST['description'];
$start_date = date('Y-m-d H:i:s', strtotime($_POST['start_date']));
$end_date = date('Y-m-d H:i:s', strtotime($_POST['end_date']));
$status_code = $_POST['status'];

$project = new Project();
$status = new ProjectStatus();

if (!$status->status_exists($status_code)) {
    flash_message('Dados inválidos', 'O "Estado" selecionado não é válido para o projeto atual ou encontra-se inativo.', 'error');
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
    flash_message('Campo obrigatório', 'O estado do projeto é obrigatório e tem de ser um estado existente e aceitável.');
}

if (!bag_has_message()) {

    $projectId = $project->setName($project_name)
        ->setDescription($description)
        ->setAssignedTo($_SESSION['id'])
        ->setStartDate($start_date)
        ->setEndDate($end_date)
        ->setStatusId($status_code)
        ->save();

    if ($projectId[0]) {
        flash_message('Operação bem-sucedida', 'Projeto registado com sucesso. Pode agora começar a atribuir-lhe tarefas.');
        response('/projects');
        die;
    }

    flash_message('Erro Interno', 'Ocorreu um erro ao registar este projeto. Tente novamente mais tarde.');
    response('/projects', 'Internal Server Error', [], '500');
    die;

}

// Contém erros de cliente definidos acima, porque há mensagens no saco; antes de iniciarmos o processamento, não devia haver mensagens
response('/projects', 'Bad Request', [], 400);
die;