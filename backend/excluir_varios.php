<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

// 1. Validação de Sessão
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit();
}

// 2. Recebe e valida os IDs
$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (empty($ids) || !is_array($ids)) {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum item selecionado para exclusão.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. Prepara a query SQL para exclusão segura com a cláusula IN
// Cria uma string de placeholders (?,?,?) para os IDs
$placeholders = implode(',', array_fill(0, count($ids), '?'));
// Define os tipos de parâmetros: 'i' para o user_id e 'i' para cada ID
$types = 'i' . str_repeat('i', count($ids));
// Junta o user_id e os IDs a serem deletados em um único array
$params = array_merge([$user_id], $ids);

// 4. Monta e executa a query
// Garante que o usuário só possa deletar suas próprias credenciais
$stmt = $conn->prepare("DELETE FROM credenciais WHERE usuario_id = ? AND id IN ($placeholders)");
// Usa o operador 'splat' (...) para passar os parâmetros
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $affected_rows = $stmt->affected_rows;
    echo json_encode(['status' => 'success', 'message' => "$affected_rows item(ns) excluído(s) com sucesso!"]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir os itens.']);
}

$stmt->close();
$conn->close();
?>