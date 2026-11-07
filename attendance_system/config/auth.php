<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user'])) {
        header("Location: ../login.php");
        exit;
    }
}

function require_role($role) {
    require_login();

    if ($_SESSION['user']['role'] !== $role) {
        // if role does not match → go to correct dashboard
        if ($_SESSION['user']['role'] === 'admin') header("Location: ../admin/");
        if ($_SESSION['user']['role'] === 'professor') header("Location: ../professor/");
        if ($_SESSION['user']['role'] === 'student') header("Location: ../student/");
        exit;
    }
}
?>