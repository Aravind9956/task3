<?php
require_once __DIR__ . '/includes/auth.php';
$_SESSION = [];
session_destroy();
session_start();
flash('success', 'Signed out.');
header('Location: login.php');
exit;