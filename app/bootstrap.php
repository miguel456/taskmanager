<?php

use App\Core\Middleware\AuthGuard;

require_once realpath(__DIR__ . '/../vendor/autoload.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
new AuthGuard()->run();
if (headers_sent()) {
    die;
}
