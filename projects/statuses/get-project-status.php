<?php

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

use App\Models\ProjectStatus\ProjectStatus;

if (!is_ajax_request()) {
    ajax_response('Pedido inválido.', 'error');
}

$status_id = $_POST['status_id'] ?? null;
if (empty($status_id)) {
    ajax_response('ID do estado em falta.', 'error');
}

try {
    $projectStatus = new ProjectStatus();
    $status = $projectStatus->get_status((int)$status_id, false, false);
    if (!$status) {
        ajax_response('Estado não encontrado.', 'error');
    }
    ajax_response('Estado encontrado.', 'success', $status);
} catch (Exception $e) {
    ajax_response('Erro interno.', 'error', ['ex' => $e->getMessage()]);
}