<?php

session_start();

require "utils.php";
require "secrets.php";

if (!isset($_SESSION["user"]) || empty($_SESSION["user"])) {
        header("Location: login.php");
}


mysqli_report(MYSQLI_REPORT_ERROR);
$con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

//Guardar produto no carrinho
if ($_SERVER["REQUEST_METHOD"]  == "POST" && isset($_POST["id"]) && isset($_POST["quantidade"])) {
    $userId = $_SESSION["user"]["id"];
    $produtoId = $_POST["id"];
    $quantidade = $_POST["quantidade"];

    $query = $con->prepare("SELECT * FROM Carrinho WHERE utilizadorId = ? AND produtoId = ?");
    $query->bind_param("ii", $userId, $produtoId);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $query = $con->prepare("UPDATE Carrinho SET quantidade = quantidade + ? WHERE utilizadorId = ? AND produtoId = ?");
        $query->bind_param("iii", $quantidade, $userId, $produtoId);
        $query->execute();
        if ($query->affected_rows > 0) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                let toast = document.createElement('div');
                toast.className = 'toast align-items-center text-bg-success border-0';
                toast.style.position = 'fixed';
                toast.style.top = '20px';
                toast.style.right = '20px';
                toast.style.zIndex = '1050';
                toast.innerHTML = `
                    <div class='d-flex'>
                    <div class='toast-body'>
                        Produto atualizado no carrinho!
                    </div>
                    <button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast' aria-label='Close'></button>
                    </div>`;
                document.body.appendChild(toast);
                let bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                });
              </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                let toast = document.createElement('div');
                toast.className = 'toast align-items-center text-bg-danger border-0';
                toast.style.position = 'fixed';
                toast.style.top = '20px';
                toast.style.right = '20px';
                toast.style.zIndex = '1050';
                toast.innerHTML = `
                    <div class='d-flex'>
                    <div class='toast-body'>
                        Erro ao atualizar produto no carrinho!
                    </div>
                    <button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast' aria-label='Close'></button>
                    </div>`;
                document.body.appendChild(toast);
                let bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                });
              </script>";
        }
    } else {
        $query = $con->prepare("INSERT INTO Carrinho (utilizadorId,produtoId,quantidade) VALUES (?,?,?)");
        $query->bind_param("iii", $userId, $produtoId, $quantidade);
        $query->execute();
        if ($query->affected_rows > 0) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        let toast = document.createElement('div');
                        toast.className = 'toast align-items-center text-bg-success border-0';
                        toast.style.position = 'fixed';
                        toast.style.top = '20px';
                        toast.style.right = '20px';
                        toast.style.zIndex = '1050';
                        toast.innerHTML = `
                            <div class='d-flex'>
                                <div class='toast-body'>
                                    Produto adicionado ao carrinho!
                                </div>
                                <button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast' aria-label='Close'></button>
                            </div>`;
                        document.body.appendChild(toast);
                        let bsToast = new bootstrap.Toast(toast);
                        bsToast.show();
                    });
                  </script>";
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        let toast = document.createElement('div');
                        toast.className = 'toast align-items-center text-bg-danger border-0';
                        toast.style.position = 'fixed';
                        toast.style.top = '20px';
                        toast.style.right = '20px';
                        toast.style.zIndex = '1050';
                        toast.innerHTML = `
                            <div class='d-flex'>
                                <div class='toast-body'>
                                    Erro ao adicionar produto ao carrinho!
                                </div>
                                <button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast' aria-label='Close'></button>
                            </div>`;
                        document.body.appendChild(toast);
                        let bsToast = new bootstrap.Toast(toast);
                        bsToast.show();
                    });
                  </script>";
        }
    }

    $query->close();
}

$search = "";
if (isset($_GET["search"])) {
    $search = $_GET["search"];
}

$sql = $con->prepare("SELECT * FROM Produto WHERE (nome LIKE ? OR descricao LIKE ?) AND stock > 0");

$search = "%" . $search . "%";
$sql->bind_param("ss", $search, $search);
$sql->execute();
$result = $sql->get_result();
$produtos = $result->fetch_all(MYSQLI_ASSOC);
$sql->close();
$con->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">
     <div class="text-end m-3">
        <?php if(isAdmin()){ ?>
            <a href="admin_produtos.php" class="btn btn-primary">Área de administração</a>
        <?php } ?>
        <a href="logout.php" class="btn btn-danger ">Logout</a>
    </div>
    <div class="container mt-5">
        <?php
        if (isAdmin()) {
            echo "<h1 class='text-center text-primary'>Bem-vindo/a administrador, ".$_SESSION['user']['username']."!</h1>";
        } else {
            echo "<h1 class='text-center text-primary'>Bem-vindo/a, ".$_SESSION['user']['username']."!</h1>";
        }
        ?>
       
        <div class="text-end">
            <a href="carrinho.php" class="btn btn-outline-primary">
            Carrinho
            </a>
        </div>

        <form method="GET" class="my-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Pesquisar produto">
                <button type="submit" class="btn btn-primary">Pesquisar</button>
            </div>
        </form>

        <div class="row">
            <?php
            foreach ($produtos as $produto) {
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $produto["nome"]; ?></h5>
                            <p class="card-text">Preço: <?php echo $produto["preco"]; ?>€</p>
                            <p class="card-text">Descrição: <?php echo $produto["descricao"]; ?></p>
                            
                            <?php if (!empty($produto["imagem"])) { ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($produto["imagem"]); ?>" class="card-img-top mt-5 mb-5" alt="Imagem do produto" style="width: 100%; height: 200px; object-fit: contain;">
                            <?php } ?>
                            
                            <form method="POST">
                                <input type="hidden" name="id" value="<?php echo $produto["id"]; ?>">
                                <div class="input-group mb-3">
                                    <input type="number" name="quantidade" class="form-control" min="1" max="<?php echo $produto["stock"]; ?>" value="1">
                                    <button type="submit" class="btn btn-success">Adicionar ao carrinho</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>

</body>

</html>
