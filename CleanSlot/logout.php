<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

redirect(url('index.php'));
