<?php
use App\Models\ProjectStatus\ProjectStatus;
require_once realpath(__DIR__ . '/../../app/bootstrap.php');

$status = new ProjectStatus();
$statuses = $status->get_status(0, true);
?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../../layout/head.php' ?>
<body>
<?php include_once '../../layout/nav.php' ?>

<div class="main-content">
    <div class="container my-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-list"></i> Estados de Projeto</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStatusModal">
                    <i class="fas fa-plus"></i> Novo estado
                </button>
            </div>
            <?php if (!empty($statuses)): ?>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Nome do estado</th>
                        <th>Descrição do estado</th>
                        <th>Usabilidade</th>
                        <th>Acções</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($statuses as $status): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($status['name']); ?></td>
                            <td><?php echo htmlspecialchars($status['description']); ?></td>
                            <td>
                                <?php
                                $status_state = $status['status'];
                                $badge = ($status_state) ? 'success' : 'danger';
                                ?>
                                <a href="/projects/statuses/flip.php?id=<?php echo $status['id']; ?>"><span class="badge bg-<?php echo $badge; ?>"><?php echo ($status_state) ? 'Active' : 'Inactive' ?></span></a>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning" disabled><i class="fas fa-pencil"></i></button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-status-id="<?php echo $status['id']; ?>">
                                    <i class="fas fa-dumpster-fire"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">
                    <h4 class="alert-title"><i class="fa fa-circle-exclamation"></i><b> Sem estados disponíveis</b></h4>
                    <p>Não existem estados de projeto a apresentar. Isto significa que <b>não irá ser possível criar um projeto novo</b>. Crie um estado agora.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Create Status Modal -->
<div class="modal fade" id="createStatusModal" tabindex="-1" aria-labelledby="createStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="create-status.php" method="POST" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="createProjectModalLabel">Novo estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="statusName" class="form-label">Nome do estado</label>
                        <input type="text" class="form-control" id="statusName" name="status_name" required>
                        <div class="invalid-feedback">Introduza um nome de estado.</div>
                    </div>
                    <div class="mb-3">
                        <label for="statusDescription" class="form-label">Descrição breve</label>
                        <input type="text" class="form-control" id="statusDescription" name="description" required></input>
                        <div class="invalid-feedback">Introduza uma descrição breve.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Guardar estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteStatusForm" method="POST" action="delete-status.php">
            <input type="hidden" name="status_id" id="deleteStatusId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar ação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem a certeza que pretende apagar este estado? Esta ação é irreversível. Tenha em mente que se houver projetos com este estado, não o será possível apagar.</p>
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
            var statusId = button.getAttribute('data-status-id');
            document.getElementById('deleteStatusId').value = statusId;
        });
    });
</script>

<?php
include '../../layout/footer.php';
include  '../../error/flash-messages.php';
?>

</body>
</html>