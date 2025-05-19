<?php
function isAdmin()
{
    if (isset($_SESSION["user"])) {
        if ($_SESSION["user"]["roleId"] == 1) {
            return true;
        } else {
            return false;
        }
    } else {
        http_response_code(403);
        header("Location: login.php");
    }
}
?>