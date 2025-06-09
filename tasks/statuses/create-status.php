<?php

use App\Models\TaskStatus\TaskStatus;

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

$name = $_POST['status_name'];
$description = $_POST['description'];

if (empty($name) || empty($description)) {
    flash_message('Campos obrigatórios', 'Um ou mais campos estão em falta.', 'error');
    response('/tasks/statuses');
    die;
}

$status = new TaskStatus($name, $description);

if ($status->create()) {
    flash_message('Sucesso', 'Estado guardado com sucesso. Pode agora utilizá-lo numa tarefa.');
    response('/tasks/statuses');
    die;
}

flash_message('Erro Interno', 'Por motivos desconhecidos, não foi possível guardar o novo estado. Tente novamente mais tarde.', 'error');
response('/tasks/statuses');
