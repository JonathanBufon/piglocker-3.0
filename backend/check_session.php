<?php
require_once 'config.php';

header('Content-Type: application/json');

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    echo json_encode([
        'status' => 'success',
        'loggedin' => true,
        'email' => $_SESSION['email'] ?? '',
        'nome' => $_SESSION['nome'] ?? '',
        'tfa_enabled' => $_SESSION['tfa_enabled'] ?? false
    ]);
} else {
    echo json_encode(['status' => 'success', 'loggedin' => false]);
}
?>