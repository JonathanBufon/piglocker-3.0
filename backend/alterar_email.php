<?php
require_once 'db_connection.php'; // Inclui config.php e inicia a sessão

header('Content-Type: application/json');

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit();
}

// 2. Recebe os dados do frontend
$input = json_decode(file_get_contents('php://input'), true);
$master_password = $input['master_password'] ?? '';
$new_email = $input['new_email'] ?? '';
$user_id = $_SESSION['user_id'];

// 3. Validação dos dados recebidos
if (empty($master_password) || empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, preencha todos os campos corretamente.']);
    exit();
}

// 4. Verifica se a senha mestra está correta
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

// 5. Verifica se o novo e-mail já está em uso por outro usuário
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
$stmt->bind_param("si", $new_email, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Este novo e-mail já está em uso.']);
    $stmt->close();
    exit();
}
$stmt->close();

// 6. Atualiza o e-mail no banco de dados
$stmt = $conn->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
$stmt->bind_param("si", $new_email, $user_id);

if ($stmt->execute()) {
    // Força o logout por segurança após alterar um dado crítico
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => 'E-mail alterado com sucesso! Você será desconectado por segurança.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ocorreu um erro ao tentar alterar o e-mail.']);
}

$stmt->close();
$conn->close();