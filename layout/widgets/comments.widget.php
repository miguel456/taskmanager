<div class="row mt-5 mb-5">
    <?php foreach($comments as $comment): ?>
        <div class="row mb-4">
            <div class="col-1 d-flex flex-column align-items-center">
                <img src="/img/avatars/avatar-image-04.png" alt="User" class="rounded-circle border border-2 border-primary shadow" width="48" height="48">
                <span class="badge bg-secondary mt-2"><?= htmlspecialchars($comment->commenter['nome']) ?></span>
            </div>
            <div class="col-11">
                <div class="bg-white p-3 rounded shadow-sm border border-light position-relative comment-box">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-primary"><?= htmlspecialchars($comment->commenter['nome']) ?></strong>
                        <span class="text-muted small">
                                <i class="fa-regular fa-clock"></i>
                                <?= $comment->getCreatedAt()->format('d/m/Y H:i') ?>
                            </span>
                    </div>
                    <p class="mb-2"><?= nl2br(htmlspecialchars($comment->getContent())) ?></p>
                    <?php if($comment->commenter['iduser'] == $_SESSION['id']): ?>
                        <div class="mt-2 text-end">
                            <form action="/comments/delete-comment.php" method="post" class="d-inline">
                                <input type="hidden" name="comment_id" value="<?= $comment->getId() ?>">
                                <input type="hidden" name="commentable" value="<?= $commentable ?>">
                                <input type="hidden" name="commentable_id" value="<?= $commentableId ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete comment">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="row">
        <div class="col-auto">
            <img src="/img/avatars/avatar-image-06.png" alt="Your Profile" class="rounded-circle" width="48" height="48">
        </div>
        <div class="col">
            <form action="/comments/add-comment.php" method="post">
                <input type="hidden" name="commentableId" value="<?= $commentableId ?>">
                <input type="hidden" name="commentable" value="task">
                <div class="mb-2">
                    <textarea class="form-control" name="comment_content" rows="3" placeholder="Escrever um comentário..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Enviar (Postar como <?php echo $_SESSION['username'] ?>)</button>
            </form>
        </div>
    </div>
</div>