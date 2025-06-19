<?php

use App\Models\Tasks\Tasks\Task;
use App\Models\TaskStatus\TaskStatus;
use App\Models\Users\User;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

if (!is_logged_in()) {
    response('/login.php');
    die;
}

$taskModel = new Task();
$userModel = new User();
$projectModel = new App\Models\Projects\Project();
$status = new TaskStatus();

$statuses = $status->read(0, true, true);
$projects = $projectModel->get_project(0, true);
$activeProject = $projectModel->getActiveProject();

$tasks = $taskModel->read();
$users = $userModel->getAllUsers(true);

?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../layout/head.php' ?>
<body>
<?php include_once '../layout/nav.php' ?>
<div class="main-content">
    <div class="container my-4">
        <?php include_once '../layout/project-status-bar.php' ?>

        <!-- Filter Controls -->
        <div class="card mb-4 shadow-sm p-3">
            <form id="taskFilterForm" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="filterProject" class="form-label">Projeto</label>
                    <select class="form-select" id="filterProject" name="project">
                        <option value="">Todos os projetos</option>
                        <?php foreach($projects as $project): ?>
                            <option value="<?= $project['id'] ?>"><?= $project['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterStatus" class="form-label">Estados</label>
                    <select class="form-select" id="filterStatus" name="status">
                        <option value="">Todos os estados</option>
                        <?php foreach($statuses as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterPriority" class="form-label">Prioridade</label>
                    <select class="form-select" id="filterPriority" name="priority">
                        <option value="">Tudo</option>
                        <option value="P0">P0 (Crítico)</option>
                        <option value="P1">P1 (Alto)</option>
                        <option value="P2">P2 (Médio)</option>
                        <option value="P3">P3 (Baixo)</option>
                        <option value="P4">P4 (Indiferente)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterDueDate" class="form-label">Dt. limite</label>
                    <input type="date" class="form-control" id="filterDueDate" name="due_date">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary w-100" id="applyFilters"><i class="fas fa-filter"></i> Aplicar filtros</button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#newTaskModal"><i class="fas fa-plus"></i></button>
                </div>
            </form>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (!empty($tasks)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tasksTable">
                            <thead class="table-light">
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr class="task-row" data-task-id="<?= $task['id'] ?>"
                                    data-project="<?= $task['rel']['project_id']['id'] ?? '' ?>"
                                    data-status="<?= $task['rel']['task_status_id']['id'] ?>"
                                    data-priority="<?= $task['task_priority'] ?>"
                                    data-due-date="<?= substr($task['due_date'], 0, 10) ?>">
                                    <td>
                                        <img id="taskOwnerProfileId<?= $task['rel']['task_owner']['iduser'] ?>" src="https://ui-avatars.com/api/?name=<?= $task['rel']['task_owner']['nome'] ?>&background=dee2e6&color=495057&size=32" alt="User" class="rounded-circle user-img" width="32" height="32">
                                    </td>
                                    <td><?= htmlspecialchars($task['task_name']) ?></td>
                                    <td><?= htmlspecialchars($task['task_description']) ?></td>
                                    <td><?= htmlspecialchars($task['rel']['project_id']['name'] ?? 'None') ?></td>
                                    <td><?= htmlspecialchars($task['rel']['task_status_id']['name']) ?></td>
                                    <td><?= htmlspecialchars($task['due_date']) ?></td>
                                    <td>
                                    <span class="badge bg-<?= $task['task_priority'] === 'P0' ? 'danger' : ($task['task_priority'] === 'P1' ? 'warning' : ($task['task_priority'] === 'P2' ? 'info' : 'secondary')) ?>">
                                        <?= $task['task_priority'] ?>
                                    </span>
                                    </td>
                                    <td>
                                        <form action="complete-task.php" method="POST" class="d-inline">
                                            <input type="hidden" name="taskId" value="<?= $task['id'] ?>">
                                            <button class="btn btn-success btn-sm btn-complete" type="submit" title="Terminar tarefa"><i class="fa-solid fa-circle-check"></i></button>
                                        </form>
                                        <button class="btn btn-info btn-sm open-details-panel" type="button" title="Detalhes" data-task-id="<?= $task['id'] ?>">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center">
                        <img src="/img/relax.svg" height="150px" class="mb-4" alt="Relax">
                        <div class="alert alert-success">
                            <h4 class="alert-title"><i class="fa fa-check-double"></i> Não há tarefas!</h4>
                            <p>Não há tarefas a fazer! #inboxzero 👌</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="taskDetailsPanel" aria-labelledby="taskDetailsPanelLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="taskDetailsPanelLabel"><i class="fas fa-tasks"></i> Detalhes da tarefa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="taskDetailsContent">

    </div>
</div>

<div class="modal fade" id="newTaskModal" tabindex="-1" aria-labelledby="newTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form id="newtask" method="POST" action="/tasks/manage-task.php" autocomplete="off">
                <input type="hidden" name="update" value="0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="newTaskModalLabel">
                        <i class="fas fa-plus-circle me-2"></i> Nova tarefa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskName" class="form-label">Título</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-heading"></i></span>
                                    <input type="text" class="form-control" id="taskName" name="task_name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="assignedUser" class="form-label">Responsável</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                    <select class="form-select" name="task_owner" id="assignedUser">
                                        <?php foreach($users as $user): ?>
                                            <option value="<?= $user['iduser'] ?>"><?= $user['nome'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="taskStatus" class="form-label">Estado</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-flag"></i></span>
                                    <select class="form-select" id="taskStatus" name="task_status_id" required>
                                        <?php foreach ($statuses as $item):  ?>
                                            <option value="<?php echo $item['id'] ?>"><?php echo $item['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="project_id" class="form-label">Projeto</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-project-diagram"></i></span>
                                    <select class="form-select" name="project_id" id="project_id">
                                        <option disabled>Selecione um projeto</option>
                                        <option value="0">Nenhum projeto</option>
                                        <?php foreach($projects as $project): ?>
                                            <option value="<?= $project['id'] ?>"
                                                <?php if (!empty($activeProject) && $activeProject['project_id'] == $project['id']) echo 'selected'; ?>>
                                                <?= $project['name'] ?>
                                                <?php if (!empty($activeProject) && $activeProject['project_id'] == $project['id']) echo ' (Projeto selecionado)'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskDescription" class="form-label">Descrição</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-align-left"></i></span>
                                    <textarea class="form-control" id="taskDescription" name="task_description" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="taskPriority" class="form-label">Prioridade</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-exclamation-circle"></i></span>
                                    <select class="form-select" id="taskPriority" name="task_priority">
                                        <option value="P0">P0 (Crítica)</option>
                                        <option value="P1">P1 (Alta)</option>
                                        <option value="P2">P2 (Média)</option>
                                        <option value="P3">P3 (Baixa)</option>
                                        <option value="P4">P4 (Pouco Importante)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="dueDate" class="form-label">Prazo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="datetime-local" class="form-control" id="dueDate" name="due_date">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Submeter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.getElementById('applyFilters').addEventListener('click', function () {
        const project = document.getElementById('filterProject').value;
        const status = document.getElementById('filterStatus').value;
        const priority = document.getElementById('filterPriority').value;
        const dueDate = document.getElementById('filterDueDate').value;
        document.querySelectorAll('#tasksTable tbody tr').forEach(row => {
            let show = true;
            if (project && row.dataset.project !== project) show = false;
            if (status && row.dataset.status !== status) show = false;
            if (priority && row.dataset.priority !== priority) show = false;
            if (dueDate && row.dataset.dueDate > dueDate) show = false;
            row.style.display = show ? '' : 'none';
        });
    });

    // Offcanvas details panel logic
    document.querySelectorAll('.open-details-panel').forEach(btn => {
        btn.addEventListener('click', function () {
            const taskId = this.dataset.taskId;
            const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
            document.getElementById('taskDetailsContent').innerHTML = `
                <div>
                    <h5>${row.children[1].textContent}</h5>
                    <p>${row.children[2].textContent}</p>
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><b>Projeto:</b> ${row.children[3].textContent}</li>
                        <li class="list-group-item"><b>Estado:</b> ${row.children[4].textContent}</li>
                        <li class="list-group-item"><b>Dt. limite:</b> ${row.children[5].textContent}</li>
                        <li class="list-group-item"><b>Prioridade:</b> ${row.children[6].textContent}</li>
                    </ul>
                   <button class="btn btn-warning me-2" onclick="location.href='view-task.php?task=${taskId}'">
                       <i class="fa-solid fa-pen-to-square"></i> Mais detalhes
                   </button>
                   <button class="btn btn-danger" onclick="if(confirm('Tem a certeza que pretende eliminar esta tarefa?')) location.href='delete-task.php?task=${taskId}'">
                       <i class="fa-solid fa-trash"></i> Eliminar
                   </button>
                </div>
            `;
            new bootstrap.Offcanvas(document.getElementById('taskDetailsPanel')).show();
        });
    });
</script>

<?php
include '../layout/footer.php';
include '../error/flash-messages.php';
?>


<script>
    tippy('.btn-complete', {
        content: 'Terminar tarefa',
    });

    tippy('.open-details-panel', {
        content: 'Ver mais detalhes',
    });

    <?php foreach ($tasks as $task): ?>
        tippy('#taskOwnerProfileId<?= $task['rel']['task_owner']['iduser'] ?>', {
            content: '<?= $task['rel']['task_owner']['nome'] ?>'
        });
    <?php endforeach; ?>
</script>

</body>
</html>