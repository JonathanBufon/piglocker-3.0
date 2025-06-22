<?php
// Informações do seu servidor MariaDB
$servername = "localhost"; // ou o IP do seu servidor de BD
$username = "piglocker_user";      // seu usuário do BD
$password = "Cosmos1964@#@"; // sua senha do BD

try {
    // Conexão inicial sem selecionar um banco de dados
    $conn = new mysqli($servername, $username, $password);

    // Verifica a conexão
    if ($conn->connect_error) {
        die("Falha na conexão com o servidor: " . $conn->connect_error);
    }

    // 1. Criação do Banco de Dados
    $sql_db = "CREATE DATABASE IF NOT EXISTS pig_locker_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql_db) === TRUE) {
        echo "Banco de dados 'pig_locker_db' criado ou já existente.<br>";
    } else {
        die("Erro ao criar banco de dados: " . $conn->error . "<br>");
    }

    // Seleciona o banco de dados para as próximas operações
    $conn->select_db('pig_locker_db');

    // 2. Criação da Tabela 'usuarios'
    $sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha_hash VARCHAR(64) NOT NULL, -- SHA-256 gera um hash de 64 caracteres hexadecimais
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql_usuarios) === TRUE) {
        echo "Tabela 'usuarios' criada ou já existente.<br>";
    } else {
        die("Erro ao criar tabela 'usuarios': " . $conn->error . "<br>");
    }

    // 3. Criação da Tabela 'credenciais'
    // Esta tabela guarda as senhas salvas, criptografadas individualmente.
    $sql_credenciais = "CREATE TABLE IF NOT EXISTS credenciais (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT(11) UNSIGNED NOT NULL,
        nome VARCHAR(255) NOT NULL,
        login VARCHAR(255) NOT NULL,
        senha_cifrada TEXT NOT NULL, -- A senha criptografada
        iv VARCHAR(32) NOT NULL,      -- O vetor de inicialização para a criptografia
        site VARCHAR(255),
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql_credenciais) === TRUE) {
        echo "Tabela 'credenciais' criada ou já existente.<br>";
    } else {
        die("Erro ao criar tabela 'credenciais': " . $conn->error . "<br>");
    }

    echo "Configuração do banco de dados concluída com sucesso!";
    $conn->close();

} catch (Exception $e) {
    die("Ocorreu um erro: " . $e->getMessage());
}
?>