<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once('config.php');
require_once('login.php');

function checkEmpty($field) {
    if (empty(trim($field)))
        return true;
    return false;
}

function checkLength($field, $min, $max) {
    $string = trim($field);
    $length = strlen($string);
    if ($length < $min || $length > $max)
        return false;
    return true;
}

function checkUsername($username) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($username)))
        return false;
    return true;
}

function checkEmail($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL))
        return true;
    return false;
}

function userExists($db, $login, $email) {
    $exists = false;

    $param_login = trim($login);
    $param_email = trim($email);

    $sql = "SELECT id FROM user WHERE login = ? OR email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$param_login, $param_email]);

    if ($stmt->rowCount() == 1)
        $exists = true;

    unset($stmt);

    return $exists;
}


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $err_msg = "";

    $login = $_POST['login'];
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $fullName = $_POST["first_name"] . ' ' . $_POST["last_name"];

    if (checkEmpty($login) === true)
        $err_msg .= "<p>Prihlasovacie meno nesmie byť prázdne.</p>";
    elseif (checkLength($login, 6, 32) === false)
        $err_msg .= "<p>Prihlasovacie meno musí mať minimálne 6 a maximálne 32 znakov.</p>";
    elseif (checkUsername($login) === false)
        $err_msg .= "<p>Prihlasovacie meno môže obsahovať iba veľké, malé pismená, číslice a podtržník.</p>";

    if (checkEmpty($email) === true)
        $err_msg .= "<p>Email nesmie byť prázdny.</p>";
    elseif (checkEmail($email) === false)
        $err_msg .= "<p>Nesprávny formát emailu.</p>";

    if (userExists($pdo, $login, $email) === true)
        $err_msg .= "Používateľ s týmto prihlasovacím menom alebo emailom už existuje.</p>";

    if (checkEmpty($_POST['password']) === true)
        $err_msg .= "<p>Heslo nesmie byť prázdne.</p>";
    elseif (checkLength($_POST['password'], 6, 512) === false)
        $err_msg .= "<p>Heslo musí mať minimálne 6 a maximálne 512 znakov.</p>";

    if (checkEmpty($_POST['first_name']) === true)
        $err_msg .= "<p>Meno nesmie byť prázdne.</p>";

    if (checkEmpty($_POST['last_name']) === true)
        $err_msg .= "<p>Priezvisko nesmie byť prázdne.</p>";

    if (checkLength($fullName, 2, 128) === false)
        $err_msg .= "<p>Kombinácia mena a priezviska nesmie presiahnuť 128 znakov.</p>";
    
    if (empty($err_msg)) {  
        $sql = "INSERT INTO user (full_name, email, login, password, 2fa_code) VALUES (?,?,?,?,?)";

        $hashed_password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

        $g2fa = new PHPGangsta_GoogleAuthenticator();
        $user_secret = $g2fa->createSecret();
        $code_url = $g2fa->getQRCodeGoogleUrl("Olympic Games", $user_secret);

        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$fullName, $email, $login, $hashed_password, $user_secret]))
            $qr_code = $code_url;
        else
            echo "Nastala chyba. Prosím zopakujte registráciu.";

        unset($stmt);
    }
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="sk">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrácia</title>
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
                        <?php
                        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
                            header('Location: index.php');
                            exit;
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
                                                <h1 class="modal-title fs-5 mb-3" id="loginModalLabel">Prihlásenie</h1>

                                                <form action="" method="post">
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <label for="login" class="form-label">Prihlasovacie meno alebo email</label><br>
                                                            <input type="text" class="form-control" name="identifier" id="identifier" required>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <label for="password" class="form-label">Heslo</label><br>
                                                            <input type="password" class="form-control" name="password" id="password" required>
                                                        </div>
                                                    </div>
                                    
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <label for="email" class="form-label">2FA Kód</label><br>
                                                            <input type="number" class="form-control" name="2fa_code" id="2fa_code" required>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-12 col-sm-6 mb-3 mb-sm-0 d-grid">
                                                            <button type="submit" class="btn btn-success btn-lg">Prihlásiť sa</button>
                                                        </div>

                                                        <div class="col-12 col-sm-6 d-grid">
                                                            <a href="registration.php" class="btn btn-primary btn-lg d-flex justify-content-center align-items-center" role="button">Vytvoriť nový účet</a>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                            <div>
                                                <h2 class="fs-6 mt-2">Prihlásiť sa cez</h2>
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
                    <a class="col-12 col-md-6 py-3 nav-button-active d-flex justify-content-center" href="./index.php">Prehľad medailistov</a>
                    <a class="col-12 col-md-6 py-3 d-flex justify-content-center" href="./top_10.php">Top 10</a>
                </div>
            </div>
        </div>

        <div class="page-content p-3">
            <?php 
            if (isset($qr_code)) {
                echo '
                <h2 class="pb-3">Dvojstupňové Overenie</h2>

                <p>1. Nainštalujte si Google Authenticator aplikáciu na váš mobilný telefón z Play Store / App Store</p>
                <p>2. Otvorte Google Authenticator a naskenujte nasledujúci QR kód alebo zadajte tajný kľúč</p>
                <div class="row py-3">
                    <div class="col-12 col-sm-6 mb-5 mb-sm-3 d-flex flex-column align-items-center">
                        <p>QR Kód</p>
                        <img class="qr-code" src="' . $qr_code . '" alt="QR kód pre Google Authenticator">
                    </div>
                    <div class="col-12 col-sm-6 mb-3 d-flex flex-column justify-content-center align-items-center">
                        <p>Tajný kľúč (zobraz kurzorom)</p>
                        <p class="secret-code p-2">' . $user_secret . '</p>
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-12 d-grid">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn btn-success" role="button">Prihlásiť sa</a>
                    </div>
                </div>';
            }
            else {
                echo '
                <h2 class="pb-3">Registrácia</h2>

                <form action="" method="post">
                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                            <label for="reg-login" class="form-label">Prihlasovacie meno</label><br>
                            <input type="text" class="form-control" name="login" id="reg-login" required>
                        </div>
    
                        <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                            <label for="reg-password" class="form-label">Heslo</label><br>
                            <input type="password" class="form-control" name="password" id="reg-password" required>
                        </div>
                    </div>
    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="reg-email" class="form-label">Emailová adresa</label><br>
                            <input type="email" class="form-control" name="email" id="reg-email" required>
                        </div>
                    </div>
    
                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                            <label for="reg-firstName" class="form-label">Meno</label><br>
                            <input type="text" class="form-control" name="first_name" id="reg-firstName" required>
                        </div>
    
                        <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                            <label for="reg-lastName" class="form-label">Priezvisko</label><br>
                            <input type="text" class="form-control" name="last_name" id="reg-lastName" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-success btn-lg">Vytvoriť účet</button>
                        </div>
                    </div>
                    ' . (!empty($err_msg) ? $err_msg : "") . '
                </form>';
            }
            ?>
        </div>        
    </div>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>