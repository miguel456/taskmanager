<?php

use App\Models\Projects\Project;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

$project_id = $_POST['project_id'];
$project = new Project();


if (empty($project_id) && !$project->project_exists($project_id)) {
    flash_message('Dados inesperados', 'O projeto a apagar não existe ou não é válido.', 'error');
    response('/projects', 'Bad Request', [], 400);
    die;
}

if ($project->delete_project($project_id)) {
    flash_message('Sucesso', 'Projeto eliminado com sucesso.');
    $project->clearActiveProject();

    response('/projects');
    die;
}

flash_message('Erro Interno', 'Por motivos desconhecidos não foi possível apagar o projeto. Tente novamente mais tarde.', 'error');
response('/projects');