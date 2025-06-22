<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use App\Models\Tasks\Tasks\Task;

$tasks = new Task()->read();

if (php_sapi_name() !== 'cli' && !is_logged_in()) {
    response('/error/access-denied.html');
}

foreach($tasks as $task) {
    $dueDate = new \DateTime($task['due_date']);
    $today = new \DateTime();

    $service = new \App\Core\Services\NotificationService();

    $taskName = $task['task_name'];

    if ($today > $dueDate) {
        $service->setTitle('Tarefas em atraso')
            ->setMessage("Uma das suas tarefas está em atraso. Marque-a como concluída ou altere a data limite de conclusão ($taskName).")
            ->setDismissable(true)
            ->setUser($task['task_owner'])
            ->setMailable(false)
            ->notify(true, $task['id']);
             // esta função não permite que notificações sejam repetidas para a mesma tarefa
    }
}

if (php_sapi_name() == 'cli') {
    return 0;
}
else {
    header('HTTP/1.1 201 No Content');
    return;
}