<?php


// IMPORTANTE: Esta página não têm html.

// 1º: Iniciar a sessão session_start()

session_start();
require "utils.php";
require "secrets.php";

// 2º: Ver se é administrador
if (!isAdmin()) {
    http_response_code(403);
    echo "Acesso negado.";
    exit();
}



// 3º: Detetar o envio de um formulário GET com o paramentro id
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {


    // 4º: Conectar à base de dados
    try {

        $con = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
        $id = $_GET["id"];

        // 5º: Remover o produto da base de dados (Query DELETE)
        $sql = $con->prepare("DELETE FROM Produto WHERE id = ?");
        $sql->bind_param("i", $id);
        $sql->execute();

        if ($sql->affected_rows > 0) {
            // 6º: Fornecer feedback ao utilizador (affected_rows)
            echo "Produto removido com sucesso.";
        } else {
            echo "Produto não encontrado ou não foi possível remover.";
        }
        
        //Esperar 2 segundos para ler a mensagem. 

        echo "<script> setTimeout(function(){
            window.location.href = 'admin_produtos.php';
        },2000);</script>";
    } catch (Exception $e) {
        echo "Erro 1768 - Problema de conexão com a base de dados. $e";
    }
}



?>