<?php
require_once '../vendor/autoload.php';
require_once 'db_connection.php';

use PragmaRX\Google2FA\Google2FA;

header('Content-Type: application/json');

if (!isset($_SESSION['pre_2fa_user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Processo de login inválido. Por favor, comece novamente.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$tfa_code = $input['tfa_code'] ?? '';
$user_id = $_SESSION['pre_2fa_user_id'];

$stmt = $conn->prepare("SELECT id, nome, email, tfa_secret FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || empty($user['tfa_secret'])) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar dados do 2FA para o usuário.']);
    exit();
}

$google2fa = new Google2FA();
$valid = $google2fa->verifyKey($user['tfa_secret'], $tfa_code);

if ($valid) {
    // Código 2FA válido. Apenas elevamos o status da sessão.
    unset($_SESSION['pre_2fa_user_id']);
    
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['nome'] = $user['nome'];
    $_SESSION['tfa_enabled'] = true;

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Código de verificação inválido.']);
}

$conn->close();
?>