<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once('restricted.php');
require_once('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $err_msg = '';

    if (isset($_POST['action']) && $_POST['action'] == 'person_edit') {
        $post = array_map('null_empty', $_POST);
        $sql = "SELECT * FROM person WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$post['person_id']])) {
            if ($stmt->rowCount() == 1) {
                $sql = "UPDATE person SET name = ?, surname = ?, birth_day = ?, birth_place = ?, birth_country = ?, death_day = ?, death_place = ?, death_country = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$post['name'], $post['surname'], $post['birth_day'], $post['birth_place'], $post['birth_country'], $post['death_day'], $post['death_place'], $post['death_country'], $post['person_id']]);
            }
            else
                $err_msg = "Nebol nájdený žiadny športovec s hľadaným ID.";
        }
        else
            $err_msg = "Nastala chyba. Zopakujte operáciu.";
    }

    if (isset($_POST['action']) && $_POST['action'] == 'placement_edit') {
        $sql = "UPDATE placement SET game_id = ?, discipline = ?, placing = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['game_id'], $_POST['discipline'], $_POST['placing'], $_POST['placement_id']]);
        exit(header('Location: ' . removeUrlParam($_SERVER['REQUEST_URI'], 'placement_id')));
    }

    if (isset($_POST['action']) && $_POST['action'] == 'placement_delete') {
        $sql = "DELETE FROM placement WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['placement_id']]);
    }

    if (isset($_POST['action']) && $_POST['action'] == 'placement_add') {
        $sql = "INSERT INTO placement (person_id, game_id, discipline, placing) VALUES(?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['person_id'], $_POST['game_id'], $_POST['discipline'], $_POST['placing']]);
    }
}

$sql = "SELECT * FROM person";
$athletes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function null_empty($var) {
  return ($var === '') ? NULL : $var;
}

function removeUrlParam($url, $param) {
    $url = preg_replace('/(&|\?)'.preg_quote($param).'=[^&]*$/', '', $url);
    $url = preg_replace('/(&|\?)'.preg_quote($param).'=[^&]*&/', '$1', $url);
    return $url;
}

if (isset($_GET['person_id'])) {
    $person_id = $_GET['person_id'];

    $sql = "SELECT * FROM person WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$person_id]);
    if ($stmt->rowCount() == 1) {
        $searched_athlete = $stmt->fetch();

        $sql = "SELECT
                    placement.*,
                    CONCAT(game.city, ', ', game.country) AS location,
                    game.year,
                    game.type
                FROM
                    placement
                JOIN game ON placement.game_id = game.id
                WHERE
                    placement.person_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$person_id]);
        $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else
        $err_msg = "Nebol nájdený žiadny športovec s hľadaným ID.";

    $sql = "SELECT
        game.id,
        CONCAT(game.city, ', ', game.country, ' (', game.year, ')') AS location
    FROM
        game";
    $games = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['placement_id'])) {
    $placement_id = $_GET['placement_id'];

    $sql = "SELECT * FROM placement WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placement_id]);
    if ($stmt->rowCount() == 1)
        $searched_placement = $stmt->fetch();
    else
        $err_msg = "Nebolo nájdené žiadne umiestnenie s hľadaným ID.";
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
            if (empty($err_msg)){}
                // echo '
                // <div class="alert alert-success alert-dismissible fade show" role="alert">
                //     Športovec ' . $post['name'] . ' ' . $post['surname'] . ' bol upravený.
                //     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                // </div>';
            else
                echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ' . $err_msg . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }
        ?>

        <div class="page-content p-3">
            <h2 class="pb-3">Upravenie športovca a jeho umiestnení</h2>

            <form action="" method="get">
                <div class="row mb-3">
                    <div class="col-6">
                        <select name="person_id" class="form-select" required>
                            <?php
                            if (isset($searched_athlete)) {
                                echo '<option disabled value="">Vyber športovca</option>';
                                foreach ($athletes as $athlete) {
                                    if ($athlete['id'] == $searched_athlete['id'])
                                        echo '<option selected value="' . $athlete['id'] . '">' . $athlete['name'] . ' ' . $athlete['surname'] . '</option>';
                                    else
                                        echo '<option value="' . $athlete['id'] . '">' . $athlete['name'] . ' ' . $athlete['surname'] . '</option>';
                                }
                            }
                            else {
                                echo '<option selected disabled value="">Vyber športovca</option>';
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
                    <input type="hidden" name="person_id" value="' . $searched_athlete['id'] . '">

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
                    
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-12 d-grid">
                            <button type="submit" name="action" value="person_edit" class="btn btn-success btn-lg">Upraviť športovca</button>
                        </div>
                    </div>
                </form>

                <h3 class="pb-3">Umiestnenia</h3>

                <div class="table-responsive mb-3">
                    <table id="table-placements" class="table">
                        <thead>
                            <tr>
                                <td>Miesto</td>
                                <td>Rok</td>
                                <td>Typ</td>
                                <td>Disciplína</td>
                                <td>Umiestnenie</td>
                                <td>Akcia</td>
                            </tr>
                        </thead>
                        <tbody>';
                        foreach ($placements as $placement) {
                            if (isset($searched_placement) && $searched_placement['id'] == $placement['id']) {
                                echo "
                                <tr>
                                    <form action='' method='post'>
                                        <td>
                                            <select name='game_id' class='form-select' required>";
                                            foreach ($games as $game) {
                                                if ($game['id'] == $searched_placement['game_id'])
                                                    echo '<option selected value="' . $game['id'] . '">' . $game['location'] . '</option>';
                                                else
                                                    echo '<option value="' . $game['id'] . '">' . $game['location'] . '</option>';
                                            }                                            
                                    echo "  </select>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td><input type='text' class='form-control' value='{$searched_placement['discipline']}' name='discipline' required></td>
                                        <td><input type='number' class='form-control' value='{$searched_placement['placing']}' name='placing' required></td>
                                        <td>
                                            <input type='hidden' name='placement_id' value={$placement['id']}>
                                            <button class='btn btn-success' name='action' value='placement_edit' type='submit'>Upraviť</button>
                                            <a href='{$_SERVER['PHP_SELF']}?person_id={$searched_athlete['id']}' class='btn btn-primary me-3' role='button'>Zrušiť</a>
                                            <button class='btn btn-danger' name='action' value='placement_delete' type='submit'>Vymazať</button>
                                        </td>
                                    </form>
                                </tr>";  
                            }                              
                            else
                                echo "
                                <tr>
                                    <td>{$placement['location']}</td>
                                    <td>{$placement['year']}</td>
                                    <td>{$placement['type']}</td>
                                    <td>{$placement['discipline']}</td>
                                    <td>{$placement['placing']}</td>
                                    <td>
                                        <form action='' method='get'>
                                            <input type='hidden' name='person_id' value={$searched_athlete['id']}>
                                            <input type='hidden' name='placement_id' value={$placement['id']}>
                                            <button class='btn btn-warning' type='submit'>Upraviť</button>
                                        </form>
                                        <form action='' method='post'>
                                            <input type='hidden' name='placement_id' value={$placement['id']}>
                                            <button class='btn btn-danger' name='action' value='placement_delete' type='submit'>Vymazať</button>
                                        </form>
                                    </td>
                                </tr>";
                        }
                echo '
                        </tbody>
                    </table>
                </div>

                <h3 class="pb-3">Pridanie umiestnenia</h3>

                <form action="" method="post">
                    <input type="hidden" name="person_id" value="' . $searched_athlete['id'] . '">

                    <div class="row mb-3">
                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <label for="placement-location" class="form-label">Miesto</label><br>
                            <select name="game_id" class="form-select" required>
                                <option disabled selected value="">Vyber Olympíjsku Hru</option>';
                                foreach ($games as $game) 
                                    echo '<option value="' . $game['id'] . '">' . $game['location'] . '</option>';
                    echo '  </select>
                        </div>

                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <label for="placement-discipline" class="form-label">Disciplína</label><br>
                            <input type="text" class="form-control" value="" name="discipline" id="placement-discipline" required>
                        </div>

                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                            <label for="placement-placing" class="form-label">Umiestnenie</label><br>
                            <input type="number" class="form-control" value="" name="placing" id="placement-placing" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-12 d-grid">
                            <button type="submit" name="action" value="placement_add" class="btn btn-success btn-lg">Pridať umiestnenie</button>
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