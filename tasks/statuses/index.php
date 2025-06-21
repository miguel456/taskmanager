<?php
use App\Models\TaskStatus\TaskStatus;
require_once realpath(__DIR__ . '/../../app/bootstrap.php');

$status = new TaskStatus();
$statuses = $status->read(true, true, false);

$hasFinishingStatus = $status->finalExists();

?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../../layout/head.php' ?>
<body>
<?php include_once '../../layout/nav.php' ?>

<div class="main-content">
    <div class="container my-4">
        <?php if (!$hasFinishingStatus && !empty($statuses)): ?>
            <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm no-finishing-status" role="alert" style="border-left: 5px solid #ffc107;">
                <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                <div>
                    <h5 class="mb-1 fw-bold">Atenção: Nenhum estado final configurado</h5>
                    <p class="mb-0">Atualmente, não existe nenhum estado de tarefa que permita marcar tarefas como concluídas. Para poder finalizar tarefas, crie ou edite um estado e ative a opção <b>“Pode concluir tarefas?”</b>.</p>
                </div>
            </div>
        <?php endif; ?>
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-list"></i> Estados de Tarefa</h5>
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
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($statuses as $status): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($status['name']); ?>
                                <?php if (!empty($status['final']) && $status['final'] == 1): ?>
                                    <span class="badge bg-danger ms-2">Final</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($status['description']); ?></td>
                            <td>
                                <?php
                                $status_state = $status['status'];
                                $badge = ($status_state) ? 'success' : 'danger';
                                ?>
                                <a href="/tasks/statuses/flip.php?id=<?= $status['id']; ?>"><span class="badge bg-<?php echo $badge; ?>"><?php echo ($status_state) ? 'Ativo' : 'Inativo' ?></span></a>
                            </td>
                            <td>
                                <button class="btn btn-warning" onclick="openEditStatusModal(<?= $status['id']; ?>)"><i class="fas fa-pencil"></i></button>
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
                    <p>Não existem estados de tarefa a apresentar. Isto significa que <b>não irá ser possível criar uma tarefa nova</b>. Crie um estado agora.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editStatusForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="editStatusModalLabel">Editar estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status_id" id="editStatusId">
                    <div class="mb-3">
                        <label for="editStatusName" class="form-label">Nome do estado</label>
                        <input type="text" class="form-control" id="editStatusName" name="status_name" required>
                        <div class="invalid-feedback">Introduza o nome do estado.</div>
                    </div>
                    <div class="mb-3">
                        <label for="editStatusDescription" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="editStatusDescription" name="description" required>
                        <div class="invalid-feedback">Por favor introduza uma descrição válida.</div>
                    </div>
                    <div class="form-check mb-3">
                        <input type="hidden" name="final" value="0">
                        <input class="form-check-input" type="checkbox" value="1" id="editStatusFinal" name="final">
                        <label class="form-check-label" for="editStatusFinal">
                            Pode concluir tarefas?
                        </label>
                    </div>
                    <div id="editStatusError" class="alert alert-danger d-none"></div>
                    <div id="editStatusSuccess" class="alert alert-success d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Guardar alterações</button>
                </div>
            </form>
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
                    <div class="form-check mb-3">
                        <input type="hidden" name="final" value="0">
                        <input class="form-check-input" type="checkbox" value="1" id="statusFinal" name="final">
                        <label class="form-check-label" for="statusFinal">
                            Pode concluir tarefas?
                        </label>
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
                    <p>Tem a certeza que pretende apagar este estado? Esta ação é irreversível. Tenha em mente que se houver tarefas com este estado, não o será possível apagar.</p>
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

    function openEditStatusModal(statusId) {
        $('#editStatusError').addClass('d-none').text('');
        $('#editStatusSuccess').addClass('d-none').text('');
        $.ajax({
            url: 'get-status.php',
            type: 'POST',
            data: { status_id: statusId },
            dataType: 'json',
            success: function(response) {
                if (response.type === 'success') {
                    const status = response.data;
                    $('#editStatusId').val(status.id);
                    $('#editStatusName').val(status.name);
                    $('#editStatusDescription').val(status.description);
                    $('#editStatusFinal').prop('checked', status.final == 1);
                    $('#editStatusModal').modal('show');
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Failed to load status details.');
            }
        });
    }

    $('#editStatusForm').on('submit', function(e) {
        e.preventDefault();
        $('#editStatusError').addClass('d-none').text('');
        $('#editStatusSuccess').addClass('d-none').text('');
        $.ajax({
            url: 'edit-status.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.type === 'success') {
                    $('#editStatusSuccess').removeClass('d-none').text(response.message);
                    setTimeout(function() {
                        const row = $('tr').filter(function() {
                            return $(this).find('button[onclick="openEditStatusModal(' + $('#editStatusId').val() + ')"]').length > 0;
                        });
                        if (row.length) {
                            // Update name
                            row.find('td').eq(0).html(
                                $('<div>').append(
                                    $('<span>').text($('#editStatusName').val()),
                                    $('#editStatusFinal').is(':checked') ? ' <span class="badge bg-danger ms-2">Final</span>' : ''
                                ).html()
                            );
                            row.find('td').eq(1).text($('#editStatusDescription').val());
                        }
                        $('#editStatusModal').modal('hide');
                    }, 1000);
                } else {
                    $('#editStatusError').removeClass('d-none').text(response.message);
                }
            },
            error: function(xhr) {
                let msg = 'Falha ao atualizar o estado.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#editStatusError').removeClass('d-none').text(msg);
            }
        });
    });

</script>

</body>
</html>