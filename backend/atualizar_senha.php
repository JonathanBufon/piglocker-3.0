<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? null;
$nome = $input['nome'] ?? '';
$login = $input['login'] ?? '';
$senha = $input['senha'] ?? ''; // Senha é opcional
$site = $input['site'] ?? '';
$usuario_id = $_SESSION['user_id'];

if (empty($id) || empty($nome) || empty($login)) {
    echo json_encode(['status' => 'error', 'message' => 'ID, Nome e Login são obrigatórios.']);
    exit();
}

// Se uma nova senha foi fornecida, criptografa-a
if (!empty($senha)) {
    $cipher = "AES-256-CBC";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $senha_cifrada = openssl_encrypt($senha, $cipher, ENCRYPTION_KEY, 0, $iv);
    
    $iv_base64 = base64_encode($iv);
    $senha_cifrada_base64 = base64_encode($senha_cifrada);

    // Query para atualizar tudo, incluindo a senha
    $stmt = $conn->prepare("UPDATE credenciais SET nome = ?, login = ?, site = ?, senha_cifrada = ?, iv = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("sssssii", $nome, $login, $site, $senha_cifrada_base64, $iv_base64, $id, $usuario_id);
} else {
    // Query para atualizar sem alterar a senha
    $stmt = $conn->prepare("UPDATE credenciais SET nome = ?, login = ?, site = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("sssii", $nome, $login, $site, $id, $usuario_id);
}

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Credencial atualizada com sucesso!']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'Nenhum dado foi alterado ou você não tem permissão.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar a credencial.']);
}

$stmt->close();
$conn->close();
?>