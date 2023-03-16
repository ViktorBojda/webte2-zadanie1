<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once('restricted.php');
require_once('config.php');

$sql = "SELECT
            logged_at,
            source,
            action,
            table_name,
            record_id
        FROM
            login_session
        LEFT JOIN user_action ON login_session.id = user_action.login_session_id
        WHERE login_session.email = ?
        ORDER BY logged_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['email']]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

unset($pdo);
?>

<!DOCTYPE html>
<html lang="sk">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>História používateľa</title>
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
                    <a class="col-12 col-md-6 py-3 d-flex justify-content-center" href="index.php">Prehľad medailistov</a>
                    <a class="col-12 col-md-6 py-3 d-flex justify-content-center" href="top_10.php">Top 10</a>
                </div>
            </div>
        </div>

        <?php 
        if (isset($err_msg)) {
            if (empty($err_msg))
                echo '
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Športovec ' . $post['name'] . ' ' . $post['surname'] . ' bol pridaný.
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
            <h2 class="pb-3">História používateľa</h2>

            <div class="table-responsive">
                <table id="table-user-history" class="table">
                    <thead>
                        <tr>
                            <td>Čas prihlásenia</td>
                            <td>Spôsob</td>
                            <td>Aktivita</td>
                            <td>Tabuľka databázy</td>
                            <td>ID záznamu</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($activities as $activity) {
                            echo "<tr>
                                    <td>" . $activity["logged_at"] . "</td>
                                    <td>" . $activity["source"] . "</td>
                                    <td>" . $activity["action"] . "</td>
                                    <td>" . $activity["table_name"] . "</td>
                                    <td>" . $activity["record_id"] . "</td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>


        </div>
        
    </div>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>