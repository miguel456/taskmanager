<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use App\Models\Comment;
use App\Models\Projects\Project;
use App\Models\Tasks\Tasks\Task;
use App\Models\TaskStatus\TaskStatus;
use App\Models\Users\User;

$curPage = '/tasks';
$taskId = $_GET['task'];

$commentable = 'task';
$commentableId = $taskId;

if (empty($taskId)) {
    flash_message('Tarefa não encontrada', 'A tarefa que está a tentar visualizar não existe, ou uma tarefa não foi fornecida.', 'error');
    response($curPage);
    die;
}

$tasks = new Task();
$user = new User();
$project = new Project();

$task = $tasks->read($taskId, false);

$taskStatus = new TaskStatus();

if (empty($task)) {

    flash_message('Tarefa não encontrada', 'A tarefa que está a tentar visualizar não existe, ou uma tarefa não foi fornecida.', 'error');
    response($curPage);
    die();

}

$singleTaskStatus = $taskStatus->read($task['task_status_id'], false, true);
$users = $user->getAllUsers(true);
$projects = $project->get_project(0, true);


try {
    $comments = Comment::getTaskComments($taskId);
} catch (Exception $e) {
    flash_message('Comentários indisponíveis', 'Não foi possível obter comentários para esta tarefa (' . $e->getMessage() . ').', 'error');
}

// TODO: Permissões futuras
$formStatus = true;

?>

<!DOCTYPE html>
<html lang="en">
<?php include_once '../layout/head.php' ?>
<body>
<?php include_once '../layout/nav.php' ?>

<div class="container mt-5">
    <!-- Placeholder Illustration -->
    <div class="text-center mb-4">
        <img src="/img/task.svg" alt="Task Illustration Placeholder" class="img-fluid" width="250" height="150">
    </div>

    <!-- Task Details Row -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Vista detalhada da tarefa</h5>
                    <small class="text-muted">Pode editar todos os detalhes da tarefa aqui.</small>
                </div>
                <form action="manage-task.php" method="post">
                    <input type="hidden" name="update" value="1">
                    <input type="hidden" name="taskId" value="<?= $task['id'] ?>">
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column: Task Info -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="task_name" class="form-label">Título da tarefa</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-square-pen"></i></span>
                                        <input type="text" class="form-control" id="task_name" name="task_name" value="<?= htmlspecialchars($task['task_name']) ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="task_description" class="form-label">Descrição</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
                                        <textarea class="form-control" id="task_description" name="task_description"><?= htmlspecialchars($task['task_description']) ?></textarea>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="task_priority" class="form-label">Priority</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-circle-question"></i></span>
                                        <select id="task_priority" name="task_priority" class="form-select">
                                            <?php for ($i = 0; $i <= 4; $i++): ?>
                                                <option value="P<?= $i ?>" <?= $task['task_priority'] === "P$i" ? 'selected' : '' ?>>P<?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="task_status_id" class="form-label">Estado atual</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-clipboard"></i></span>
                                        <select class="form-control" name="task_status_id" id="task_status_id">
                                            <option value="<?= htmlspecialchars($task['rel']['task_status_id']['id']) ?>"><?= htmlspecialchars($task['rel']['task_status_id']['name']) ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="project_id" class="form-label">Projeto atribuído</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-diagram-project"></i></span>
                                        <select class="form-control" name="project_id" id="project_id">
                                            <option disabled>Selecione um projeto</option>
                                            <option value="0" <?= empty($task['rel']['project_id']) ? 'selected' : '' ?>>Nenhum projeto</option>
                                            <?php foreach($projects as $project): ?>
                                                <option value="<?= $project['id'] ?>" <?= (!empty($task['rel']['project_id']) && $task['rel']['project_id']['id'] == $project['id']) ? 'selected' : '' ?>>
                                                    <?= $project['name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Data de início</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-calendar-check"></i></span>
                                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?= date('Y-m-d\TH:i', strtotime($task['start_date'])) ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Data limite de conclusão</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
                                        <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-d\TH:i', strtotime($task['due_date'])) ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="finish_date" class="form-label">Data de conclusão</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                                        <input type="datetime-local" class="form-control" id="finish_date" name="finish_date" value="<?= date('Y-m-d\TH:i', strtotime($task['finish_date'])) ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="created_at" class="form-label">Data de criação</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-calendar-plus"></i></span>
                                        <input type="text" class="form-control" id="created_at" name="created_at" value="<?= htmlspecialchars($task['created_at']) ?>" readonly disabled>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="updated_at" class="form-label">Data de atualização</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-regular fa-calendar-plus"></i></span>
                                        <input type="text" class="form-control" id="updated_at" name="updated_at" value="<?= htmlspecialchars($task['updated_at']) ?>" readonly disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row mt-2 mb-2">
                            <label for="task_owner" class="form-label">Utilizador atribuído</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user-plus"></i></span>
                                <select class="form-control" name="task_owner" id="task_owner">
                                    <?php foreach($users as $user): ?>
                                        <option <?= ($task['rel']['task_owner']['iduser'] == $user['iduser']) ? 'selected' : '' ?> value="<?= $user['iduser'] ?>"><?= $user['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-info" onclick="location.href='/tasks'">
                            <i class="fa-solid fa-circle-left"></i> Voltar
                        </button>

                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-circle-check"></i> Guardar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include_once '../layout/widgets/comments.widget.php' ?>
</div>


<?php
include '../layout/footer.php';
include  '../error/flash-messages.php';
?>
</body>
</html>