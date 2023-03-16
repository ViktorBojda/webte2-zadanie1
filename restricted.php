<?php 
if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || 
    (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true)) {}
else
    exit(header('Location: index.php'));
?>