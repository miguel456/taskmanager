<?php

use App\Models\Tasks\Tasks\Task;
use App\Models\TaskStatus\TaskStatus;
use App\Models\Users\User;

require_once realpath(__DIR__ . '/../vendor/autoload.php');

if (direct_check() || !is_logged_in()) {
    response('/tasks');
    die;
}

if (isset($_POST['update']) && $_POST['update'] == 1 || $_POST['update'] == 0) {
  $update = $_POST['update'];
} else {
    flash_message('Parâmetro obrigatório em falta', 'Um parâmetro obrigatório está em falta. Recarregue a página e tente novamente.', 'error');
    response('/tasks');
    die;
}




$taskName = trim($_POST['task_name'] ?? '');
$assignedUser = trim($_POST['task_owner'] ?? '');
$taskStatusId = trim($_POST['task_status_id'] ?? '');
$taskDescription = trim($_POST['task_description'] ?? '');
$taskPriority = trim($_POST['task_priority'] ?? '');
$dueDate = trim($_POST['due_date'] ?? '');

if ($update) {
    $taskId = $_POST['taskId'];
    $finishDate = trim($_POST['finish_date'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
}

$backTo = ($update) ? '/tasks/view-task.php?task=' . $taskId : '/tasks';

$taskStatuses = new TaskStatus();
$user = new User();
$task = new Task();

if ($dueDate !== '') {
    $d = DateTime::createFromFormat('Y-m-d\TH:i', $dueDate);
    if (!$d || $d->format('Y-m-d\TH:i') !== $dueDate) {
        flash_message('Erro de validação!', 'Data limite inválida.', 'error');
        response('/tasks');
        die;
    }
    $dueDate = $d->format('Y-m-d H:i:s');
}

$allowedPriorities = [
    'P0',
    'P1',
    'P2',
    'P3',
    'P4'
];

if (
    $taskName === '' ||
    $assignedUser === '' ||
    !is_string($assignedUser) ||
    $taskStatusId === '' ||
    $taskDescription === '' ||
    $taskPriority === '' ||
    ($update && ($finishDate === '' && $startDate === '')) ||
    $dueDate === '' && !in_array($dueDate, $allowedPriorities)
) {
    flash_message('Erro de validação!', 'Todos os campos são obrigatórios e têm de corresponder ao formato pretendido.', 'error');
    response($backTo);
    die;
}


if (empty($user->getUserById($assignedUser))) {
    // mensagem genérica, prevenir enumeração de utilizadores
    flash_message('Erro de validação!', 'O utilizador atribuído é inválido ou não pode receber tarefas.', 'error');
    response($backTo);
    die;
}

if (empty($taskStatuses->read($taskStatusId, false, true))) {
    flash_message('Erro de validação!', 'O estado atribuído não existe.', 'error');
    response($backTo);
    die;
}

if ($update && !$task->exists($taskId)) {
    flash_message('Erro de validação!', 'A tarefa a atualizar não existe.', 'error');
    response($backTo);
    die;
}

if (!$update) {

    $task = new Task($taskName, $assignedUser, $taskStatusId, $taskDescription, "", $taskPriority, $d);

    if ($task->create()) {
        flash_message('Tarefa criada.', 'Boa sorte!');
        response($backTo);
        die;
    }

    flash_message('Erro Interno', 'Ocorreu um erro interno e não foi possível guardar a tarefa.', 'error');
    response($backTo);
    die;
}

$curTask = $task->read($taskId, false);
$updateData = [
    'task_name' => $taskName,
    'task_owner' => $assignedUser,
    'task_status_id' => $taskStatusId,
    'task_description' => $taskDescription,
    'task_priority' => $taskPriority,
    'due_date' => $dueDate,
    'finish_date' => $finishDate,
    'start_date' => $startDate
];

if (isset($curTask['due_date']) && isset($updateData['due_date'])) {
    $curDueDate = new DateTime($curTask['due_date'])->format('Y-m-d H:i:s');
    $newDueDate = new DateTime($updateData['due_date'])->format('Y-m-d H:i:s');
    if ($curDueDate === $newDueDate) {
        unset($updateData['due_date']);
    } else {
        $updateData['due_date'] = $newDueDate;
    }
}

// Otimizar a consulta, porque não adianta atualizar tudo se só mudamos uma coisa.
// TODO: Abstraír esta lógica
foreach ($curTask as $fieldName => $data) {
    if (array_key_exists($fieldName, $updateData) && $data == $updateData[$fieldName]) {
        unset($updateData[$fieldName]);
    }
}


try {
    if ($task->update($taskId, $updateData)) {
        $msg = (count($updateData) == 1) ? ' campo atualizado.' : ' campos atualizados';
        flash_message('Tarefa atualizada!', count($updateData) . $msg);
    } else {
        flash_message('Erro Interno', 'Não foi possível atualizar a tarefa.', 'error');
    }
    response($backTo);
    die;

} catch (Exception $e) {
    flash_message('Erro Interno', 'Por motivos desconhecidos não foi possível atualizar a tarefa.', 'error');
    response($backTo);
}

