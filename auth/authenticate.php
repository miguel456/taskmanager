<?php

require '../bd.php';
require 'common.php';

$pdo = Database::getConnection();

session_start();

$email = $_POST['email'];
$password = $_POST['password'];

$errors = [];
if(empty($email)) {
    $errors[] = 'E-mail address is required.';
}

if(empty($password)) {
    $errors[] = 'Password is required.';
}

if (!user_exists($email)) {
    $errors[] = 'Credenciais inválidas ou conta inativa.';
    response('/login.php', 'Unauthorized', $errors, 401);
    die;
}

$userStmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
$userStmt->execute([$email]);

$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if(password_verify($password, $user['password']) && $user['estado'] == 1) {
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $user['nome'];
    $_SESSION['email'] = $user['email'];

    header('Location: /dashboard/inicio.php?message=' . urlencode('Autenticado com sucesso! Bem-vindo ' . $user['nome'] . '.'));
}
else
{
    $errors[] = 'Credenciais inválidas ou conta inativa.';
    response('/login.php', 'Unauthorized', $errors, 401);
}

die('Falha no redirecionamento!');