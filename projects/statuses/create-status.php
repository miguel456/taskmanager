<?php
use App\Models\ProjectStatus\ProjectStatus;

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

$name = $_POST['status_name'];
$description = $_POST['description'];

if (empty($name) || empty($description)) {
    flash_message('Campos obrigatórios', 'Um ou mais campos estão em falta.', 'error');
    response('/projects/statuses');
    die;
}

$status = new ProjectStatus($name, $description);

if ($status->save()) {
    flash_message('Sucesso', 'Estado guardado com sucesso. Pode agora utilizá-lo num projeto.');
    response('/projects/statuses');
    die;
}

flash_message('Erro Interno', 'Por motivos desconhecidos, não foi possível guardar o novo estado. Tente novamente mais tarde.', 'error');
response('/project/statuses');
