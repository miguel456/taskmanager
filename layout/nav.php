<?php
$current = $_SERVER['REQUEST_URI'];
$username = htmlspecialchars($_SESSION['username'] ?? 'Utilizador');

if (!is_logged_in()) {
    response('/error/access-denied.html', 'Unauthorized', [], 401);
    die;
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark custom-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="/dashboard/index.php"><i class="fas fa-fire"></i> TaskManager</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link<?php if (str_contains($current, '/dashboard/index.php')) echo ' active'; ?>" href="/dashboard/index.php"><i class="fa fa-home"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if (str_contains($current, '/auth/account/profile.php')) echo ' active'; ?>" href="/auth/account/profile.php"><i class="fas fa-user"></i> Profile</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?php if (str_contains($current, '/projects') && !str_contains($current, '/projects/statuses')) echo ' active'; ?>" href="#" id="projectsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-list-check"></i> Projetos
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="projectsDropdown">
                        <li>
                            <a class="dropdown-item<?php if ($current === '/projects' || $current === '/projects/') echo ' active'; ?>" href="/projects">
                                <i class="fas fa-tasks"></i> Gerir projetos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item<?php if (str_contains($current, '/projects/statuses')) echo ' active'; ?>" href="/projects/statuses">
                                <i class="fas fa-flag"></i> Gerir estados
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?php if (str_contains($current, '/tasks') && !str_contains($current, '/tasks/statuses')) echo ' active'; ?>" href="#" id="tasksDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-clipboard-check"></i> O meu trabalho
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="tasksDropdown">
                        <li>
                            <a class="dropdown-item<?php if ($current === '/tasks' || $current === '/tasks/') echo ' active'; ?>" href="/tasks">
                                <i class="fas fa-tasks"></i> Gerir tarefas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item<?php if (str_contains($current, '/tasks/statuses')) echo ' active'; ?>" href="/tasks/statuses">
                                <i class="fas fa-flag"></i> Gerir estados
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <?php include_once __DIR__ . '/widgets/notification-bell.widget.php' ?>
            <div class="d-flex align-items-center gap-2">
                <span class="btn btn-outline-light btn-sm user-btn mb-0" tabindex="-1" style="pointer-events: none;">
                    <i class="fas fa-user-circle"></i> <?php echo $username; ?>
                </span>
                <a href="/logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Terminar sessão</a>
            </div>
        </div>
    </div>
</nav>
<link rel="stylesheet" href="/assets/css/navbar-custom.css">