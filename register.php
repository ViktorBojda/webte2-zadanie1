<?php 
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $sql = "INSERT INTO user (fullname, email, login, password) VALUES (?,?,?,?)";

    $fullName = $_POST["firstname"] . ' ' . $_POST["lastname"];

    $password_hash = password_hash($_POST['password'], PASSWORD_ARGON2ID);

    if ($stmt = $pdo->prepare($sql)) {
        $stmt->execute([$fullName])
    }
}
?>