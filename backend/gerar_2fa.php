<?php
require_once '../vendor/autoload.php';
require_once 'config.php';

// Novas classes que estamos usando
use PragmaRX\Google2FA\Google2FA;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

header('Content-Type: application/json');

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado.']);
    exit();
}

try {
    $google2fa = new Google2FA();
    $secretKey = $google2fa->generateSecretKey();

    // 2. Armazena o segredo temporariamente na sessão
    $_SESSION['tfa_setup_secret'] = $secretKey;

    $userEmail = $_SESSION['email'];
    $appName = 'Pig-Locker';

    // 3. Gera o link especial do QR Code (otpauth://)
    $otpauthUrl = $google2fa->getQRCodeUrl($appName, $userEmail, $secretKey);

    // 4. **A MUDANÇA:** Usa a nova biblioteca para criar a imagem do QR Code
    $qrCode = QrCode::create($otpauthUrl)
        ->setSize(200)
        ->setMargin(10);

    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    // Converte a imagem para um Data URI, que pode ser usado diretamente na tag <img>
    $qrCodeDataUri = $result->getDataUri();

    // 5. Retorna o segredo e a imagem do QR Code para o frontend
    echo json_encode([
        'status' => 'success',
        'secret' => $secretKey,
        'qrCodeDataUri' => $qrCodeDataUri // Nova chave de resposta
    ]);

} catch (Exception $e) {
    // Captura qualquer erro que possa ocorrer durante a geração
    echo json_encode(['status' => 'error', 'message' => 'Erro ao gerar o QR Code no servidor.']);
}
?>