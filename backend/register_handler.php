<?php

// depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui os arquivos de configuração e conexão
require_once 'config.php'; // Inicia a sessão
require_once 'db_connection.php';

// --- PASSO 1: Recebe e valida o e-mail ---
if (isset($_POST['step']) && $_POST['step'] == '1') {
    
    // Validação básica
    if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        // Redireciona de volta com uma mensagem de erro
        header('Location: ../signup.html?error=invalid_email');
        exit();
    }
    if (!isset($_POST['agree_terms'])) {
        header('Location: ../signup.html?error=terms');
        exit();
    }

    $email = $_POST['email'];

    // Verifica se o e-mail já existe no banco de dados
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // E-mail já cadastrado, redireciona com erro
        header('Location: ../signup.html?error=email_exists');
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Se tudo estiver certo, armazena o e-mail na sessão e avança para o próximo passo
    $_SESSION['signup_email'] = $email;
    header('Location: ../signup_password.html');
    exit();
}

// --- PASSO 2: Recebe a senha e finaliza o cadastro ---
elseif (isset($_POST['step']) && $_POST['step'] == '2') {

    // Verifica se o e-mail da sessão existe (evita acesso direto à página de senha)
    if (!isset($_SESSION['signup_email'])) {
        header('Location: ../signup.html?error=session_expired');
        exit();
    }

    // Validação básica da senha
    if (!isset($_POST['master_password']) || !isset($_POST['confirm_password'])) {
        header('Location: ../signup_password.html?error=missing_fields');
        exit();
    }

    $master_password = $_POST['master_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verifica se as senhas coincidem
    if ($master_password !== $confirm_password) {
        header('Location: ../signup_password.html?error=password_mismatch');
        exit();
    }

    // Verifica o comprimento mínimo da senha
    if (strlen($master_password) < 12) {
        header('Location: ../signup_password.html?error=password_short');
        exit();
    }

    // Se todas as validações passaram, cria o usuário
    $email = $_SESSION['signup_email'];
    $senha_hash = hash('sha256', $master_password); // Usa SHA-256 para o hash

    $stmt = $conn->prepare("INSERT INTO usuarios (email, senha_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $senha_hash);

    if ($stmt->execute()) {
        // Limpa a sessão e redireciona para o login com mensagem de sucesso
        unset($_SESSION['signup_email']);
        header('Location: ../index.html?success=registered');
    } else {
        // Erro ao inserir no banco
        header('Location: ../signup.html?error=db_error');
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Se o script for acessado de forma inválida, redireciona para a primeira página
header('Location: ../signup.html');
exit();