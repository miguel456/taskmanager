<?php

require 'auth/common.php';

session_start();

if (is_logged_in()) {
    $_SESSION = [];

    session_unset();
    session_destroy();
    session_regenerate_id(true);
}

response('/login.php');
die;