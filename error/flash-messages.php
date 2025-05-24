<?php

require realpath(__DIR__ . "/../auth/common.php");
?>

<?php if (bag_has_message()) : ?>

<script>
    <?php
    $bag_contents = pull_messages();
    $errors = [];
    $successes = [];

    foreach ($bag_contents as $message) {
        if ($message['type'] === 'success') {
            $successes[] = $message;
        } else {
            $errors[] = $message;
        }
    }

    if (!empty($errors)) :
    $errorText = '';
    foreach ($errors as $error) {
        $errorText .= '<b>' . htmlspecialchars($error['title']) . '</b><br>' . nl2br(htmlspecialchars($error['body'])) . '<br><br>';
    }
    ?>
    Swal.fire({
        title: "Um ou mais itens da sua submissão contém erros.",
        html: "<?php echo addslashes($errorText); ?>",
        icon: "error"
    });
    <?php endif; ?>

    <?php foreach ($successes as $success) : ?>
    Swal.fire({
        title: "<?php echo htmlspecialchars($success['title']); ?>",
        text: "<?php echo htmlspecialchars($success['body']); ?>",
        icon: "success"
    });
    <?php endforeach; ?>
</script>

<?php else: ?>

    <!-- nothing to report -->

<?php endif; ?>
