<?php
require "secrets.php";

if (isset($_GET["email"]) && isset($_GET["activation_code"])) {

    //Conectar com a base de dados
    $con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    //Verificar se a conexão foi bem sucedida
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    //Preparar a query
    $query = $con->prepare("SELECT * FROM Utilizador WHERE email = ? AND codigo_ativacao = ?");
    $query->bind_param("ss", $_GET["email"], $_GET["activation_code"]);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        //Ativar a conta
        $query = $con->prepare("UPDATE Utilizador SET active = 1 WHERE email = ?");
        $query->bind_param("s", $_GET["email"]);
        $query->execute();
        //Verificar se a atualização foi bem sucedida
        if ($query->affected_rows > 0) {
            echo "Conta ativada com sucesso!";
            header("Location: login.php");
        } else {
            echo "Erro ao ativar a conta.";
        }
    } else {
        echo "Código de ativação inválido ou email inválido.";
    }
}
?>


