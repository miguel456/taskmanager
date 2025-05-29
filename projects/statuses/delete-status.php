<?php
use App\Models\ProjectStatus\ProjectStatus;

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

$status_id = $_POST['status_id'];

if (empty($status_id)) {
    flash_message('Campo em falta', 'O estado que pretende apagar não existe, está a ser utilizado ou está inválido.', 'error');
    response('/projects/statuses');
    die;
}

$status = new ProjectStatus();

try {
    if ($status->status_exists($status_id) && $status->delete_status($status_id)) {
        flash_message('Sucesso', 'Estado apagado com sucesso.');
        response('/projects/statuses');
        die;
    }
    flash_message('Erro Interno', "Um ou mais motivos estão a impedir que o estado seja apagado. Tente novamente mais tarde.", 'error');
} catch (PDOException $exception) {
    flash_message('Erro Interno', "Um ou mais motivos estão a impedir que o estado seja apagado. Tente novamente mais tarde.", 'error');
}

response('/projects/statuses');