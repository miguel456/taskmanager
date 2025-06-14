<?php

$activeProject = new \App\Models\Projects\Project()->getActiveProject();
$hasSelectedProject = !empty($activeProject);


?>
<div class="card mb-4 border-primary shadow-sm" style="background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);">
    <div class="card-body">
        <h3 class="card-title text-primary mb-1">
            <i class="fas fa-folder-open me-2"></i>
            <?php echo htmlspecialchars(($hasSelectedProject) ? $activeProject['title'] : 'Não tem um projeto selecionado.'); ?>
        </h3>
        <p class="card-text text-secondary fs-5 mb-0">
            <?php echo htmlspecialchars(($hasSelectedProject) ? $activeProject['description'] : 'Sem um projeto selecionado, as novas tarefas ficarão órfãs.'); ?>
        </p>
        <?php if ($hasSelectedProject): ?>

            <span class="badge bg-primary mt-2">Projeto atualmente selecionado</span>
            <span class="badge bg-info mt-2">Estado: <?php echo htmlspecialchars($activeProject['status_name'] ?? 'Em progresso'); ?></span>

            <span class="badge mt-2 <?php echo (isset($activeProject['deadline']) && strtotime($activeProject['deadline']) < strtotime(date('Y-m-d'))) ? 'bg-danger' : 'bg-success'; ?>">
                <?php if (isset($activeProject['deadline']) && strtotime($activeProject['deadline']) < strtotime(date('Y-m-d'))): ?>
                    <i class="fas fa-exclamation-triangle me-1"></i>
                <?php else: ?>
                    <i class="fas fa-check-circle me-1"></i>
                <?php endif; ?>
                Prazo: <?php echo htmlspecialchars($activeProject['deadline'] ?? '2024-12-31'); ?>
            </span>

        <?php endif; ?>
    </div>
</div>