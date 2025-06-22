<?php
// Configurações do Banco de Dados
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'testuser');
define('DB_PASSWORD', 'testpass123');
define('DB_NAME', 'piglocker');

// Chave de Criptografia - IMPORTANTE!
// Esta chave será usada para criptografar/descriptografar as senhas no cofre.
// Deve ser uma string aleatória e segura de 32 bytes.
// NUNCA mude esta chave após ter salvo senhas no banco.
define('ENCRYPTION_KEY', 'sua-chave-secreta-de-32-bytes-aqui'); // Ex: use random_bytes(32) para gerar uma

// --- LÓGICA DE SESSÃO MELHORADA ---
if (session_status() == PHP_SESSION_NONE) {
    // Define um nome para o cookie da sessão para evitar conflitos
    session_name('PigLockerSession');

    // Configurações de segurança para o cookie da sessão
    session_set_cookie_params([
        'lifetime' => 0, // O cookie dura até o navegador fechar
        'path' => '/',
        'domain' => '', // Deixe em branco para localhost
        'secure' => isset($_SERVER['HTTPS']), // True se estiver em HTTPS
        'httponly' => true, // O cookie não pode ser acessado por JavaScript
        'samesite' => 'Lax' // Proteção contra ataques CSRF
    ]);

    session_start();
}
?>