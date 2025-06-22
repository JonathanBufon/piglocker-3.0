<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

// 1. Validação de Sessão
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit();
}

// 2. Recebe e valida a senha mestra
$input = json_decode(file_get_contents('php://input'), true);
$master_password = $input['master_password'] ?? '';

if (empty($master_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Senha mestra é obrigatória.']);
    exit();
}

// 3. Verifica se a senha mestra está correta
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT senha_hash FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !hash_equals($user['senha_hash'], hash('sha256', $master_password))) {
    echo json_encode(['status' => 'error', 'message' => 'A senha mestra está incorreta.']);
    exit();
}

// 4. Senha correta, remove o 2FA do banco
$stmt = $conn->prepare("UPDATE usuarios SET tfa_enabled = 0, tfa_secret = NULL WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    // Atualiza a sessão
    $_SESSION['tfa_enabled'] = false;
    echo json_encode(['status' => 'success', 'message' => 'Autenticação de dois fatores removida com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao remover o 2FA do banco de dados.']);
}

$stmt->close();
$conn->close();
?>