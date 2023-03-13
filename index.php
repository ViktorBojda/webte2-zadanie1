<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once('vendor/autoload.php');
require_once('config.php');

$client = new Google\Client();
$client->setAuthConfig('client_secret.json');

$redirect_uri = "https://site60.webte.fei.stuba.sk/webte2-zadanie1/redirect.php";
$client->setRedirectUri($redirect_uri);

$client->addScope("email");
$client->addScope("profile");

$auth_url = $client->createAuthUrl();

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT
                person.id,
                person.name,
                person.surname,
                game.year,
                CONCAT(game.city, ', ', game.country) AS location,
                game.type,
                placement.discipline
            FROM
                person
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
    <title>Prehľad medailistov</title>
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
                        <?php
                        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
                            echo '
                                <span class="align-self-center px-3 text-white">Vitaj ' . $_SESSION['name'] . '</span>
                                <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#adb5bd" class="bi bi-person-circle" viewBox="0 0 16 16">
                                        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                        <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                                    </svg>
                                </a>

                                <div class="dropdown">
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#">Action</a></li>
                                        <li><a class="dropdown-item" href="#">Another action</a></li>
                                        <li><a class="dropdown-item" href="logout.php">Odhlásiť sa</a></li>
                                    </ul>
                                </div>';
                        }
                        else {
                            echo '
                                <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#adb5bd" class="bi bi-person-circle" viewBox="0 0 16 16">
                                        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                        <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                                    </svg>
                                </a>

                                <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="border-bottom">
                                                    <h1 class="modal-title fs-5" id="loginModalLabel">Prihlásenie</h1>
                                                </div>
                                                <div>
                                                    <h2 class="fs-6">Prihlásiť cez</h2>
                                                    <a href="' . filter_var($auth_url, FILTER_SANITIZE_URL) . '">
                                                        <img class="mx-auto d-block" src="images/google-icon.png" alt="Prihlásenie cez Google" width="32" height="32">
                                                    </a>
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                </div>';
                        } 
                        ?>
                    </div>
                </div>
            </nav>
            <div class="collapse" id="nav-toggle">
                <div class="row dark-blue-color mx-0">
                    <a class="col-12 col-md-6 py-3 nav-button-active d-flex justify-content-center" href="#">Prehľad medailistov</a>
                    <a class="col-12 col-md-6 py-3 d-flex justify-content-center" href="./top-10.php">Top 10</a>
                </div>
            </div>
        </div>

        <div class="page-content p-3">
            <h2 class="pb-3">Slovenskí olympijskí medailisti</h2>

            <div class="table-responsive">
                <table id="table-winners" class="table">
                    <thead>
                        <tr>
                            <td>ID</td>
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
                                    <td>" . $result["id"] . "</td>
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
        </div>
        
    </div>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap5.min.js"></script>
    <script src="./js/index.js"></script>
</body>

</html>