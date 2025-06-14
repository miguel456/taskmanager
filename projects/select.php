<?php

use App\Models\Projects\Project;

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$projects = new Project();
$pid = $_GET['pid'];

if (empty($pid) || !$projects->project_exists($pid)) {
    flash_message('Erro de validação', 'O projeto a ativar não existe ou é inválido.', 'error');
    response('/projects');
    die;
}

if (empty($projects->getActiveProject())) {
    flash_message('Projeto ativado', 'Novas tarefas serão agora criadas neste projeto.');
    $projects->activateProject($pid);
} else {
    flash_message('Projeto desativado', 'Novas tarefas não terão um projeto padrão, mas podem ser atribuídas a um mais tarde.');
    $projects->clearActiveProject();
}

response('/projects');


