

<?php

//Ver se o user é adminstrador
session_start();
require "utils.php";
require "secrets.php";

if (!isAdmin()) {
    http_response_code(403);
    echo "Acesso negado.";
    exit();
}

//Ver se recebemos o POST com todos os dados
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"]) && isset($_POST["nome"]) && isset($_POST["preco"]) && isset($_POST["descricao"]) && isset($_POST["stock"])) {


    //Verificar se os campos são válidos
    if (empty($_POST["id"]) || empty($_POST["nome"]) || empty($_POST["preco"]) || empty($_POST["stock"])) {
        echo "Erro 4045 - Não foi possível editar o produto. Campos inválidos.";
        exit();
    }

    try {
        $con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        //Fazer update a todos campos e guardar na base de dados

        $sql = $con->prepare("UPDATE Produto SET nome = ?, preco = ?, descricao = ?, stock = ? WHERE id = ?");
        $sql->bind_param("sssii", $_POST["nome"], $_POST["preco"], $_POST["descricao"], $_POST["stock"], $_POST["id"]);
        $sql->execute();
      
        header("Location: admin_produtos.php");
      
        //Fechar a ligação
        $sql->close();
        $con->close();
    } catch (Exception $e) {
        echo "Erro 4046 - Não foi possível editar o produto. Erro ao conectar à base de dados.";
        exit();
    }
}



//Dar feedback ao utilizador

?>