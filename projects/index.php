<?php

use App\Models\Projects\Project;
use App\Models\ProjectStatus\ProjectStatus;
use App\Models\Users\User;

require_once realpath(__DIR__ . '/../app/bootstrap.php');


$projectsInstance = new Project();
$projects = $projectsInstance->get_project(0, true);


$status = new ProjectStatus();
$statuses = $status->get_status(0, true, true);

$users = new User()->getAllUsers(true);


?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../layout/head.php' ?>
<body>
<?php include_once '../layout/nav.php' ?>
<div class="main-content">
    <div class="container my-4">
        <?php include_once '../layout/project-status-bar.php' ?>
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-list"></i> Projetos atuais</h5>
                <?php

                ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                    <i class="fas fa-plus"></i> Novo projeto
                </button>
            </div>
            <?php if (!empty($projects)): ?>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Data de início</th>
                        <th>Data de fim prevista</th>
                        <th>Estado</th>
                        <th>Responsável</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['name']); ?></td>
                            <td><?php echo htmlspecialchars($project['description']); ?></td>
                            <td><?php echo htmlspecialchars($project['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                $status_data = $status->get_status($project['status_id']);
                                $span_disabled = '<i class="fas fa-triangle-exclamation"></i> ';
                                ?>
                                <span class="badge bg-<?php echo $badge; ?>"><?php echo ($status_data['status']) ? htmlspecialchars($status_data['name']) : $span_disabled . htmlspecialchars($status_data['name']); ?></span>
                            </td>
                            <td>
                                <?php
                                $avatar = !empty($project['rel']['assigned_to']['avatar_url']) ? $project['rel']['assigned_to']['avatar_url'] : '/assets/default-avatar.png';
                                $assignee = !empty($project['rel']['assigned_to']['nome']) ? $project['rel']['assigned_to']['nome'] : 'N/D';
                                ?>
                                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="rounded-circle" width="32" height="32" title="<?php echo htmlspecialchars($assignee); ?>">
                                <span class="ms-2"><?php echo htmlspecialchars($assignee); ?></span>
                            </td>
                            <td>
                                <a class="project-activation" href="/projects/select.php?pid=<?= $project['id'] ?>"><button id="activateProjectId<?= $project['id'] ?>" type="button" class="btn btn-secondary"><i class="fa-solid fa-gears"></i></button></a>
                                <a class="project-edit" href="<?php echo '/projects/edit.php?pid=' . $project['id'] ?>"><button type="button" class="btn btn-warning"><i class="fas fa-pencil"></i></button></a>
                                <button type="button" class="btn btn-danger project-delete" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-project-id="<?php echo $project['id']; ?>">
                                    <i class="fas fa-dumpster-fire"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">
                    <h4 class="alert-title"><i class="fa fa-circle-exclamation"></i><b> Sem projetos disponíveis</b></h4>
                    <p>De momento, ou não existem projetos a apresentar, ou estes não estão disponíveis para si. Experimente criar um; clique no botão azul acima para começar.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Create Project Modal -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-labelledby="createProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0">
            <?php if (!empty($statuses)): ?>
                <form action="create-project.php" method="POST" novalidate>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="createProjectModalLabel"><i class="fas fa-folder-plus me-2"></i>Novo projeto</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                    <input type="text" class="form-control" id="projectName" name="project_name" placeholder="Nome do projeto" required>
                                    <div class="invalid-feedback">Introduza um nome de projeto.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <select class="form-select" id="assignedTo" name="assigned_to" required>
                                        <option value="" disabled selected>Responsável</option>
                                        <?php foreach($users as $user): ?>
                                            <option value="<?php echo $user['iduser']; ?>"><?php echo htmlspecialchars($user['nome']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Selecione um responsável.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                    <textarea class="form-control" id="projectDescription" name="description" rows="2" placeholder="Descrição" required></textarea>
                                    <div class="invalid-feedback">Introduza uma descrição.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-calendar-plus"></i></span>
                                    <input type="datetime-local" class="form-control" id="startDate" name="start_date" required>
                                    <div class="invalid-feedback">Selecione uma data e hora prevista de início.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                    <input type="datetime-local" class="form-control" id="endDate" name="end_date" required>
                                    <div class="invalid-feedback">Selecione uma data e hora prevista de fim.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="" disabled selected>Estado</option>
                                        <?php foreach($statuses as $status): ?>
                                            <option value="<?php echo $status['id']; ?>"><?php echo htmlspecialchars($status['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Selecione um estado.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Guardar projeto</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="createProjectModalLabel"><i class="fas fa-folder-plus me-2"></i>Novo projeto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h3><i class="fas fa-triangle-exclamation"></i> Não é possível criar um projeto</h3>
                        <p>De momento, não é possível criar um projeto, pois não há nenhum estado disponível para lhe atribuír. Consulte a <a href="/projects/statuses">área de estados</a> para corrigir a situação.</p>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" disabled><i class="fa fa-check"></i> Guardar projeto</button>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>



<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteProjectForm" method="POST" action="delete-project.php">
            <input type="hidden" name="project_id" id="deleteProjectId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar ação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem a certeza que pretende apagar este projeto? Esta ação é irreversível. Tenha em mente que projetos com tarefas associadas não podem ser apagados.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-can"></i> Confirmar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-ban"></i> Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
include_once '../layout/footer.php';
include_once  '../error/flash-messages.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var projectId = button.getAttribute('data-project-id');
            document.getElementById('deleteProjectId').value = projectId;
        });
    });

    tippy(".project-activation", {
        content: 'Ativar/desativar projeto'
    });

    tippy(".project-delete", {
        content: 'Apagar projeto'
    });

    tippy(".project-edit", {
        content: 'Editar projeto'
    });
</script>


</body>
</html>