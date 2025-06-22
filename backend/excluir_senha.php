<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$credencial_id = $input['id'] ?? null;
$usuario_id = $_SESSION['user_id'];

if ($credencial_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'ID do item não fornecido.']);
    exit();
}

// Prepara a exclusão, garantindo que o usuário só possa excluir suas próprias credenciais
$stmt = $conn->prepare("DELETE FROM credenciais WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $credencial_id, $usuario_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Item excluído com sucesso.']);
    } else {
        // Isso acontece se o item não existe ou não pertence ao usuário
        echo json_encode(['status' => 'error', 'message' => 'Não foi possível excluir o item ou você não tem permissão.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao executar a exclusão.']);
}

$stmt->close();
$conn->close();
?>