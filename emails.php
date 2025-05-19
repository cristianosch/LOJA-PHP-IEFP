<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



function enviarEmail($destino, $username, $conteudo, $assunto = "Confirma a tua conta")
{
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    require 'secrets.php';

    //Enviar o email de activação
    $mail = new PHPMailer(true);

    try {
        $mail->CharSet = "utf-8";
        //Server settings
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.sapo.pt';                         //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $EMAIL_SAPO;                            //SMTP username
        $mail->Password   = $EMAIL_PASS;                            //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        //Recipients
        $mail->setFrom($EMAIL_SAPO, $EMAIL_NOME);
        $mail->addAddress($destino, $username);     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $assunto;
        $mail->Body    = $conteudo;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
