<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once('restricted.php');
require_once('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $err_msg = '';
    $post = array_map('null_empty', $_POST);
    $sql = "SELECT * FROM person WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$post['id']])) {
        if ($stmt->rowCount() == 1) {
            $sql = "UPDATE person SET name = ?, surname = ?, birth_day = ?, birth_place = ?, birth_country = ?, death_day = ?, death_place = ?, death_country = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$post['name'], $post['surname'], $post['birth_day'], $post['birth_place'], $post['birth_country'], $post['death_day'], $post['death_place'], $post['death_country'], $post['id']]);
        }
        else
            $err_msg = "Nebol nájdený žiadny športovec s hľadaným ID.";
    }
    else
        $err_msg = "Nastala chyba. Zopakujte operáciu.";
}

$sql = "SELECT * FROM person";
$athletes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function null_empty($var) {
  return ($var === '') ? NULL : $var;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM person WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    if ($stmt->rowCount() == 1)
        $searched_athlete = $stmt->fetch();
    else
        $err_msg = "Nebol nájdený žiadny športovec s hľadaným ID.";
}

unset($stmt);
unset($pdo);
?>

<!DOCTYPE html>
<html lang="sk">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pridaj športovca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/dataTables.bootstrap5.min.css">
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
                        <?php require_once('logged_in_navbar.php') ?>
                    </div>
                </div>
            </nav>
            <div class="collapse" id="nav-toggle">
                <div class="row dark-blue-color mx-0">
                    <a class="col-12 col-md-6 py-3 nav-button-active d-flex justify-content-center" href="index.php">Prehľad medailistov</a>
                    <a class="col-12 col-md-6 py-3 d-flex justify-content-center" href="top_10.php">Top 10</a>
                </div>
            </div>
        </div>

        <?php 
        if (isset($err_msg)) {
            if (empty($err_msg))
                echo '
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Športovec ' . $post['name'] . ' ' . $post['surname'] . ' bol upravený.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            else
                echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ' . $err_msg . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }
        ?>

        <div class="page-content p-3">
            <h2 class="pb-3">Uprav športovca</h2>

            <form action="" method="get">
                <div class="row mb-3">
                    <div class="col-6">
                        <select name="id" class="form-select" required>
                            <?php
                            if (isset($searched_athlete)) {
                                echo '<option disabled>Nájdi športovca</option>';
                                foreach ($athletes as $athlete) {
                                    if ($athlete['id'] == $searched_athlete['id'])
                                        echo '<option selected value="' . $athlete['id'] . '">' . $athlete['name'] . ' ' . $athlete['surname'] . '</option>';
                                    else
                                        echo '<option value="' . $athlete['id'] . '">' . $athlete['name'] . ' ' . $athlete['surname'] . '</option>';
                                }
                            }
                            else {
                                echo '<option selected disabled>Nájdi športovca</option>';
                                foreach ($athletes as $athlete)
                                    echo '<option value="' . $athlete['id'] . '">' . $athlete['name'] . ' ' . $athlete['surname'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-6 d-grid">
                        <button class="btn btn-primary" type="submit">Vyhľadaj športovca</button>
                    </div>
                </div>
            </form>
            
            <?php 
            if (isset($searched_athlete)) {
                echo '
                <form action="" method="post">
                    <input type="hidden" name="id" value="' . $searched_athlete['id'] . '">

                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                            <label for="person-name" class="form-label">Meno</label><br>
                            <input type="text" class="form-control" value="' . $searched_athlete['name'] . '" name="name" id="person-name" required>
                        </div>

                        <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                            <label for="person-surname" class="form-label">Priezvisko</label><br>
                            <input type="text" class="form-control" value="' . $searched_athlete['surname'] . '" name="surname" id="person-surname" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <label for="person-birth-day" class="form-label">Dátum narodenia</label><br>
                            <input type="date" class="form-control" value="' . $searched_athlete['birth_day'] . '" name="birth_day" id="person-birth-day" required>
                        </div>

                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <label for="person-birth-place" class="form-label">Miesto narodenia</label><br>
                            <input type="text" class="form-control" value="' . $searched_athlete['birth_place'] . '" name="birth_place" id="person-birth-place" required>
                        </div>

                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <label for="person-birth-country" class="form-label">Krajina narodenia</label><br>
                            <input type="text" class="form-control" value="' . $searched_athlete['birth_country'] . '" name="birth_country" id="person-birth-country" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <label for="person-death-day" class="form-label">Dátum úmrtia</label><br>
                            <input type="date" class="form-control" value="' . $searched_athlete['death_day'] . '" name="death_day" id="person-death-day">
                        </div>

                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <label for="person-death-place" class="form-label">Miesto úmrtia</label><br>
                            <input type="text" class="form-control" value="' . $searched_athlete['death_place'] . '" name="death_place" id="person-death-place">
                        </div>

                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <label for="person-death-country" class="form-label">Krajina úmrtia</label><br>
                            <input type="text" class="form-control" value="' . $searched_athlete['death_country'] . '" name="death_country" id="person-death-country">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-success btn-lg">Upraviť</button>
                        </div>
                    </div>
                </form>';
            }
            ?>
        </div>
        
    </div>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>