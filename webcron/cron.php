<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$tasks = new \App\Models\Tasks\Tasks\Task();

foreach($tasks as $task) {
    $dueDate = new \DateTime($task['due_date']);
    $today = new \DateTime();

    $service = new \App\Core\Services\NotificationService();

    $taskName = $task['task_name'];

    if ($dueDate > $today) {
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
    http_send_status(201);
    return;
}