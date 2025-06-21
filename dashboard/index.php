<?php

use App\Models\History;
use App\Models\Projects\Project;
use App\Models\Tasks\Tasks\Task;
use App\Models\TaskStatus\TaskStatus;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

$history = History::all();

$task = new Task();
$tasks = $task->read();

$taskStatus = new TaskStatus();
$taskStatuses = $taskStatus->read(0, true, false);

$projects = new Project();

$completingTaskStatus = $taskStatus->getFinalStatus();

// contagens dos widgets do dashboard
$totalTasks = count($tasks);
$totalComplete = 0;
$totalOverdue = 0;
$totalActiveProjects = count($projects->get_project(0, true));

foreach ($tasks as $task) {
    if ($task['task_status_id'] == $completingTaskStatus['id']) {
        $totalComplete++;
    }

    if (isset($task['due_date']) && (new DateTime($task['due_date'])) < new DateTime() && $task['task_status_id'] != $completingTaskStatus['id']) {
        $totalOverdue++;
    }
}

$statusCounts = [];
foreach ($taskStatuses as $status) {

    $statusName = $status['name'];
    $statusId = $status['id'];
    $statusCounts[$statusName] = 0;

    foreach ($tasks as $task) {
        if ($task['task_status_id'] == $statusId) {
            $statusCounts[$statusName]++;
        }
    }
}

$tasksByDay = [
    'Mon' => 0,
    'Tue' => 0,
    'Wed' => 0,
    'Thu' => 0,
    'Fri' => 0,
    'Sat' => 0,
    'Sun' => 0,
];

$completedTasksByDay = [
    'Mon' => 0,
    'Tue' => 0,
    'Wed' => 0,
    'Thu' => 0,
    'Fri' => 0,
    'Sat' => 0,
    'Sun' => 0,
];

foreach ($tasks as $task) {
    if (!empty($task['created_at'])) {
        $date = new DateTime($task['created_at']);
        $day = $date->format('D'); // Mon, Tue, etc.
        if (isset($tasksByDay[$day])) {
            $tasksByDay[$day]++;
        }
    }

    if (
        isset($task['task_status_id'], $task['updated_at']) &&
        $task['task_status_id'] == $completingTaskStatus['id']
    ) {
        $date = new DateTime($task['updated_at']);
        $day = $date->format('D');
        if (isset($completedTasksByDay[$day])) {
            $completedTasksByDay[$day]++;
        }
    }

}



?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../layout/head.php' ?>
<body>
<?php include_once '../layout/nav.php' ?>

<div class="container-fluid py-4">
    <div class="row g-2 mb-4">
        <div class="col d-flex justify-content-center">
            <?php include_once '../layout/project-status-bar.php' ?>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-tasks fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Tarefas totais</h6>
                        <h3 class="fw-bold mb-0" id="totalTasks"><?= $totalTasks ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Terminadas</h6>
                        <h3 class="fw-bold mb-0" id="completedTasks"><?= $totalComplete ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Atrasadas</h6>
                        <h3 class="fw-bold mb-0" id="overdueTasks"><?= $totalOverdue ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-project-diagram fa-2x text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Active Projects</h6>
                        <h3 class="fw-bold mb-0" id="activeProjects"><?= $totalActiveProjects ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- Task Status Pie Chart -->
        <div class="col-md-4 d-flex justify-content-center">
            <div class="card shadow-sm border-0 h-100 w-100">
                <div class="card-header bg-white">
                    <i class="fas fa-chart-pie me-2"></i>Distribuição dos estados das tarefas
                </div>
                <div class="card-body">
                    <canvas id="statusPieChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <!-- Tasks Over Time Chart -->
        <div class="col-md-4 d-flex justify-content-center">
            <div class="card shadow-sm border-0 h-100 w-100">
                <div class="card-header bg-white">
                    <i class="fas fa-chart-line me-2"></i>Tasks Over Time
                </div>
                <div class="card-body">
                    <canvas id="tasksLineChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <!-- Recent Activities Widget -->
        <?php if (!empty($history)): ?>
            <div class="col-md-4 d-flex justify-content-center">
                <div class="card shadow-sm border-0 h-100 w-100">
                    <div class="card-header bg-white d-flex align-items-center">
                        <i class="fas fa-history me-2 text-primary"></i>
                        <span class="fw-bold">Atividade recente</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach (array_slice($history, 0, 4) as $item): ?>
                            <?php
                            $icon = [
                                'create' => ['fa-plus-circle', 'text-success'],
                                'update' => ['fa-edit', 'text-warning'],
                                'delete' => ['fa-trash-alt', 'text-danger'],
                            ][$item->getAction()] ?? ['fa-info-circle', 'text-secondary'];

                            $typeBadge = $item->getType() === 'task'
                                ? 'bg-primary'
                                : 'bg-info';

                            $date = $item->getCreatedAt()->format('d M Y H:i');
                            ?>
                            <li class="list-group-item d-flex align-items-center">
                        <span class="me-3">
                            <i class="fas <?= $icon[0] ?> fa-lg <?= $icon[1] ?>"></i>
                        </span>
                                <div class="flex-grow-1">
                                    <div>
                                        <span class="fw-semibold"><?= ucfirst($item->getAction()) ?></span>
                                        <span class="badge <?= $typeBadge ?> ms-2"><?= ucfirst($item->getType()) ?></span>
                                        <span class="badge bg-light text-dark border ms-2">
                                    <i class="far fa-calendar-alt me-1"></i><?= $date ?>
                                </span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <?= htmlspecialchars($item->getDescription()) ?>
                                    </div>
                                    <div class="small mt-1">
                                        <i class="fas fa-user-circle me-1"></i>
                                        <?= htmlspecialchars($item->authorUser['nome'] ?? 'Desconhecido') ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>



</div>

<?php
include '../layout/footer.php';
include '../error/flash-messages.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const statusData = {
        labels: <?= json_encode(array_keys($statusCounts)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($statusCounts)) ?>,
            backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#6c757d', '#fd7e14', '#20c997'],
        }]
    };
    const statusPieChart = new Chart(document.getElementById('statusPieChart'), {
        type: 'doughnut',
        data: statusData,
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    const tasksLineData = {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [
            {
                label: 'Created',
                data: <?= json_encode(array_values($tasksByDay)) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                tension: 0.4,
            },
            {
                label: 'Completed',
                data: <?= json_encode(array_values($completedTasksByDay)) ?>,
                borderColor: '#198754',
                backgroundColor: 'rgba(25,135,84,0.1)',
                tension: 0.4,
            }
        ]
    };
    const tasksLineChart = new Chart(document.getElementById('tasksLineChart'), {
        type: 'line',
        data: tasksLineData,
        options: { responsive: true, plugins: { legend: { position: 'bottom' }, scales: { y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 }} } } }
    });
</script>
</body>
</html>