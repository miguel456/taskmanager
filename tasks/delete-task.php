<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use App\Models\Tasks\Tasks\Task;

if (!is_logged_in()) {
    response('/login.php');
    die;
}

if (direct_check()) {
    response('/tasks');
    die;
}

$taskId = $_POST['task_id'];

if (empty($taskId)) {
    flash_message('Tarefa não encontrada', 'A tarefa que tentou apagar não existe ou não é válida.', 'error');
    response('/tasks');
    die;
}


$tasks = new Task();
if ($tasks->exists($taskId) && $tasks->delete($taskId)) {
    flash_message('Tarefa eliminada', 'A tarefa foi eliminada com sucesso.');
} else {
    flash_message('Tarefa não encontrada', 'A tarefa que tentou apagar não existe ou não é válida', 'error');
}
response('/tasks');