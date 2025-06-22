<?php
require_once 'db_connection.php'; // Inclui config.php e a sessão

header('Content-Type: application/json');

// Proteção: Apenas usuários logados podem salvar senhas
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado. Faça o login.']);
    exit();
}

// Recebe dados do formulário (enviados como x-www-form-urlencoded pelo seu JS)
$nome = $_POST['nome'] ?? '';
$login = $_POST['login'] ?? '';
$senha = $_POST['senha'] ?? '';
$site = $_POST['site'] ?? '';
$usuario_id = $_SESSION['user_id'];

if (empty($nome) || empty($login) || empty($senha)) {
    echo json_encode(['status' => 'error', 'message' => 'Nome, login e senha são obrigatórios.']);
    exit();
}

// --- Criptografia da Senha ---
$cipher = "AES-256-CBC";
$ivlen = openssl_cipher_iv_length($cipher);
$iv = openssl_random_pseudo_bytes($ivlen); // Gera um IV aleatório e seguro

// Criptografa a senha
$senha_cifrada = openssl_encrypt($senha, $cipher, ENCRYPTION_KEY, 0, $iv);

// Converte IV e senha para base64 para armazenamento seguro no BD
$iv_base64 = base64_encode($iv);
$senha_cifrada_base64 = base64_encode($senha_cifrada);


// Prepara a inserção no banco de dados
$stmt = $conn->prepare("INSERT INTO credenciais (usuario_id, nome, login, senha_cifrada, iv, site) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $usuario_id, $nome, $login, $senha_cifrada_base64, $iv_base64, $site);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Credencial salva com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar credencial.']);
}

$stmt->close();
$conn->close();
?>