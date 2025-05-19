<?php

require '../../bd.php';
require '../common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acesso direto não permitido.');
}

$email = $_POST['email'];
$errors = [];

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Forneça um email válido.';
    response('/auth/forgot-password.php', 'Bad Request', $errors, 400);
    die;
}





