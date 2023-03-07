<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!empty($_POST)) {
        $sql = "INSERT INTO person (name, surname, birth_day, birth_place, birth_country) VALUES (?,?,?,?,?)";
        $pdo->prepare($sql)
        ->execute([$_POST["name"], $_POST["surname"], $_POST["birth_day"], $_POST["birth_place"], $_POST["birth_country"]]);
    }
} 
catch (PDOException $err) {
    echo $err->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="#" method="post">
        <label for="inputName">Name:</label>
        <input type="text" name="name" id="inputName" required>

        <label for="inputSurname">Surname:</label>
        <input type="text" name="surname" id="inputSurname" required>

        <label for="inputBirthDay">Birth Day:</label>
        <input type="date" name="birth_day" id="inputBirthDay" required>

        <label for="inputBirthPlace">Birth Place:</label>
        <input type="text" name="birth_place" id="inputBirthPlace" required>

        <label for="inputBirthCountry">Birth Country:</label>
        <input type="text" name="birth_country" id="inputBirthCountry" required>

        <button type="submit">Post</button>
    </form>
</body>
</html>