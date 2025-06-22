<?php

use App\Models\Notification;

$notificationErr = false;

try {
    $myNotification = Notification::allForUser(current_id());
} catch (Exception $e) {
    $notificationErr = true;
    flash_message('Sem notificações', 'Não foi possível carregar notificações. Pedimos desculpa pelo incómodo.', 'error');
}

?>

<?php if (!$notificationErr): ?>

    <div class="dropdown me-2">
        <button class="btn btn-outline-light btn-sm position-relative" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount">
                <?= count($myNotification) ?>
                <span class="visually-hidden">notificações não lidas</span>
            </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notificationsDropdown" style="min-width: 300px;" id="notificationList">
            <?php foreach($myNotification as $notification): ?>
                <?php
                $content = json_decode($notification->getContent(), true);
                ?>
                <li>
                    <a class="dropdown-item d-flex align-items-center notification-item" href="#" data-id="<?= $notification->getId() ?>">
                        <i class="fas fa-tasks text-primary me-2"></i>
                        <?= $content['content'] ?>
                        <span class="badge bg-secondary ms-auto"><?= $content['ui-meta']['created_at'] ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-center" href="#" id="markAllRead">Marcar tudo como lido</a>
            </li>
        </ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const markAllBtn = document.getElementById('markAllRead');
            markAllBtn?.addEventListener('click', function (e) {
                e.preventDefault();

                // Collect all notification IDs
                const ids = Array.from(document.querySelectorAll('.notification-item'))
                    .map(item => parseInt(item.getAttribute('data-id')))
                    .filter(id => !isNaN(id));

                if (ids.length === 0) return;

                fetch('/notifications/mark-read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ ids })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.type === 'success') {
                            document.getElementById('notificationCount').textContent = '0';
                            document.getElementById('notificationList').innerHTML = '<li><div class="dropdown-item text-center">Sem notificações não lidas.</div></li>';
                        } else {
                            alert('Erro ao marcar notificações como lidas.');
                        }
                    })
                    .catch(() => {
                        alert('Erro de comunicação com o servidor.');
                    });
            });
        });
    </script>

<?php else: ?>

    <div class="dropdown me-2">
        <button class="btn btn-outline-light btn-sm position-relative" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                !
                <span class="visually-hidden">erro ao carregar notificações</span>
            </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notificationsDropdown" style="min-width: 300px;">
            <li>
                <div class="dropdown-item text-danger text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Não foi possível carregar notificações.
                </div>
            </li>
        </ul>
    </div>

<?php endif; ?>