<?php

use App\Core\Exceptions\CommentNotFoundException;
use App\Models\Comment;

require_once realpath(__DIR__ . '/../vendor/autoload.php');

$commentId = $_POST['comment_id'];
$commentable = $_POST['commentable'];
$commentableId = $_POST['commentable_id'];


$back = match ($commentable) {
    'task' => '/tasks/view-task.php?task=' . $commentableId,
    'project' => '/projects/edit-project.php?pid=' . $commentableId,
    default => '/dashboard',
};

if (empty($commentId) || empty($commentable) || !in_array($commentable, ['task', 'project']) || empty($commentableId)) {
    flash_message('Erro de validação', 'Metadados do comentário inválidos.', 'error');
    response($back);
}

try {

    $comment = Comment::findByIdOrFail($commentId, $commentable);

    if ($comment->getCommenterId() !== $_SESSION['id']) {
        flash_message('Erro de autorização', 'Não é possível apagar comentários de outras pessoas.', 'error');
        response($back);
        die;
    }

    if ($comment->delete()) {
        flash_message('Feito', 'Comentário apagado.');
        response($back);
        die;
    }

    flash_message('Erro Interno', 'Não foi possível apagar o comentário. Tenta novamente mais tarde.', 'error');
    response($back);
    die;

} catch (CommentNotFoundException|Exception $e) {
    flash_message('Erro Interno', 'Não foi possível apagar o comentário. Tenta novamente mais tarde (' . $e->getMessage() . ').', 'error');
    response($back);
    die;
}