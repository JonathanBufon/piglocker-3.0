<?php
require_once 'config.php';

// Cria a conexão
$conn = new \mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica a conexão
if ($conn->connect_error) {
    // Em um ambiente de produção, não exiba o erro detalhado.
    // Logue o erro e mostre uma mensagem genérica.
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno do servidor (DB Connection).'
    ]);
    exit();
}

// Define o charset para UTF-8 para evitar problemas com caracteres especiais
$conn->set_charset("utf8mb4");
?>