<?php

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

if (!is_ajax_request()) {
    ajax_response('Tipo de pedido inválido.', 'error');
}

$status_id = $_POST['status_id'] ?? null;

if (empty($status_id)) {
    ajax_response('Estado em falta.', 'error');
}

try {

    $taskStatus = new App\Models\TaskStatus\TaskStatus();
    $status = $taskStatus->read((int) $status_id, false, false);

    if (!$status) {
        ajax_response('Estado não encontrado.', 'error');
    }

    ajax_response('Estado encontrado.', 'success', $status);

} catch (Exception $exception) {
    ajax_response('Erro interno.', 'error', ['ex' => $exception->getMessage()]);
}