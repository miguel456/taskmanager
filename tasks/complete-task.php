<?php

use App\Core\Utilities\HistoryLog;
use App\Models\Tasks\Tasks\Task;

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$taskId = $_POST['taskId'];

$task = new Task();
$taskStatus = new App\Models\TaskStatus\TaskStatus();

if (empty($taskId) || !$task->exists($taskId)) {
    flash_message('Tarefa desconhecida.',  'A tarefa referida não foi encontrada.', 'error');
    response('/tasks');
    die;
}

$finalStatus = $taskStatus->getFinalStatus();

if (empty($finalStatus)) {
    flash_message('Sem estados disponíveis ):', 'Não há estado que possa marcar tarefas como completas. Crie (ou edite) um estado e marque-o como finalizador.', 'error');
    response('/tasks');
    die;
}

try {
    if ($task->update($taskId, ['task_status_id' => $finalStatus['id']])) {
        flash_message('Tarefa concluída.', 'Bom trabalho!');
        HistoryLog::taskUpdated("A tarefa {$taskId} foi concluída.", current_id(), $taskId);

    } else {
        flash_message('Erro Interno', 'Não foi possível marcar a tarefa como concluída.', 'error');
    }
    response('/tasks');
    die;
} catch (Exception $e) {
    flash_message('Erro Interno', 'Não foi possível marcar a tarefa como concluída.', 'error');
    response('/tasks');
    die;
}