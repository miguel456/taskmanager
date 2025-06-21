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
                                <button class="btn btn-warning" onclick="openEditProjectStatusModal(<?= $status['id']; ?>)"><i class="fas fa-pencil"></i></button>
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

<!-- Edit Project Status Modal -->
<div class="modal fade" id="editProjectStatusModal" tabindex="-1" aria-labelledby="editProjectStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editProjectStatusForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="editProjectStatusModalLabel">Edit Project Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status_id" id="editProjectStatusId">
                    <div class="mb-3">
                        <label for="editProjectStatusName" class="form-label">Status Name</label>
                        <input type="text" class="form-control" id="editProjectStatusName" name="status_name" required>
                        <div class="invalid-feedback">Please enter a status name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="editProjectStatusDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="editProjectStatusDescription" name="description" required>
                        <div class="invalid-feedback">Please enter a description.</div>
                    </div>
                    <div id="editProjectStatusError" class="alert alert-danger d-none"></div>
                    <div id="editProjectStatusSuccess" class="alert alert-success d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include '../../layout/footer.php';
include  '../../error/flash-messages.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var statusId = button.getAttribute('data-status-id');
            document.getElementById('deleteStatusId').value = statusId;
        });
    });

    // Show modal and load project status data
    function openEditProjectStatusModal(statusId) {
        $('#editProjectStatusError').addClass('d-none').text('');
        $('#editProjectStatusSuccess').addClass('d-none').text('');
        $.ajax({
            url: 'get-project-status.php',
            type: 'POST',
            data: { status_id: statusId },
            dataType: 'json',
            success: function(response) {
                if (response.type === 'success') {
                    const status = response.data;
                    $('#editProjectStatusId').val(status.id);
                    $('#editProjectStatusName').val(status.name);
                    $('#editProjectStatusDescription').val(status.description);
                    $('#editProjectStatusModal').modal('show');
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Failed to load project status details.');
            }
        });
    }

    $('#editProjectStatusForm').on('submit', function(e) {
        e.preventDefault();
        $('#editProjectStatusError').addClass('d-none').text('');
        $('#editProjectStatusSuccess').addClass('d-none').text('');
        $.ajax({
            url: 'edit-project-status.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.type === 'success') {
                    $('#editProjectStatusSuccess').removeClass('d-none').text(response.message);
                    setTimeout(function() {
                        const row = $('tr').filter(function() {
                            return $(this).find('button[onclick="openEditProjectStatusModal(' + $('#editProjectStatusId').val() + ')"]').length > 0;
                        });
                        if (row.length) {
                            row.find('td').eq(0).text($('#editProjectStatusName').val());
                            row.find('td').eq(1).text($('#editProjectStatusDescription').val());
                        }
                        $('#editProjectStatusModal').modal('hide');
                    }, 1000);
                } else {
                    $('#editProjectStatusError').removeClass('d-none').text(response.message);
                }
            },
            error: function(xhr) {
                let msg = 'Falha ao atualizar o estado de projeto.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#editProjectStatusError').removeClass('d-none').text(msg);
            }
        });
    });
</script>

</body>
</html>