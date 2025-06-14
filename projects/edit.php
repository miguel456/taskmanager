<?php

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
        </div>
    </div>

<?php
include_once '../layout/footer.php';
include  '../error/flash-messages.php';
?>

</body>
</html>