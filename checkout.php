<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "utils.php";
require "secrets.php";
require "emails.php";

if (session_status() == PHP_SESSION_ACTIVE) {
    if (!isset($_SESSION["user"])) {
        header("Location: login.php");
        exit;
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$utilizadorId = $_SESSION["user"]["id"];

// 1º - Guardar a encomenda
$sqlEncomenda = "INSERT INTO Encomenda (utilizadorId) VALUES (?)";
$stmt = $con->prepare($sqlEncomenda);
$stmt->bind_param("i", $utilizadorId);
$stmt->execute();
$encomendaId = $stmt->insert_id;

// 2º - Atualizar stock dos produtos no carrinho
$sqlCarrinho = "SELECT * FROM Carrinho WHERE utilizadorId = ?";
$stmt = $con->prepare($sqlCarrinho);
$stmt->bind_param("i", $utilizadorId);
$stmt->execute();
$result = $stmt->get_result();
$itensCarrinho = $result->fetch_all(MYSQLI_ASSOC);

foreach ($itensCarrinho as $item) {
    $produtoId = $item['produtoId'];
    $quantidade = $item['quantidade'];

    // Atualiza stock
    $sqlUpdateStock = "UPDATE Produto SET stock = stock - ? WHERE id = ?";
    $stmt = $con->prepare($sqlUpdateStock);
    $stmt->bind_param("ii", $quantidade, $produtoId);
    $stmt->execute();
}

// 3º - Buscar nome e email do utilizador
$sqlEmail = "SELECT username, email FROM Utilizador WHERE id = ?";
$stmt = $con->prepare($sqlEmail);
$stmt->bind_param("i", $utilizadorId);
$stmt->execute();
$result = $stmt->get_result();
$utilizador = $result->fetch_assoc();

$email = $utilizador['email'];
$nomeUsuario = $utilizador['username'];

// Criar mensagem de email
$assunto = "Confirmação de Encomenda $encomendaId";
$mensagem = "<h1>Olá {$nomeUsuario},</h1>";
$mensagem .= "<p>Recebemos a sua encomenda de registro numero <strong>$encomendaId</strong> com sucesso!</p>";
$mensagem .= "<h3>Detalhes da Encomenda:</h3>";
$mensagem .= "<ul>";

$total = 0;

foreach ($itensCarrinho as $item) {
    $produtoId = $item['produtoId'];
    $quantidade = $item['quantidade'];

    $sqlProduto = "SELECT nome, preco FROM Produto WHERE id = ?"; 
    $stmtProduto = $con->prepare($sqlProduto);
    $stmtProduto->bind_param("i", $produtoId);
    $stmtProduto->execute();
    $resProduto = $stmtProduto->get_result();
    $produto = $resProduto->fetch_assoc();

    $subtotal = $produto['preco'] * $quantidade;
    $total += $subtotal;

    $mensagem .= "<li>{$produto['nome']} - {$quantidade} unidade(s) - " . number_format($subtotal, 2, ',', '.') . "€</li>";
}

$mensagem .= "</ul>";
$mensagem .= "<p><strong>Total: " . number_format($total, 2, ',', '.') . "€</strong></p>";
$mensagem .= "<p>Obrigado por comprar conosco!</p>";

enviarEmail($email, $nomeUsuario, $mensagem, $assunto);

// 4º - Limpar carrinho
$sqlDelete = "DELETE FROM Carrinho WHERE utilizadorId = ?";
$stmt = $con->prepare($sqlDelete);
$stmt->bind_param("i", $utilizadorId);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container text-center mt-5">
        <h1 class="display-4">Obrigado pela sua compra!</h1>
        <p class="lead">Recebemos a sua encomenda com sucesso. Um email foi enviado para <strong><?= htmlspecialchars($email) ?></strong>.</p>
        <a href="index.php" class="btn btn-primary mt-3">Homepage</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
