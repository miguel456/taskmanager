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

if (empty($status->get_status(0, true, true))) {
    flash_message('Impossível adicionar projeto', 'Não pode registar um projeto sem estados disponíveis.', 'error');
    response('/projects');
    die;
}

validate_project_data($status, $status_code, $project_name, $description, $start_date, $end_date);

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