<?php
require_once realpath(__DIR__ . '/../app/bootstrap.php');
?>
<!DOCTYPE html>
<html lang="en">
<?php include_once '../layout/head.php' ?>
<body class="bg-light" style="min-height: 100vh; display: flex; flex-direction: column;">

<div class="container-fluid flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="row w-100" style="min-height: 80vh;">
        <div class="col-md-6 d-flex align-items-center justify-content-center">
            <div class="w-100" style="max-width: 400px;">
                <div class="text-center mb-4">
                    <img src="/img/logo.svg" alt="Logo" style="max-width: 120px;" class="mb-2">
                </div>
                <div class="card shadow rounded-4">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Recuperação de conta</h2>
                        <?php
                        $messages = pull_messages();
                        if (!empty($messages)):
                            foreach ($messages as $msg):
                                $alertType = match ($msg['type']) {
                                    'success' => 'success',
                                    'error'   => 'danger',
                                    'info'    => 'info',
                                    default   => 'secondary'
                                };
                                ?>
                                <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
                                    <?php if (!empty($msg['title'])): ?>
                                        <i class="fa-solid <?= ($alertType == 'danger') ? 'fa-triangle-exclamation' : 'fa-circle-info' ?>"></i>
                                        <strong> <?= htmlspecialchars($msg['title']) ?></strong><br>
                                    <?php endif; ?>
                                    <?= nl2br(htmlspecialchars($msg['body'])) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php
                            endforeach;
                        endif;
                        ?>
                        <form action="process-reset.php" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Endereço de e-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="alguem@sapo.pt" required>
                                </div>
                                <div class="invalid-feedback">Introduza um endereço de email válido.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                <i class="fa fa-envelope me-2"></i>Enviar link de recuperação
                            </button>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="/login.php">Voltar ao login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 d-none d-md-block p-0">
            <div style="background: url('/img/auth/auth-vertical-banner.jpg') center center/cover no-repeat; height: 100vh; width: 100%; filter: blur(6px)"></div>
        </div>
    </div>
</div>

<footer class="bg-white border-top py-2 mt-auto w-100">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <span class="text-muted small">Licenciado sob GNU AGPL v3.</span>
        <span class="ml-3 text-muted small ms-auto">&copy; <?= date('Y') ?> Miguel Nogueira</span>
    </div>
</footer>
<?php include_once '../layout/standalone-scripts.php'; ?>
</body>
</html>