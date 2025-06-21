<?php

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

use App\Models\ProjectStatus\ProjectStatus;

if (!is_ajax_request()) {
    http_response_code(400);
    response('/projects/status');
    die;
}

try {
    $projectStatus = new ProjectStatus();
    $statuses = $projectStatus->get_status(0, true, false);
    ajax_response('Todos os estados:', 'success', $statuses);
} catch (Exception $e) {
    ajax_response('Erro interno', 'error', ['ex' => $e->getMessage()]);
}