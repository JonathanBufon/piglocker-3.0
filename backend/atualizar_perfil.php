<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$nome = trim($input['nome'] ?? '');
$user_id = $_SESSION['user_id'];

if (empty($nome)) {
    echo json_encode(['status' => 'error', 'message' => 'O nome de usuário não pode estar vazio.']);
    exit();
}

$stmt = $conn->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
$stmt->bind_param("si", $nome, $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Nome de usuário atualizado com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar o nome de usuário.']);
}

$stmt->close();
$conn->close();