<?php
require_once 'config.php'; // Inicia a sessão

// Destrói todas as variáveis da sessão
$_SESSION = array();

// Se é desejável destruir a sessão completamente, apague também o cookie de sessão.
// Nota: Isso destruirá a sessão e não apenas os dados da sessão!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão
session_destroy();

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Logout realizado com sucesso.']);
?>