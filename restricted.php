<?php 
if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || 
    (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)) {}
else
    exit(header('Location: index.php'));
?>