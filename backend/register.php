<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

// Simulação de dados de formulário - Troque estes valores para registrar
$email = "piglocker@gmail.com";
$senha_mestra = "senha123";

// --- Lógica de Registro ---

// 1. Validação simples
if (empty($email) || empty($senha_mestra)) {
    echo json_encode(['status' => 'error', 'message' => 'E-mail e senha são obrigatórios.']);
    exit();
}

// 2. Verifica se o e-mail já existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Este e-mail já está cadastrado.']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// 3. Hash da senha com SHA-256
$senha_hash = hash('sha256', $senha_mestra);

// 4. Insere o novo usuário no banco de dados
$stmt = $conn->prepare("INSERT INTO usuarios (email, senha_hash) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $senha_hash);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Usuário registrado com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar usuário.']);
}

$stmt->close();
$conn->close();
?>