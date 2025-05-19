
<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$APP_NAME=$_ENV['APP_NAME'];
$APP_ENV=$_ENV['APP_ENV'];
$APP_DEBUG=$_ENV['APP_DEBUG'];

$DB_HOST=$_ENV['DB_HOST'];
$DB_PORT=$_ENV['DB_PORT'];
$DB_DATABASE=$_ENV['DB_DATABASE'];
$DB_USERNAME=$_ENV['DB_USERNAME'];
$DB_PASSWORD=$_ENV['DB_PASSWORD'];

$EMAIL_PASS=$_ENV['EMAIL_PASS'];
$EMAIL_SAPO=$_ENV['EMAIL_SAPO'];
$EMAIL_NOME=$_ENV['EMAIL_NOME'];
$PAYPAL_CLIENT_ID=$_ENV['PAYPAL_CLIENT_ID'];

?>

