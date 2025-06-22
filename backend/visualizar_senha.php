<?php
require_once 'db_connection.php'; // Inclui config.php e a sessão

header('Content-Type: application/json');

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado. Faça o login.']);
    exit();
}

// 2. Pega o ID da credencial enviado pelo frontend
$input = json_decode(file_get_contents('php://input'), true);
$credencial_id = $input['id'] ?? null;

if ($credencial_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'ID da credencial não fornecido.']);
    exit();
}

$usuario_id = $_SESSION['user_id'];

// 3. Busca a senha criptografada e o IV no banco
// IMPORTANTE: A query verifica se a credencial pertence ao usuário logado (usuario_id)
$stmt = $conn->prepare("SELECT senha_cifrada, iv FROM credenciais WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $credencial_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // 4. Decodifica os dados do banco (eles estão em base64)
    $iv = base64_decode($row['iv']);
    $senha_cifrada = base64_decode($row['senha_cifrada']);
    
    // 5. Descriptografa a senha usando a chave do config.php
    $cipher = "AES-256-CBC";
    $senha_original = openssl_decrypt($senha_cifrada, $cipher, ENCRYPTION_KEY, 0, $iv);

    if ($senha_original !== false) {
        // Sucesso! Envia a senha original de volta
        echo json_encode(['status' => 'success', 'password' => $senha_original]);
    } else {
        // Erro na descriptografia
        echo json_encode(['status' => 'error', 'message' => 'Não foi possível descriptografar a senha.']);
    }
} else {
    // Credencial não encontrada ou não pertence ao usuário
    echo json_encode(['status' => 'error', 'message' => 'Credencial não encontrada ou acesso negado.']);
}

$stmt->close();
$conn->close();
