<?php

use App\Models\Tasks\Tasks\Task;
use App\Models\TaskStatus\TaskStatus;
use App\Models\Users\User;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

if (!is_logged_in()) {
    response('/login.php');
    die;
}

// TODO: Filtrar com base no projeto ativo em sessão
$taskModel = new Task();
$userModel = new User();
$status = new TaskStatus();
$statuses = $status->read(0, true, true);

$tasks = $taskModel->read();



if (!empty($tasks)) {
    // TODO: Mover/abstraír esta lógica
    // adicionar os respetivos relacionamentos às $tasks originais
    foreach ($tasks as &$task) {
        $taskStatus = $status->read($task['task_status_id'], false, false);
        $task['rel']['task_status'] = empty($taskStatus) ? [] : $taskStatus;
        $task['rel']['task_owner'] = $userModel->getUserById($task['task_owner']);
    }
    unset($task);
}

?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../layout/head.php' ?>
<body>
<?php include_once '../layout/nav.php' ?>
<div class="main-content">
    <div class="container my-4">

        <!-- Project Info Section -->
        <?php
        // Placeholder for project data from session
        $project = [
            'title' => $_SESSION['active_project']['title'] ?? 'Projeto não selecionado',
            'description' => $_SESSION['active_project']['description'] ?? 'Selecione um projeto para começar a trabalhar; não vai conseguir criar tarefas novas sem selecionar um projeto.'
        ];
        ?>
        <div class="card mb-4 border-primary shadow-sm" style="background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);">
            <div class="card-body">
                <h3 class="card-title text-primary mb-1">
                    <i class="fas fa-folder-open me-2"></i>
                    <?php echo htmlspecialchars($project['title']); ?>
                </h3>
                <p class="card-text text-secondary fs-5 mb-0">
                    <?php echo htmlspecialchars($project['description']); ?>
                </p>
                <span class="badge bg-primary mt-2">Projeto ativo</span>
            </div>
        </div>

        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-list"></i> O seu trabalho</h5>
                <div class="btn-group" role="group" aria-label="Filtros de tarefas">
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2<?php echo empty($tasks) ? ' disabled' : ''; ?>" title="Filtrar por estado">
                        <i class="fas fa-filter"></i> Estado
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm ml-2<?php echo empty($tasks) ? ' disabled' : ''; ?>" title="Filtrar por data limite">
                        <i class="fas fa-calendar-day"></i> Data limite
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm ml-2<?php echo empty($tasks) ? ' disabled' : ''; ?>" title="Filtrar por prioridade">
                        <i class="fas fa-exclamation"></i> Prioridade
                    </button>
                </div>
            </div>
            <?php if (!empty($tasks)): ?>
                <table class="table table-hover text-center">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Estado</th>
                        <th>Data limite</th>
                        <th>Acções</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td>
                                <img src="https://ui-avatars.com/api/?name=User&background=dee2e6&color=495057&size=32" alt="User" class="rounded-circle" width="32" height="32">
                            </td>
                            <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                            <td><?php echo htmlspecialchars($task['task_description']); ?></td>
                            <td><?php echo htmlspecialchars($task['rel']['task_status']['name']); ?></td>
                            <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                            <td>
                                <form action="complete-task.php" method="POST">
                                    <input type="hidden" name="taskId" value="<?= $task['id'] ?>">
                                    <button class="btn btn-success" type="submit" id="task-finish-task-<?php echo $task['id'] ?>"><i class="fa-solid fa-circle-check"></i></button>
                                </form>
                                <button class="btn btn-info" type="button" id="view-button-task-<?php echo $task['id'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#taskDetailsModalTask<?php echo $task['id'] ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <img src="/img/relax.svg" height="150px" class="mb-4" alt="Duas pessoas a relaxar">
                <div class="alert alert-success">
                    <h4 class="alert-title"><i class="fa fa-check-double"></i><b> Não há tarefas!</b></h4>
                    <p>Não tem nenhuma tarefa para fazer. Ótima oportunidade para relaxar descansado! 👌</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="tool-separator mb-3 mt-5">
            <h4>Gerir tarefas</h4>
            <hr
        </div>

      <div class="accordion mb-5" id="newTaskAccordion">
          <div class="card">
              <div class="card-header" id="headingNewTask">
                  <h2 class="mb-0">
                      <button class="btn btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNewTask" aria-expanded="true" aria-controls="collapseNewTask">
                          Nova tarefa
                      </button>
                  </h2>
              </div>
              <div id="collapseNewTask" class="collapse" aria-labelledby="headingNewTask" data-bs-parent="#newTaskAccordion">
                  <div class="card-body">
                      <form method="POST" action="/tasks/manage-task.php">
                          <input type="hidden" name="update" value="0">
                          <div class="mb-3">
                              <label for="taskName" class="form-label">Nome da tarefa</label>
                              <input type="text" class="form-control" id="taskName" name="task_name" required>
                          </div>
                          <div class="mb-3">
                              <label for="assignedUser" class="form-label">Utilizador atribuído</label>
                              <input type="text" class="form-control" id="assignedUser" name="task_owner" placeholder="ID ou nome do utilizador">
                          </div>
                          <div class="mb-3">
                              <label for="taskStatus" class="form-label">Estado</label>
                              <select class="form-select" id="taskStatus" name="task_status_id" required>
                                  <?php foreach ($statuses as $item):  ?>
                                        <option value="<?php echo $item['id'] ?>"><?php echo $item['name']; ?></option>
                                  <?php endforeach; ?>
                              </select>
                          </div>
                          <div class="mb-3">
                              <label for="taskDescription" class="form-label">Descrição</label>
                              <textarea class="form-control" id="taskDescription" name="task_description" rows="2"></textarea>
                          </div>
                          <div class="mb-3">
                              <label for="taskPriority" class="form-label">Prioridade</label>
                              <select class="form-select" id="taskPriority" name="task_priority">
                                  <option value="P0">P0 (Crítica)</option>
                                  <option value="P1">P1 (Alta)</option>
                                  <option value="P2">P2 (Média)</option>
                                  <option value="P3">P3 (Baixa)</option>
                                  <option value="P4">P4 (Pouco Importante)</option>
                              </select>
                          </div>
                          <div class="mb-3">
                              <label for="dueDate" class="form-label">Data limite</label>
                              <input type="datetime-local" class="form-control" id="dueDate" name="due_date">
                          </div>
                          <button type="submit" class="btn btn-primary">Criar tarefa</button>
                      </form>
                  </div>
              </div>
          </div>
      </div>
    </div>
</div>


<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteProjectForm" method="POST" action="delete-task.php">
            <input type="hidden" name="task_id" id="deleteTaskId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar ação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem a certeza que pretende apagar esta tarefa? Esta ação é irreversível.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-can"></i> Confirmar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-ban"></i> Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php foreach($tasks as $task): ?>
    <div class="modal fade" id="taskDetailsModal<?php echo "Task" . $task['id'] ?>" tabindex="-1" aria-labelledby="taskDetailsModalLabel<?php echo "Task" . $task['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskDetailsModalLabel<?php echo "Task" . $task['id'] ?>">
                        <i class="fas fa-tasks"></i> Vista detalhada da tarefa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <div class="mb-3">
                                <i class="fas fa-heading me-2"></i>
                                <strong>Título:</strong> <?php echo $task['task_name'] ?>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-align-left me-2"></i>
                                <strong>Descrição:</strong> <?php echo $task['task_description'] ?>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-user me-2"></i>
                                <strong>Atribuído a:</strong> <?php echo $task['rel']['task_owner']['nome'] ?>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-flag me-2"></i>
                                <strong>Estado/Fase:</strong> <?php echo $task['rel']['task_status']['name'] ?>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Prioridade:</strong> <?php echo $task['task_priority'] ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <i class="fas fa-calendar-day me-2"></i>
                                <strong>Data limite de conclusão:</strong> <?php echo $task['due_date'] ?>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-play me-2"></i>
                                <strong>Data de início da tarefa:</strong> <?php echo $task['start_date'] ?>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Data de conclusão da tarefa:</strong> <?php echo $task['finish_date'] ?>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-hourglass-half me-2"></i>
                                <strong>Tempo gasto:</strong> <?php echo $task['time_spent'] ?? 'Sem registos'?>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-plus-circle me-2"></i>
                                <strong>Data de criação:</strong> <?php echo $task['created_at'] ?>
                            </div>
                            <div class="mb-3">
                                <i class="fas fa-edit me-2"></i>
                                <strong>Última atualização:</strong> <?php echo $task['updated_at'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" onclick="location.href='view-task.php?task=<?php echo $task['id'] ?>'">
                        <i class="fa-solid fa-pen-to-square"></i> Editar
                    </button>
                    <button type="button"
                            class="btn btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmDeleteModal"
                            data-task-id="<?php echo $task['id']; ?>">
                        <i class="fa-solid fa-eraser"></i> Apagar
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php endforeach; ?>

<?php
include '../layout/footer.php';
include  '../error/flash-messages.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var taskId = button.getAttribute('data-task-id');
            document.getElementById('deleteTaskId').value = taskId;
        });
    });

    <?php foreach($tasks as $task): ?>
    tippy('#task-finish-task-<?php echo $task['id'] ?>', {
        content: 'Terminar tarefa',
    });

    tippy('#view-button-task-<?php echo $task['id'] ?>', {
        content: 'Ver mais detalhes',
    });
    <?php endforeach; ?>

</script>
</body>
</html>