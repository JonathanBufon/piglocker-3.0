<?php
require_once 'db_connection.php'; // Inclui config.php e inicia a sessão

header('Content-Type: application/json');

// 1. Verifica se o usuário está logado na sessão
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Sessão não encontrada.']);
    exit();
}

// 2. Pega a senha enviada pelo frontend
$input = json_decode(file_get_contents('php://input'), true);
$password_attempt = $input['password'] ?? '';

if (empty($password_attempt)) {
    echo json_encode(['status' => 'error', 'message' => 'Senha não fornecida.']);
    exit();
}

// 3. Busca o hash da senha do usuário logado no banco de dados
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT senha_hash FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // 4. Compara o hash da senha enviada com o hash do banco
    $password_hash_attempt = hash('sha256', $password_attempt);

    if (hash_equals($user['senha_hash'], $password_hash_attempt)) {
        // Sucesso! A senha está correta.
        echo json_encode(['status' => 'success']);
    } else {
        // Senha incorreta
        echo json_encode(['status' => 'error', 'message' => 'Senha mestra incorreta.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Usuário da sessão não encontrado.']);
}

$stmt->close();
$conn->close();
