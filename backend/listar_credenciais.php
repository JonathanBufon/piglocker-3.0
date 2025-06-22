<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit();
}

$usuario_id = $_SESSION['user_id'];
$credenciais = [];

$sql = "SELECT id, nome, login, site FROM credenciais WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $credenciais[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $credenciais]);

$stmt->close();
$conn->close();
?>