<?php

use App\Models\TaskStatus\TaskStatus;

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

$status_id = $_GET['id'];
$status = new TaskStatus();

if (!empty($status->read($status_id, false, false))) {
    $status_data = $status->read($status_id, false, false);

    if ($status_data['status']) {
        $op = $status->update($status_id, ['status' => 0]);
    } else {
        $op = $status->update($status_id, ['status' => 1]);
    }

    response('/tasks/statuses');
    die;
}

flash_message('Erro Interno', 'Não foi possível alterar o estado do estado. Tente novamente mais tarde', 'error');
response('/tasks/statuses');