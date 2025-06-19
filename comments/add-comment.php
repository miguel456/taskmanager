<?php

use App\Models\Comment;

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$commentContent = $_POST['comment_content'];

$commentable = $_POST['commentable'];
$commentableId = $_POST['commentableId'];


$back = match ($commentable) {
    'task' => '/tasks/view-task.php?task=' . $commentableId,
    'project' => '/projects/edit-project.php?pid=' . $commentableId,
    default => '/dashboard',
};

if(empty($commentable) || empty($commentableId) || !in_array($commentable, ['task', 'project'])) {
    flash_message('Erro de validação', 'Metadados do comentário inválidos, recarrega a página e tenta novamente.', 'error');
    response($back);
    die;
}

if (empty($commentContent)) {
    flash_message('Erro de validação', '...comentários vazios? Escreve alguma coisa!', 'error');
    response($back);
    die;
}



try {
    $comment = new Comment($_SESSION['id'], true, htmlspecialchars($commentContent), $commentable, $commentableId)->save();

    if ($comment) {
        flash_message('Feito', 'O teu comentário foi publicado.');
    }
    else {
        flash_message('Erro Interno', 'Por motivos desconhecidos, não foi possível publicar o teu comentário.', 'error');
    }
    response($back);
    die;

} catch (Exception $e) {
    flash_message('Erro Interno', 'Por motivos desconhecidos, não foi possível publicar o teu comentário (' . $e->getMessage() . ').', 'error');
    response($back);
    die;
}