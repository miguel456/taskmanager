<?php

use App\Models\TaskStatus\TaskStatus;

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

if (!is_ajax_request()) {
    ajax_response('Tipo de pedido inválido.', 'error');
}

$status_id = $_POST['status_id'] ?? null;
$name = trim($_POST['status_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$final = isset($_POST['final']) ? (int)$_POST['final'] : 0;

if (empty($status_id) || $name === '' || $description === '') {
    ajax_response('Campos obrigatórios em falta.', 'error');
}

$status = new TaskStatus();

if (!$status->exists($status_id)) {
    ajax_response('Estado não encontrado.', 'error');
}

if ($status->finalExists() && $final === 1) {
    ajax_response('Já existe um estado marcado como final!', 'error');
}

try {
    $fieldsToUpdate = [
        'name' => $name,
        'description' => $description,
        'final' => $final
    ];
    $status->update($status_id, $fieldsToUpdate);
    ajax_response('Estado atualizado.', 'success');
} catch (\Exception $e) {
    ajax_response($e->getMessage(), 'error');
}