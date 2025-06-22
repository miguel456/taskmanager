<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use App\Models\Notification;

if (!is_logged_in()) {
    ajax_response('Sem sessão iniciada.', 'error');
}

if (!is_ajax_request()) {
    ajax_response('Tipo de pedido inválido', 'error');
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['ids']) || !is_array($data['ids'])) {
    ajax_response('Formato de dados inválido.', 'error');
}

$ids = array_filter($data['ids'], 'is_numeric');
if (empty($ids)) {
    ajax_response('Nenhum ID de notificação válido fornecido.', 'error');
}

$success = [];
$failed = [];

foreach ($ids as $id) {
    try {
        $notification = Notification::findByIdOrFail((int)$id);
        if ($notification->getNotifyee() == current_id()) {
            if (Notification::markRead($notification)) {
                $success[] = $id;
            } else {
                $failed[] = $id;
            }
        } else {
            $failed[] = $id;
        }
    } catch (Exception $e) {
        throw $e;
        $failed[] = $id;
    }
}

if (count($success) > 0) {
    ajax_response('Notificações marcadas como lidas', 'success', ['marked' => $success, 'failed' => $failed]);
} else {
    ajax_response('Nenhuma notificação marcada como lida.', 'error', ['failed' => $failed]);
}