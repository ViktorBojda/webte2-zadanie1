<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once('config.php');
require_once('login.php');

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT
                CONCAT(person.name, ' ', person.surname) AS fullName,
                CONCAT(game.city, ', ', game.country) AS location,
                game.year,
                game.type,
                placement.discipline,
                placement.placing
            FROM
                person
            LEFT JOIN placement ON person.id = placement.person_id
            INNER JOIN game ON placement.game_id = game.id
            WHERE
                person_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET["id"]]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Detail olympionika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/basic.css">
</head>

<body>
    <div class="container-xl">
        <header>
            <h1 class="page-content text-center py-3 my-3">Bojda Olympic Games</h1>
        </header>

        <div class="page-content my-3">
            <nav class="navbar navbar-dark dark-blue-color">
                <div class="container-fluid">
                    <button class="navbar-toggler border-gray" type="button" data-bs-toggle="collapse" data-bs-target="#nav-toggle" 
                    aria-controls="nav-toggle" aria-expanded="false" aria-label="Zobraz menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="d-flex">
                        <?php require_once('login_modal.php')?>
                    </div>
                </div>
            </nav>
            <div class="collapse" id="nav-toggle">
                <div class="row dark-blue-color mx-0">
                    <a class="col-12 col-md-6 py-3 d-flex justify-content-center" href="index.php">Prehľad medailistov</a>
                    <a class="col-12 col-md-6 py-3 d-flex justify-content-center" href="top_10.php">Top 10</a>
                </div>
            </div>
        </div>

        <div class="page-content p-3">
            <h2 class="pb-3">
                <?php 
                    echo $results[0]["fullName"];
                ?>
            </h2>

            <div class="table-responsive">
                <table id="table-winners" class="table">
                    <thead>
                        <tr>
                            <td>Miesto</td>
                            <td>Rok</td>
                            <td>Typ</td>
                            <td>Disciplína</td>
                            <td>Umiestnenie</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($results as $result) {
                            echo "<tr>
                                    <td>" . $result["location"] . "</td>
                                    <td>" . $result["year"] . "</td>
                                    <td>" . $result["type"] . "</td>
                                    <td>" . $result["discipline"] . "</td>
                                    <td>" . $result["placing"] . "</td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>

</html>