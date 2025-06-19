<?php

use App\Models\History;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

$history = History::all();

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
                        <h6 class="mb-0">Total Tasks</h6>
                        <h3 class="fw-bold mb-0" id="totalTasks">--</h3>
                        <!-- Provide: total number of tasks -->
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
                        <h6 class="mb-0">Completed</h6>
                        <h3 class="fw-bold mb-0" id="completedTasks">--</h3>
                        <!-- Provide: number of completed tasks -->
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
                        <h6 class="mb-0">Overdue</h6>
                        <h3 class="fw-bold mb-0" id="overdueTasks">--</h3>
                        <!-- Provide: number of overdue tasks -->
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
                        <h3 class="fw-bold mb-0" id="activeProjects">--</h3>
                        <!-- Provide: number of active projects -->
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
                    <i class="fas fa-chart-pie me-2"></i>Task Status Distribution
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

<!-- Chart.js for charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Example data for charts (replace with PHP variables)
    const statusData = {
        labels: ['To Do', 'In Progress', 'Done'],
        datasets: [{
            data: [10, 7, 15], // Provide: counts for each status
            backgroundColor: ['#0d6efd', '#ffc107', '#198754'],
        }]
    };
    const statusPieChart = new Chart(document.getElementById('statusPieChart'), {
        type: 'doughnut',
        data: statusData,
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    const tasksLineData = {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], // Provide: days or dates
        datasets: [
            {
                label: 'Created',
                data: [2, 4, 3, 5, 2, 1, 0], // Provide: tasks created per day
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                tension: 0.4,
            },
            {
                label: 'Completed',
                data: [1, 2, 2, 3, 1, 0, 1], // Provide: tasks completed per day
                borderColor: '#198754',
                backgroundColor: 'rgba(25,135,84,0.1)',
                tension: 0.4,
            }
        ]
    };
    const tasksLineChart = new Chart(document.getElementById('tasksLineChart'), {
        type: 'line',
        data: tasksLineData,
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
</script>
</body>
</html>