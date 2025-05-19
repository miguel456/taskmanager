<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $_SESSION = [];

    session_unset();
    session_destroy();
    session_regenerate_id(true);
}

header('Location: /login.php');
die;