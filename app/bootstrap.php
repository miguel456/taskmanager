<?php
require_once realpath(__DIR__ . '/../vendor/autoload.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
