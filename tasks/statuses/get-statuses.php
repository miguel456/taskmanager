<?php

require_once realpath(__DIR__ . '/../../vendor/autoload.php');


if (!is_ajax_request()) {
    http_response_code(400);
    response('/tasks/status');
    die;
}

try {
    $taskStatus = new App\Models\TaskStatus\TaskStatus();
    $statuses = $taskStatus->read(0, true, false);

    ajax_response('All statuses:', 'success', $statuses);

} catch (Exception $exception) {
    ajax_response('Erro interno:', 'error', ['ex' => $exception->getMessage()]);
}

