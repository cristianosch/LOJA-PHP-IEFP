<?php

session_start();
require "utils.php";
require "secrets.php";

if (!isAdmin()) {
    http_response_code(403);
    echo "Acesso negado.";
    exit();
}

$error_message = false;
$success_message = false;
$message = "";
$produtos = [];

try {
    $con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

    $sql = "SELECT * FROM Produto";
    $result = $con->query($sql);
    if ($result->num_rows > 0) {
        $produtos = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $produtos = [];
    }
} catch (Exception $e) {
    $error_message = true;
    $message = "Erro ao conectar à base de dados: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de produtos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h1 class="mb-4">Administração de produtos</h1>
        <a href="index.php" class="btn btn-outline-primary mb-3">Home</a>

        <a href="adicionar_produto.php" class="btn btn-primary mb-3">Adicionar Produto</a>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Descrição</th>
                    <th>Stock</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <form action="editar_produto.php" method="POST">
                            <td><input type="number" name="id" value="<?php echo $produto['id']; ?>" class="form-control" readonly></td>
                            <td><input type="text" name="nome" value="<?php echo $produto['nome']; ?>" class="form-control"></td>
                            <td><input type="number" step="0.01" name="preco" value="<?php echo $produto['preco']; ?>" class="form-control"></td>
                            <td><input type="text" name="descricao" value="<?php echo $produto['descricao']; ?>" class="form-control"></td>
                            <td><input type="number" name="stock" value="<?php echo $produto['stock']; ?>" class="form-control"></td>
                            <td>
                                <input type="submit" value="Editar" class="btn btn-warning btn-sm">
                                <a href="remover_produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-danger btn-sm">Remover</a>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>