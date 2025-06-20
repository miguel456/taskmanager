<?php

use App\Models\TaskStatus\TaskStatus;

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

if (!is_logged_in()) {
    response('/login.php');
    die;
}

$name = $_POST['status_name'];
$description = $_POST['description'];
$final = $_POST['final'];


if (empty($name) || empty($description) || !isset($_POST['final'])) {
    flash_message('Campos obrigatórios', 'Um ou mais campos estão em falta.', 'error');
    response('/tasks/statuses');
    die;
}

try {
    $status = new TaskStatus($name, $description, 1, $final);

    if ($status->create()) {
        flash_message('Sucesso', 'Estado guardado com sucesso. Pode agora utilizá-lo numa tarefa.');
        response('/tasks/statuses');
        die;
    }

} catch (InvalidArgumentException $exception) {
    flash_message('Erro ao criar estado', $exception->getMessage(), 'error');
    response('/tasks/statuses');
    die;
}

flash_message('Erro Interno', 'Por motivos desconhecidos, não foi possível guardar o novo estado. Tente novamente mais tarde.', 'error');
response('/tasks/statuses');
