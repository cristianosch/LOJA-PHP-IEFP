# 🛒 Loja PHP com Sistema de Autenticação

Este é um projeto de uma loja online desenvolvida em **PHP**, com funcionalidades como cadastro de utilizadores, login, ativação de conta via e-mail e área de administração para gerenciamento de produtos.

## ✨ Funcionalidades

- Cadastro de usuários com verificação por e-mail
- Login e logout com sessões seguras
- Painel de administração com proteção de acesso
- CRUD de produtos (adicionar, editar, remover)
- Sistema de permissões (administrador / utilizador comum)
- Integração com PayPal (pagamento simulado ou real)

## 🚀 Tecnologias

- PHP (puro, sem frameworks)
- MySQL
- HTML/CSS
- Bootstrap (opcional)
- PHPMailer (envio de e-mails)
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) para variáveis de ambiente
- Apache ou servidor local (XAMPP, Laragon etc.)

## 🔧 Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/cristianosch/LOJA-PHP-IEFP.git
   cd LOJA-PHP-IEFP

2. Crie o banco de dados MySQL e importe o arquivo loja_php_db.sql
   
3. Configure os dados de conexão em secrets.php
   
	$DB_HOST = "localhost";
	$DB_USERNAME = "root";
	$DB_PASSWORD = "";
	$DB_DATABASE = "nome_do_banco";

4. Configure seu servidor local (ex: XAMPP) para apontar para o diretório do projeto.
   
5.  Instale as dependências com Composer

Se ainda não tem o Composer, instale-o e depois rode:

	composer install

6. Teste o registro de um novo utilizador. Verifique seu email para ativar a conta.

📂 Estrutura de Pastas

	📁 LOJA-PHP-IEFP/

	├── ativarconta.php
	├── checkout.php
	├── login.php
	├── register.php
	├── admin_produtos.php
	├── utils.php
	├── secrets.php
	├── .env (NÃO incluido)
	├── carrinho.php
	├── adicionar_produto.php
	├── emails.php
	├── index.php
	├── logout.php
	├── recuperacao.php
	├── remover_produto.php
	├── registro.php

7. Configure o arquivo .env
   
Crie um arquivo .env na raiz do projeto com o seguinte conteúdo:

	DB_HOST=localhost
	DB_USERNAME=root
	DB_PASSWORD=
	DB_DATABASE=loja_php

	MAIL_HOST=smtp.seudominio.com
	MAIL_USERNAME=seu_email@dominio.com
	MAIL_PASSWORD=sua_senha
	MAIL_PORT=587
	MAIL_FROM_NAME="Loja PHP"
	MAIL_FROM_ADDRESS=seu_email@dominio.com

	PAYPAL_CLIENT_ID=SEU_CLIENT_ID_DO_PAYPAL
	PAYPAL_SECRET=SEU_SECRET_DO_PAYPAL

8. Configure o secrets.php

Você pode manter apenas as chamadas ao getenv() se estiver usando o .env. Exemplo:

	$DB_HOST = getenv('DB_HOST');
	$DB_USERNAME = getenv('DB_USERNAME');
	$DB_PASSWORD = getenv('DB_PASSWORD');
	$DB_DATABASE = getenv('DB_DATABASE');

9. Configure seu servidor local
    
Aponte o Apache (ou outro servidor) para a pasta do projeto. No XAMPP, coloque a pasta dentro de htdocs/.

10. Teste a aplicação
    
	Acesse o projeto no navegador (ex: http://localhost/loja-php)

	Registre um novo utilizador

	Verifique seu e-mail para ativar a conta

# 📬 Contato

Se tiver dúvidas ou sugestões, sinta-se à vontade para abrir uma issue ou enviar um pull request.

# 🚀 Dê uma estrela ⭐ no repositório se este projeto te ajudou!