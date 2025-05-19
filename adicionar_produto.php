<?php

session_start();
require "utils.php";
require "secrets.php";

// Verifica se o utilizador é Administrador (Role Based Security)
if (!isAdmin()) {
    http_response_code(403);
    echo "Acesso negado.";
    exit();
}

$error_message = false;
$success_message = false;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nome"]) && isset($_POST["preco"]) && isset($_POST["stock"])) {
    $nome = $_POST["nome"];
    $descricao = $_POST["descricao"];
    $preco = $_POST["preco"];
    $stock = $_POST["stock"];
    $image_content = null;

    if (strlen($descricao) > 200) {
        $error_message = true;
        $message = "A descrição não pode ter mais de 200 caracteres.";
    }

    if (empty($nome) || empty($preco) || empty($stock)) {
        $error_message = true;
        $message = "Por favor preencha todos os campos obrigatórios.";
    }

    if (!is_numeric($preco) || !is_numeric($stock)) {
        $error_message = true;
        $message = "Preço e stock devem ser números.";
    }

    if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == 0) {
        $imagem = $_FILES["imagem"];
        $image_content = file_get_contents($imagem["tmp_name"]);
    }

    try {
        $con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

        $sql = $con->prepare("INSERT INTO Produto (nome, descricao, preco, stock, imagem) VALUES (?, ?, ?, ?, ?)");
        $sql->bind_param("ssdis", $nome, $descricao, $preco, $stock, $image_content);
        $sql->execute();

        if ($sql->affected_rows > 0) {
            $success_message = true;
            $message = "Produto adicionado com sucesso.";
        } else {
            $error_message = true;
            $message = "Erro ao adicionar produto.";
        }
        $sql->close();
        $con->close();
    } catch (Exception $e) {
        $error_message = true;
        $message = "Erro ao adicionar produto. $e";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="d-flex container mt-5 align-items-center justify-content-center flex-column">
        <?php
        if ($error_message) {
            echo "<div class='alert alert-danger'>$message</div>";
        } elseif ($success_message) {
            echo "<div class='alert alert-success'>$message</div>";
        }
        ?>

        <h1 class="mb-4">Adicionar Produto</h1>

        <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm ">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome:</label>
                <input type="text" id="nome" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição:</label>
                <textarea id="descricao" name="descricao" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label for="preco" class="form-label">Preço:</label>
                <input type="number" id="preco" name="preco" class="form-control" step="0.01" required>
            </div>

            <div class="mb-3">
                <label for="stock" class="form-label">Stock:</label>
                <input type="number" id="stock" name="stock" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="imagem" class="form-label">Imagem:</label>
                <input type="file" id="imagem" name="imagem" class="form-control" accept="image/*">
            </div>
            <div class="d-flex justify-content-center align-items-center">
                <button type="submit" class="btn btn-primary  mt-3">Adicionar Produto</button>
            </div>
            <div class="d-flex justify-content-center align-items-center">
                <a href="admin_produtos.php" class="btn btn-outline-primary  mt-3">Back</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
