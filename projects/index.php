<?php

use App\Models\Projects\Project;
use App\Models\ProjectStatus\ProjectStatus;

require_once realpath(__DIR__ . '/../app/bootstrap.php');


$projects = new Project()->get_project(0, true);
$status = new ProjectStatus();
$statuses = $status->get_status(0, true, true);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskManager | Gestão de Projetos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { margin-bottom: 20px; }
        .card { border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
<?php include_once '../layout/nav.php' ?>
<div class="container my-4">
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
                    <th>Acções</th>
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
                            $span_disabled = '<i class="fas fa-triangle-exclamation"></i> '

                            ?>
                            <span class="badge bg-<?php echo $badge; ?>"><?php echo ($status_data['status']) ? htmlspecialchars($status_data['name']) : $span_disabled . htmlspecialchars($status_data['name']); ?></span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-warning" disabled><i class="fas fa-pencil"></i></button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-project-id="<?php echo $project['id']; ?>">
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

<!-- Create Project Modal -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-labelledby="createProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php if (!empty($statuses)): ?>
                <form action="create-project.php" method="POST" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title" id="createProjectModalLabel">Novo projeto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="projectName" class="form-label">Nome do projeto</label>
                            <input type="text" class="form-control" id="projectName" name="project_name" required>
                            <div class="invalid-feedback">Introduza um nome de projeto.</div>
                        </div>
                        <div class="mb-3">
                            <label for="projectDescription" class="form-label">Descrição</label>
                            <textarea class="form-control" id="projectDescription" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback">Introduza uma descrição.</div>
                        </div>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Data prevista de início</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                            <div class="invalid-feedback">Selecione uma data prevista.</div>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">Data prevista de fim</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required>
                            <div class="invalid-feedback">Selecione uma data prevista.</div>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php foreach($statuses as $status): ?>
                                    <option value="<?php echo $status['id']; ?>"><?php echo $status['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione um estado.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Guardar projeto</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="createProjectModalLabel">Novo projeto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h3><i class="fas fa-triangle-exclamation"></i> Não é possível criar um projeto</h3>
                        <p>De momento, não é possível criar um projeto, pois não há nenhum estado disponível para lhe atribuír. Consulte a <a href="/projects/statuses">área de estados</a> para corrigir a situação.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" disabled><i class="fa fa-check"></i> Guardar projeto</button>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var projectId = button.getAttribute('data-project-id');
            document.getElementById('deleteProjectId').value = projectId;
        });
    });
</script>

<?php include  '../error/flash-messages.php'; ?>

</body>
</html>