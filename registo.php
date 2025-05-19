<?php

require "emails.php";
require "secrets.php";

$error_message = false;
$success_message = false;
$message = "";
if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["confpass"]) && isset($_POST["email"])) {

    if ($_POST["password"] != $_POST["confpass"]) {
        $error_message = true;
        $message = $message . "As palavras-passe não coincidem.<br>";
    }
    if ($_POST["email"] == "") {
        $error_message = true;
        $message = $message . "O email não pode estar vazio.<br>";
    }
    if ($_POST["username"] == "") {
        $error_message = true;
        $message = $message . "O nome de utilizador não pode estar vazio.<br>";
    }
    if (!preg_match('/[A-Z]/', $_POST["password"])) {
        $error_message = true;
        $message = $message . "A palavra-passe deve conter pelo menos uma letra maiúscula.<br>";
    }
    if (!preg_match('/[a-z]/', $_POST["password"])) {
        $error_message = true;
        $message = $message . "A palavra-passe deve conter pelo menos uma letra minúscula.<br>";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $_POST["password"])) {
        $error_message = true;
        $message = $message . "A palavra-passe deve conter pelo menos um caracter especial.<br>";
    }
    if (strlen($_POST["password"]) < 8) {
        $error_message = true;
        $message = $message . "A palavra-passe deve conter pelo menos 8 caracteres.<br>";
    }

    $connection = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $query = $connection->prepare("SELECT * FROM Utilizador WHERE username = ?");
    $query->bind_param("s", $_POST["username"]);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $error_message = true;
        $message = $message . "O nome de utilizador já existe.<br>";
    }

    $query = $connection->prepare("SELECT * FROM Utilizador WHERE email = ?");
    $query->bind_param("s", $_POST["email"]);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $error_message = true;
        $message = $message . "O email já existe.<br>";
    }

    if (!$error_message) {
    }

    //Ver se o email já existe
    $query = $connection->prepare("SELECT * FROM Utilizador WHERE email = ?");
    $query->bind_param("s", $_POST["email"]);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $error_message = true;
        $message = $message . "O email já existe.<br>";
    }

    if (!$error_message) {

        //Encriptar a password
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

        //Gerar o token de activação
        $token = bin2hex(random_bytes(16));

        //Inserir o utilizador na base de dados
        $query = $connection->prepare("INSERT INTO Utilizador (username, password, email, codigo_ativacao) VALUES (?, ?, ?, ?)");
        $query->bind_param("ssss", $_POST["username"], $password, $_POST["email"], $token);
        $query->execute();
        $query->close();
        $connection->close();


        if(enviarEmail($_POST["email"], $_POST["username"], '<a href="http://localhost/LOJA-PHP-IEFP/ativarconta.php?email=' . $_POST["email"] . '&activation_code=' . $token . '">Confirma a tua conta</a>')){
            $success_message = true;
            $message = "Foi enviado um email para " . $_POST["email"] . " com o link de ativação da conta.<br>";
        }else{
            $error_message = true;
            $message = $message . "Não foi possível enviar o email de ativação.<br>";
        }

    }
}

?>


<!DOCTYPE html>
<html lang="pt-PT">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h4 text-center mb-4">Registo</h1>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nome de Utilizador:</label>
                                <input type="text" id="username" name="username" class="form-control" required value="<?php echo isset($_POST["username"]) ? $_POST["username"] : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Palavra-passe:</label>
                                <input type="password" id="password" name="password" class="form-control" required value="<?php echo isset($_POST["password"]) ? $_POST["password"] : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="confpass" class="form-label">Confirma Palavra-passe:</label>
                                <input type="password" id="confpass" name="confpass" class="form-control" required value="<?php echo isset($_POST["confpass"]) ? $_POST["confpass"] : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" id="email" name="email" class="form-control" required value="<?php echo isset($_POST["email"]) ? $_POST["email"] : ''; ?>">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Registar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional, for interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>