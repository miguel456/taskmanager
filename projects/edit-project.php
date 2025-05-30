<?php

use App\Models\Projects\Project;
use App\Models\ProjectStatus\ProjectStatus;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

if (!is_logged_in()) {
    response('/login.php', 'Unauthorized', [], 401);
    die;
}

$pid = $_POST['pid'];
$project_name = $_POST['project_name'];
$description = $_POST['description'];
$start_date = date('Y-m-d H:i:s', strtotime($_POST['start_date']));
$end_date = date('Y-m-d H:i:s', strtotime($_POST['end_date']));
$status_code = $_POST['status'];

$project = new Project();
$status = new ProjectStatus();

if (empty($pid) || !$project->project_exists($pid)) {
    flash_message('Projeto inexistente', 'Não é possível editar um projeto inválido/inexistente.', 'error');
    response('/projects');
    die;
}

if (empty($status->get_status(0, true, true))) {
    flash_message('Impossível editar projeto', 'Não pode editar um projeto sem estados disponíveis.', 'error');
    response('/projects');
    die;
}

validate_project_data($status, $status_code, $project_name, $description, $start_date, $end_date);

if (!bag_has_message()) {

    $toUpdate = [
        'name' => $project_name,
        'description' => $description,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'status_id' => $status_code
    ];

    if ($project->update_project($pid, $toUpdate)) {
        flash_message('Operação bem-sucedida', 'Projeto editado com sucesso.');
        response('/projects/edit.php?pid=' . $pid);
        die;
    }

    flash_message('Erro Interno', 'Ocorreu um erro ao editar este projeto. Tente novamente mais tarde.');
    response('/projects', 'Internal Server Error', [], '500');
    die;

}

response('/projects/edit.php?pid=' . $pid, 'Bad Request', [], 400);
die;