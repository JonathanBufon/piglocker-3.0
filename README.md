# PigLocker 3.0 - Cofre de Senhas Seguro

Bem-vindo ao PigLocker! Este é um projeto de cofre de senhas projetado para armazenar suas credenciais de forma segura. Ele utiliza criptografia para proteger seus dados e oferece autenticação de dois fatores (2FA) para uma camada extra de segurança.

## Pré-requisitos

Antes de começar, certifique-se de que você tem os seguintes softwares instalados no seu sistema (baseado em Debian/Ubuntu):

* PHP 8.1
* MariaDB (ou MySQL)
* Composer

## Instalação

Siga os passos abaixo para configurar o ambiente de desenvolvimento.

1.  **Clone o repositório:**
    ```bash
    git clone [https://github.com/https://github.com/JonathanBufon/piglocker-3.0](https://github.com/https://github.com/JonathanBufon/piglocker-3.0)
    cd piglocker-3.0
    ```

2.  **Instale as dependências do sistema:**
    Abra o terminal e execute o seguinte comando para instalar o PHP, o servidor de banco de dados e as extensões PHP necessárias.
    ```bash
    sudo apt update
    sudo apt install php8.1 mariadb-server composer php8.1-mysql php8.1-gd
    ```

3.  **Instale as dependências do Composer:**
    Execute os comandos abaixo para instalar as bibliotecas PHP necessárias para o projeto.
    ```bash
    composer require pragmarx/google2fa
    composer require endroid/qr-code
    ```

## Configuração do Banco de Dados

Para que a aplicação funcione, você precisa configurar o banco de dados.

1.  **Acesse o console do MariaDB/MySQL:**
    Execute o comando abaixo para entrar no console do banco de dados como usuário root. Você precisará da sua senha de superusuário (sudo).
    ```bash
    sudo mysql -u root
    ```

2.  **Crie o Usuário do Banco de Dados:**
    Dentro do console do MySQL, execute o comando abaixo para criar um novo usuário e conceder-lhe as permissões necessárias.
    ```sql
    CREATE USER 'testuser'@'localhost' IDENTIFIED BY 'testpass123';
    ```

3.  **Crie o Banco de Dados:**
    Agora, crie o banco de dados que será usado pela aplicação.
    ```sql
    CREATE DATABASE piglocker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

4.  **Conceda Privilégios ao Usuário:**
    Conceda ao usuário `testuser` as permissões para acessar e modificar o banco de dados `piglocker`.
    ```sql
    GRANT SELECT, INSERT, UPDATE, DELETE ON piglocker.* TO 'testuser'@'localhost';
    ```

5.  **Atualize os Privilégios e Saia:**
    Para que as alterações entrem em vigor, execute o seguinte comando e saia do console.
    ```sql
    FLUSH PRIVILEGES;
    EXIT;
    ```

### Executando o Script de Criação das Tabelas

Você pode criar um arquivo `database_setup.sql` com o conteúdo abaixo e executá-lo para criar todas as tabelas e fazer as alterações necessárias de uma só vez.

**Conteúdo para o arquivo `database_setup.sql`:**
```sql
-- Seleciona o banco de dados a ser utilizado
USE piglocker;

-- Tabela para armazenar os dados de login dos usuários
CREATE TABLE usuarios (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  senha_hash VARCHAR(64) NOT NULL,
  tfa_secret TEXT NULL,
  tfa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela para armazenar as credenciais salvas no cofre
CREATE TABLE credenciais (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT(11) UNSIGNED NOT NULL,
  nome VARCHAR(255) NOT NULL,
  login VARCHAR(255) NOT NULL,
  senha_cifrada TEXT NOT NULL,
  iv VARCHAR(32) NOT NULL,
  site VARCHAR(255),
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

**Para executar o script, use o seguinte comando no seu terminal:**

Este comando usará o usuário `testuser` que você criou para executar os comandos SQL do arquivo e configurar as tabelas no banco de dados `piglocker`.
```bash
mysql -u testuser -p piglocker < database_setup.sql
```

Você será solicitado a inserir a senha do `testuser` (que é `testpass123`).

---

Pronto! Agora seu ambiente está configurado e pronto para executar a aplicação.
