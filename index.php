<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('config.php');

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT  person.name,
                    person.surname,
                    game.year,
                    CONCAT(game.city, ', ', game.country) AS location,
                    game.type,
                    placement.discipline
            FROM person
            LEFT JOIN placement ON person.id = placement.person_id
            INNER JOIN game ON placement.game_id = game.id";

    $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $err) {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css"> -->
    <!-- <link href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.css"> -->
    <!-- <link href="https://cdn.datatables.net/1.13.3/css/dataTables.bootstrap5.min.css"> -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/dataTables.bootstrap5.min.css">
</head>

<body>
    <div class="container">
        <h1>Naši olympionici</h1>
        <table id="table-winners" class="table">
            <thead>
                <tr>
                    <td>Meno</td>
                    <td>Priezvisko</td>
                    <td>Rok</td>
                    <td>Miesto</td>
                    <td>Typ</td>
                    <td>Disciplína</td>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($results as $result) {
                    echo "<tr>
                            <td>" . $result["name"] . "</td>
                            <td>" . $result["surname"] . "</td>
                            <td>" . $result["year"] . "</td>
                            <td>" . $result["location"] . "</td>
                            <td>" . $result["type"] . "</td>
                            <td>" . $result["discipline"] . "</td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap5.min.js"></script>
    <script src="./js/index.js"></script>
</body>

</html>