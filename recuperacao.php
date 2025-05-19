<?php

require "emails.php";
require "secrets.php";

$success_message = false;
$error_message = false;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"]) && !isset($_POST["token"])) {
    // Só vai entrar se o formulário for submetido

    //Ver se o email existe na base de dados
    try {
        $con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

        $sql = $con->prepare("SELECT * FROM utilizador WHERE email = ?");
        $sql->bind_param("s", $_POST["email"]);
        $sql->execute();
        $result = $sql->get_result();
        $user = $result->fetch_assoc();
        if ($result->num_rows > 0) {
            //Se existir, gerar o código token e enviar por email um link para o utilizador
            //http://localhost/recuperacao.php?email=""&token=
            $token = bin2hex(random_bytes(16));
            $sql = $con->prepare("UPDATE utilizador SET codigo_ativacao = ?, updated_at = now() WHERE email = ?");
            $sql->bind_param("ss", $token, $_POST["email"]);
            $sql->execute();
            //Verificar se a atualização foi bem sucedida
            if ($sql->affected_rows > 0) {
                if (enviarEmail($user["email"], $user["username"], '<a href="http://localhost/recuperacao.php?email=' . $user["email"] . '&token=' . $token . '">Recuperar password</a>')) {
                    $success_message = true;
                    $message = "Foi enviado um email para " . $_POST["email"] . " com o link de recuperação de password.<br>";
                } else {
                    $error_message = true;
                    //Erro 1426 - Não foi possível enviar o email de recuperação de password
                    $message = "Erro 1426 - Ocorreu um erro inesperado.";
                }
            } else {
                $error_message = true;
                //Erro 1425 - Falhou a gravação do token na base de dados
                $message = "Erro 1425 - Ocorreu um erro inesperado.";
            }
        } else {
            $error_message = true;
            //Erro 1424 - O email não existe na base de dados
            $message = "Erro 1424 - Ocorreu um erro inesperado.";
        }
    } catch (Exception $e) {
        $error_message = true;
        $message = "Erro 4235 - Erro de conexão com a base de dados.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"]) && isset($_POST["token"]) && isset($_POST["password"]) && isset($_POST["conf_password"])) {

    try {
        $con = new mysqli("localhost", "root", "", "loja_php");
        $sql = $con->prepare("SELECT * FROM utilizador WHERE email = ? AND codigo_ativacao = ?");
        $sql->bind_param("ss", $_POST["email"], $_POST["token"]);
        $sql->execute();
        $result = $sql->get_result();
        if ($result->num_rows > 0) {

            if ($_POST["password"] != $_POST["conf_password"]) {
                $error_message = true;
                //Erro 1428 - As passwords não coincidem
                $message = "Erro 1428 - As passwords não coincidem.";
            } else {
                // Verificar se a password têm - Um Caracter Maiúsculo, Um caracter minusculo, Um Caracter Especial, 8 Caracteres no minimo
                //Um Caracter Maiúsculo
                if (!preg_match('/[A-Z]/', $_POST["password"])) {
                    $error_message = true;
                    $message = $message . "A palavra-passe deve conter pelo menos uma letra maiúscula.<br>";
                }
                // Um Caracter Minusculo
                if (!preg_match('/[a-z]/', $_POST["password"])) {
                    $error_message = true;
                    $message = $message . "A palavra-passe deve conter pelo menos uma letra minúscula.<br>";
                }
                // Um Caracter Especial
                if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $_POST["password"])) {
                    $error_message = true;
                    $message = $message . "A palavra-passe deve conter pelo menos um caracter especial.<br>";
                }
                // 8 Caracteres no minimo
                if (strlen($_POST["password"]) < 8) {
                    $error_message = true;
                    $message = $message . "A palavra-passe deve conter pelo menos 8 caracteres.<br>";
                }
                if (!$error_message) {

                    $encripted_password = password_hash($_POST["password"], PASSWORD_BCRYPT);
                    $sql = $con->prepare("UPDATE utilizador SET password = ?, codigo_ativacao = NULL, updated_at=now(), active = 1 WHERE email = ?");
                    $sql->bind_param("ss", $encripted_password, $_POST["email"]);
                    $sql->execute();

                    if ($sql->affected_rows > 0) {
                        $success_message = true;
                        $message = "A sua password foi alterada com sucesso.";
                    } else {
                        $error_message = true;
                        //Erro 1429 - Falhou a gravação da nova password na base de dados
                        $message = "Erro 1429 - Ocorreu um erro inesperado.";
                    }
                }
            }
        } else {
            $error_message = true;
            //Erro 1427 - Token ou Email inválido
            $message = "Erro 1427 - Token ou Email inválido.";
        }
    } catch (Exception $e) {
        $error_message = true;
        $message = "Erro 4235 - Erro de conexão com a base de dados.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de password</title>
</head>

<body>

    <?php
    if ($error_message) {
        echo "<p style='color: red;'>$message</p>";
    }
    if ($success_message) {
        echo "<p style='color: green;'>$message</p>";
    }
    ?>
    <?php
    if (($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["email"]) && isset($_GET["token"])) || ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"]) && isset($_POST["token"]) && isset($_POST["password"]) && isset($_POST["conf_password"]))) {
    ?>
        <form method="POST">
            <input type="hidden" name="email" value="<?php echo $_GET["email"]; ?>">
            <input type="hidden" name="token" value="<?php echo $_GET["token"]; ?>">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required value="<?php if (isset($_POST["password"])) {
                                                                                        echo $_POST["password"];
                                                                                    } else {
                                                                                        echo "";
                                                                                    } ?>">
            <br>
            <label for="conf_password">Confirm password:</label>
            <input type="password" name="conf_password" id="conf_password" required value="<?php if (isset($_POST["conf_password"])) {
                                                                                                echo $_POST["conf_password"];
                                                                                            } else {
                                                                                                echo "";
                                                                                            } ?>">
            <br>
            <button type="submit">Alterar</button>
            <!-- Ocultar ou Mostrar as passwords com js -->
            <button type="button" onclick="(function(){
                const passwordField = document.getElementById('password');
                const confPasswordField = document.getElementById('conf_password');
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    confPasswordField.type = 'text';
                } else {
                    passwordField.type = 'password';
                    confPasswordField.type = 'password';
                }
            })()">Mostrar passwords</button>
        </form>

    <?php
    } else {
    ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <br>
            <button type="submit">Recuperar</button>
        </form>
    <?php
    }
    ?>

</body>

</html>