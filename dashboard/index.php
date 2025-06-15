<?php
require_once realpath(__DIR__ . '/../app/bootstrap.php');
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

    <div class="row g-4">
        <!-- Task Status Pie Chart -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <i class="fas fa-chart-pie me-2"></i>Task Status Distribution
                </div>
                <div class="card-body">
                    <canvas id="statusPieChart" height="220"></canvas>
                    <!-- Provide: status counts (e.g., To Do, In Progress, Done) -->
                </div>
            </div>
        </div>
        <!-- Tasks Over Time Chart -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <i class="fas fa-chart-line me-2"></i>Tasks Over Time
                </div>
                <div class="card-body">
                    <canvas id="tasksLineChart" height="220"></canvas>
                    <!-- Provide: tasks created/completed per day/week -->
                </div>
            </div>
        </div>
    </div>

    <!-- Improved layout for the last row: each card is a direct child of a .col -->
    <div class="row g-4 mt-4 row-cols-1 row-cols-md-2">
        <!-- Recent Activity -->
        <div class="col">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <i class="fas fa-history me-2"></i>Recent Activity
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush" id="recentActivity">
                        <!--
                        Provide: array of recent activities, e.g.:
                        [
                            ['user' => 'Alice', 'action' => 'completed task', 'task' => 'Design UI', 'time' => '2 hours ago'],
                            ...
                        ]
                        -->
                        <li class="list-group-item">
                            <i class="fas fa-user-circle text-primary"></i>
                            <strong>Alice</strong> completed task <b>Design UI</b>
                            <span class="text-muted small">2 hours ago</span>
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-user-circle text-success"></i>
                            <strong>Bob</strong> added new task <b>Write Docs</b>
                            <span class="text-muted small">3 hours ago</span>
                        </li>
                        <!-- ... -->
                    </ul>
                </div>
            </div>
        </div>
        <!-- Quick Actions -->
        <div class="col">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </div>
                <div class="card-body d-flex flex-wrap gap-2">
                    <a href="/tasks/manage-task.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Task
                    </a>
                    <a href="/projects" class="btn btn-info">
                        <i class="fas fa-folder-open"></i> View Projects
                    </a>
                    <a href="/tasks" class="btn btn-secondary">
                        <i class="fas fa-list"></i> All Tasks
                    </a>
                    <a href="/reports" class="btn btn-warning">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </div>
            </div>
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white">
                    <i class="fas fa-calendar-day me-2"></i>Upcoming Deadlines
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush" id="upcomingDeadlines">
                        <!--
                        Provide: array of tasks with upcoming due dates, e.g.:
                        [
                            ['task' => 'Prepare Meeting', 'due' => '2024-06-15 14:00', 'project' => 'Project X'],
                            ...
                        ]
                        -->
                        <li class="list-group-item">
                            <i class="fas fa-clock text-danger"></i>
                            <b>Prepare Meeting</b> <span class="text-muted">in Project X</span>
                            <span class="badge bg-danger float-end">Due: 2024-06-15 14:00</span>
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-clock text-warning"></i>
                            <b>Review PR</b> <span class="text-muted">in Project Y</span>
                            <span class="badge bg-warning float-end">Due: 2024-06-16 10:00</span>
                        </li>
                        <!-- ... -->
                    </ul>
                </div>
            </div>
        </div>
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