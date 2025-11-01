<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'nome_completo' => $_SESSION['nome_completo'],
        'email' => $_SESSION['email']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}