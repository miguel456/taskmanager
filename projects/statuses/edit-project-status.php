<?php
require_once realpath(__DIR__ . '/../../vendor/autoload.php');
use App\Models\ProjectStatus\ProjectStatus;

if (!is_ajax_request()) {
    ajax_response('Pedido inválido', 'error');
}

$status_id = $_POST['status_id'] ?? null;
$name = trim($_POST['status_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

if (empty($status_id) || $name === '' || $description === '') {
    ajax_response('Campos obrigatórios em falta.', 'error');
}

$projectStatus = new ProjectStatus();

if (!$projectStatus->status_exists($status_id)) {
    ajax_response('Estado não encontrado.', 'error');
}

try {
    $fieldsToUpdate = [
        'name' => $name,
        'description' => $description,
        'status' => $status
    ];
    $projectStatus->update_status($status_id, $fieldsToUpdate);
    ajax_response('Estado atualizado.', 'success');
} catch (Exception $e) {
    ajax_response($e->getMessage(), 'error');
}