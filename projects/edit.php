<?php

use App\Models\Comment;
use App\Models\History;
use App\Models\Projects\Project;
use App\Models\ProjectStatus\ProjectStatus;
use App\Models\Users\User;

require_once realpath(__DIR__ . '/../app/bootstrap.php');

$pId = $_GET['pid'];

$projects = new Project();

$user = new User();
$users = $user->getAllUsers(true);

if (empty($pId) || !$projects->project_exists($pId)) {
    flash_message('Projeto desconhecido', 'Não é possível editar um projeto desconhecido.');
    response('/projects');
    die;
}

$commentable = "project";
$commentableId = $pId;
$showHistory = true;

try {
    $history = History::allForProject($pId);
} catch (Exception $e) {
    $showHistory = false;
    flash_message('Erro Interno', 'Não foi possível apresentar o histórico desta tarefa e por isso não será mostrado', 'error');
}

try {
    $comments = Comment::getProjectComments($pId);
} catch (Exception $e) {
    flash_message('Comentários indisponíveis', 'Não foi possível obter comentários para este projeto (' . $e->getMessage() . ').', 'error');
    $comments = [];
}


$project = $projects->get_project($pId);
$assigned_to = get_user_id($project['assigned_to']);

$statuses = new ProjectStatus()->get_status(0, true, true);


?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../layout/head.php' ?>
<body>
    <?php include_once '../layout/nav.php' ?>
    <div class="main-content">
        <div class="container my-4">
            <?php if (!empty($statuses)): ?>

                <div class="row justify-content-center align-items-center">
                    <div class="col-md-4 d-flex align-items-center justify-content-center mb-3 mb-md-0" style="min-height: 100%;">
                        <img src="/img/edit-post.svg" alt="Woman messing around on her phone" height="180" class="img-fluid" style="max-width: 80%; object-fit: contain;">
                    </div>
                    <div class="col-md-8">
                        <div class="card p-0 mb-4">
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <h2 class="card-title mb-2"><i class="fas fa-pen-to-square"></i> Editar projeto</h2>
                                    <p class="card-text text-muted mb-0">Alteração do projeto - algumas operações, como alteração do utilizador atribuído, podem não estar disponíveis nesta página.</p>
                                </div>
                                <form id="editProject" action="edit-project.php" method="POST" novalidate>
                                    <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pId); ?>">
                                    <div class="mb-3">
                                        <label for="projectName" class="form-label">Nome do projeto</label>
                                        <input type="text" class="form-control" id="projectName" name="project_name" value="<?php echo $project['name'] ?>" required>
                                        <div class="invalid-feedback">Introduza um nome de projeto.</div>
                                    </div>

                                    <div class="mb-3">
                                        <!-- não editável -->
                                        <label for="assignedToName" class="form-label">Atribuído a</label>
                                        <select name="assignedTo" class="form-control" id="assignedToName">
                                            <?php foreach($users as $user): ?>

                                            <option <?= ($project['assigned_to'] == $user['iduser']) ? 'selected' : '' ?> value="<?= $user['iduser'] ?>"><?= $user['nome'] ?></option>

                                            <?php endforeach; ?>
                                        </select>

                                    </div>

                                    <div class="mb-3">
                                        <label for="projectDescription" class="form-label">Descrição</label>
                                        <textarea class="form-control" id="projectDescription" name="description" rows="3" required><?php echo $project['description'] ?></textarea>
                                        <div class="invalid-feedback">Introduza uma descrição.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="startDate" class="form-label">Data prevista de início</label>
                                        <input type="datetime-local" class="form-control" id="startDate" name="start_date" value="<?php echo isset($project['start_date']) ? date('Y-m-d\TH:i', strtotime($project['start_date'])) : '' ?>" required>
                                        <div class="invalid-feedback">Selecione uma data e hora prevista.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="endDate" class="form-label">Data prevista de fim</label>
                                        <input type="datetime-local" class="form-control" id="endDate" name="end_date" value="<?php echo isset($project['end_date']) ? date('Y-m-d\TH:i', strtotime($project['end_date'])) : '' ?>" required>
                                        <div class="invalid-feedback">Selecione uma data e hora prevista.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Estado</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <?php foreach($statuses as $status): ?>
                                                <option value="<?php echo $status['id']; ?>" <?php echo ($status['id'] == $project['status_id']) ? 'selected' : '' ?>><?php echo $status['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Selecione um estado.</div>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer">
                                <a href="/projects"><button type="button" class="btn btn-secondary"><i class="fas fa-circle-left"></i> Voltar aos projetos</button></a>
                                <button onclick="document.getElementById('editProject').submit()" type="button" class="btn btn-info ml-4" ><i class="fas fa-floppy-disk"></i> Guardar alterações</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card border-warning mb-4">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><i class="fas fa-exclamation-triangle"></i> Aviso</h5>
                                <p class="card-text">Não é possível editar um projeto neste momento, pois não há estados de projeto disponíveis (ex. pelo menos um estado tem de existir e de estar ativado).</p>
                                <a href="/projects/statuses"><button type="button" class="btn btn-warning"><i class="fas fa-clipboard-check"></i> Gerir estados</button></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#commentsCollapse" aria-expanded="false" aria-controls="commentsCollapse">
                    <h5 class="mb-0">
                        <i class="fas fa-comments"></i> Comentários
                        <span class="float-end"><i class="fas fa-chevron-down"></i></span>
                    </h5>
                </div>
                <div id="commentsCollapse" class="collapse">
                    <div class="card-body">
                        <?php include_once '../layout/widgets/comments.widget.php' ?>
                    </div>
                </div>
            </div>

            <?php if ($showHistory): ?>
                <div class="card mb-4">
                    <div class="card-header" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#historyCollapse" aria-expanded="false" aria-controls="historyCollapse">
                        <h5 class="mb-0">
                            <i class="fas fa-clock-rotate-left"></i> Histórico
                            <span class="float-end"><i class="fas fa-chevron-down"></i></span>
                        </h5>
                    </div>
                    <div id="historyCollapse" class="collapse">
                        <div class="card-body">
                            <?php if (!empty($history)): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach (array_slice($history, 0, 8) as $item): ?>
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
                            <?php else: ?>
                                <div class="text-muted">Parece que este projeto ainda não tem histórico.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

<?php
include_once '../layout/footer.php';
include  '../error/flash-messages.php';
?>

</body>
</html>