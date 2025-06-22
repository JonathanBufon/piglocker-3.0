<?php
require_once 'db_connection.php'; // Já inicia a sessão via config.php

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'E-mail e senha são obrigatórios.']);
    exit();
}

$stmt = $conn->prepare("SELECT id, nome, email, senha_hash, tfa_enabled FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $password_hash_attempt = hash('sha256', $password);

    if (hash_equals($user['senha_hash'], $password_hash_attempt)) {
        // Senha correta. Regenera o ID da sessão imediatamente por segurança.
        session_regenerate_id(true);

        if ($user['tfa_enabled']) {
            // Se 2FA está ativo, prepara para o próximo passo.
            $_SESSION['pre_2fa_user_id'] = $user['id'];
            echo json_encode(['status' => '2fa_required']);
        } else {
            // Se 2FA não está ativo, finaliza o login.
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['tfa_enabled'] = false;
            echo json_encode(['status' => 'success', 'message' => 'Login realizado com sucesso!']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'E-mail ou senha inválidos.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'E-mail ou senha inválidos.']);
}

$stmt->close();
$conn->close();
?>