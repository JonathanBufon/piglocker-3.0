<?php
require_once '../vendor/autoload.php';
require_once 'db_connection.php';

use PragmaRX\Google2FA\Google2FA;

header('Content-Type: application/json');

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit();
}

// 2. Verifica se um segredo de configuração 2FA existe na sessão
if (!isset($_SESSION['tfa_setup_secret'])) {
    echo json_encode(['status' => 'error', 'message' => 'Processo de configuração não iniciado. Por favor, tente novamente.']);
    exit();
}

// 3. Pega o código enviado pelo usuário e o segredo da sessão
$input = json_decode(file_get_contents('php://input'), true);
$user_code = $input['tfa_code'] ?? '';
$secretKey = $_SESSION['tfa_setup_secret'];
$user_id = $_SESSION['user_id'];

$google2fa = new Google2FA();

// 4. Verifica se o código fornecido pelo usuário é válido
$valid = $google2fa->verifyKey($secretKey, $user_code);

if ($valid) {
    // 5. Se o código for válido, salva o segredo no banco e ativa o 2FA
    $stmt = $conn->prepare("UPDATE usuarios SET tfa_secret = ?, tfa_enabled = 1 WHERE id = ?");
    $stmt->bind_param("si", $secretKey, $user_id);

    if ($stmt->execute()) {
        // Limpa o segredo temporário da sessão
        unset($_SESSION['tfa_setup_secret']);
        echo json_encode(['status' => 'success', 'message' => 'Autenticação de dois fatores ativada com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar a configuração no banco de dados.']);
    }
    $stmt->close();
} else {
    // Se o código for inválido
    echo json_encode(['status' => 'error', 'message' => 'Código de verificação inválido. Tente novamente.']);
}

$conn->close();
?>