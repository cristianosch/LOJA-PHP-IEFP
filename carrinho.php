<?php

session_start();

if (!isset($_SESSION['user'])) {
    echo "Sessão expirada ou utilizador não autenticado.";
    exit;
}

if (session_status() == PHP_SESSION_ACTIVE) {
    if (!isset($_SESSION["user"])) {
        header("Location: login.php");
        exit();
    }
}

require "utils.php";
require "secrets.php";


mysqli_report(MYSQLI_REPORT_ERROR);

$con = new mysqli("localhost", "root", "My@NewPass", "php-aula");
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

//Atualiza produtos no carrinho
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"]) && isset($_POST["quantidade"])) {
    if ($_POST["quantidade"] > 0) {
        $query = $con->prepare("UPDATE Carrinho SET quantidade = ? WHERE id = ?");
        $query->bind_param("ii", $_POST["quantidade"], $_POST["id"]);
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
        $query = $con->prepare("DELETE FROM Carrinho WHERE id = ?");
        $query->bind_param("i", $_POST["id"]);
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
                            Produto removido do carrinho!
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
                            Erro ao remover produto do carrinho!
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
}



$sql = $con->prepare("SELECT Carrinho.id as id, quantidade, preco, nome  FROM Carrinho, Produto where utilizadorId = ? and Carrinho.produtoid = Produto.id ORDER BY Produto.nome;");
$sql->bind_param("i", $_SESSION["user"]["id"]);
$sql->execute();
$result = $sql->get_result();
$produtosnocarrinho = $result->fetch_all(MYSQLI_ASSOC);
$sql->close();
$con->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5 align-items-center justify-content-center">
        <div class="text-end m-3">
        <a href="index.php" class="btn btn-primary ">Home</a>
    </div>
        <h1 class="text-center mb-4">Carrinho</h1>

        <?php
        $total = 0.0;
        foreach ($produtosnocarrinho as $produto) {
            $total += $produto["quantidade"] * $produto["preco"];
        ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Nome: <?php echo $produto["nome"]; ?></h5>
                    <form method="POST" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="quantidade" class="form-label">Quantidade:</label>
                            <input type="number" name="quantidade" class="form-control" value="<?php echo $produto["quantidade"]; ?>" min="0">
                        </div>
                        <input type="hidden" name="id" value="<?php echo $produto["id"]; ?>">
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Atualizar</button>
                        </div>
                    </form>
                    <p class="mt-3">Preço Unitário: <strong><?php echo $produto["preco"]; ?>€</strong></p>
                    <p>Preço Total: <strong><?php echo $produto["quantidade"] * $produto["preco"]; ?>€</strong></p>
                </div>
            </div>
        <?php
        }
        ?>

        <div class="text-center">
            <h2>Total: <strong><?php echo $total; ?>€</strong></h2>
        </div>
        <!-- PayPal Button -->
        <div class="d-flex justify-content-center">
            <div id="paypal-button-container" class="w-50"></div>
        </div>

    </div>

    <!-- PayPal SDK -->
    <script src=<?php echo "https://www.paypal.com/sdk/js?client-id=$PAYPAL_CLIENT_ID&currency=EUR" ?>></script>

    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo $total; ?>'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    window.location.href = "checkout.php";
                });
            },
            onError: function(err) {
                console.error('Erro no pagamento:', err);
                alert('Ocorreu um erro durante o pagamento. Tente novamente.');
            }
        }).render('#paypal-button-container');
    </script>

    <!-- Bootstrap JS (optional, for interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>