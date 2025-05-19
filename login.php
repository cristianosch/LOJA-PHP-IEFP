<?php
require "emails.php";
require "secrets.php";

session_start();


// Redireciona para o index se o utilizador já estiver autenticado
if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

// Inicializa variável de controle do 2FA
$two_factor = false;
$user_id_temp = null;

// PROCESSO DO LOGIN - PRIMEIRO PASSO
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    $query = $con->prepare("SELECT * FROM Utilizador WHERE (username = ? OR email = ?) AND active = 1");
    $query->bind_param("ss", $_POST["email"], $_POST["email"]);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($_POST["password"], $user["password"])) {
            // Gerar código e enviar
            $code = rand(1000, 9999);

            $update = $con->prepare("UPDATE Utilizador SET two_factor_code = ? WHERE id = ?");
            $update->bind_param("si", $code, $user["id"]);
            $update->execute();

            if ($update->affected_rows > 0) {
                if (enviarEmail($user["email"], $user["username"], "O seu código de dois fatores é: " . $code, "Código de dois fatores")) {
                    $_SESSION["2fa_user_id"] = $user["id"];
                    $two_factor = true;
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
                                        Erro 1542 - Ocorreu um erro tente mais tarde.
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
                                    Erro 1234 - Ocorreu um erro tente mais tarde.
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
                                Erro 2424 - Utilizador ou password inválidos.
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
                            Erro 2345 - Utilizador ou password inválidos.
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

// VERIFICAÇÃO DO 2FA - SEGUNDO PASSO
if (isset($_POST["code"]) && isset($_SESSION["2fa_user_id"])) {
    $con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    $query = $con->prepare("SELECT * FROM Utilizador WHERE id = ? AND two_factor_code = ?");
    $query->bind_param("is", $_SESSION["2fa_user_id"], $_POST["code"]);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION["user"] = $user;
        unset($_SESSION["2fa_user_id"]); // Remover a sessão temporária
        header("Location: index.php");
        exit();
    } else {
        echo "Código inválido.";
        $two_factor = true;
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-center align-items-center vh-100">
            <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                <?php if ($two_factor) { ?>
                    <h2 class="text-success text-center">Código dois-fatores enviado!<br>Verifique o seu email.</h2>
                    <form method="POST" class="mt-4">
                    <div class="mb-3">
                        <label for="code" class="form-label">Código de 4 dígitos:</label>
                        <input type="text" id="code" name="code" class="form-control" required>
                    </div>
                    <input type="hidden" name='id' value="<?php echo $user['id']; ?>">
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
                <?php } else { ?>
                    <h2 class="text-center">Login</h2>
                    <form method="POST" class="mt-4">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email ou Username:</label>
                        <input type="text" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Palavra-passe:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="text-end mb-3"> 
                        <a href="recuperacao.php" class="text-decoration-none">Recuperar palavra-passe</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    <a href="registo.php" class="btn btn-secondary w-100 mt-2">Registar</a>
                    </form>
                <?php } ?>
                </div>
            </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
