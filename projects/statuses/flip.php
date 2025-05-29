<?php
use App\Models\ProjectStatus\ProjectStatus;

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

$status_id = $_GET['id'];
$status = new ProjectStatus();

if ($status->status_exists($status_id)) {
   $status_data = $status->get_status($status_id);

   if ($status_data['status']) {
       $op = $status->update_status($status_id, ['status' => 0]);
   } else {
       $op = $status->update_status($status_id, ['status' => 1]);
   }

   response('/projects/statuses');
   die;
}

flash_message('Erro Interno', 'Não foi possível alterar o estado do estado. Tente novamente mais tarde', 'error');
response('/projects/statuses');