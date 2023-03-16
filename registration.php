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

function checkGmail($email) {
    if (!preg_match('/^[\w.+\-]+@gmail\.com$/', trim($email)))
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


if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action']) && $_POST['action'] == 'user_create') {
    $alert_msg=array(
        "message"=>"",
        "class"=>"danger"
    );

    $login = $_POST['login'];
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $fullName = $_POST["first_name"] . ' ' . $_POST["last_name"];

    if (checkEmpty($login) === true)
        $alert_msg['message'] .= "<p>Prihlasovacie meno nesmie byť prázdne.</p>";
    elseif (checkLength($login, 6, 32) === false)
        $alert_msg['message'] .= "<p>Prihlasovacie meno musí mať minimálne 6 a maximálne 32 znakov.</p>";
    elseif (checkUsername($login) === false)
        $alert_msg['message'] .= "<p>Prihlasovacie meno môže obsahovať iba veľké, malé pismená, číslice a podtržník.</p>";

    if (checkEmpty($email) === true)
        $alert_msg['message'] .= "<p>Email nesmie byť prázdny.</p>";
    elseif (checkEmail($email) === false)
        $alert_msg['message'] .= "<p>Nesprávny formát emailu.</p>";

    if (checkGmail($_POST['email']))
        $alert_msg['message'] .= "<p>Prihláste sa pomocou účtu Google.</p>";

    if (userExists($pdo, $login, $email) === true)
        $alert_msg['message'] .= "<p>Používateľ s týmto prihlasovacím menom alebo emailom už existuje.</p>";

    if (checkEmpty($_POST['password']) === true)
        $alert_msg['message'] .= "<p>Heslo nesmie byť prázdne.</p>";
    elseif (checkLength($_POST['password'], 6, 512) === false)
        $alert_msg['message'] .= "<p>Heslo musí mať minimálne 6 a maximálne 512 znakov.</p>";

    if (checkEmpty($_POST['first_name']) === true)
        $alert_msg['message'] .= "<p>Meno nesmie byť prázdne.</p>";

    if (checkEmpty($_POST['last_name']) === true)
        $alert_msg['message'] .= "<p>Priezvisko nesmie byť prázdne.</p>";

    if (checkLength($fullName, 2, 128) === false)
        $alert_msg['message'] .= "<p>Kombinácia mena a priezviska nesmie presiahnuť 128 znakov.</p>";
    
    if (empty($alert_msg['message'])) {  
        $sql = "INSERT INTO user (full_name, email, login, password, 2fa_code) VALUES (?,?,?,?,?)";

        $hashed_password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

        $g2fa = new PHPGangsta_GoogleAuthenticator();
        $user_secret = $g2fa->createSecret();
        $code_url = $g2fa->getQRCodeGoogleUrl("Olympic Games", $user_secret);

        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$fullName, $email, $login, $hashed_password, $user_secret]))
            $qr_code = $code_url;
        else
            $alert_msg['message'] .= "<p>Nastala chyba. Prosím zopakujte registráciu.</p>";

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
                        if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || 
                            (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true))
                            exit(header('Location: index.php'));
                        else
                            require_once('login_modal.php'); 
                        ?>
                    </div>
                </div>
            </nav>
            <div class="collapse" id="nav-toggle">
                <div class="row dark-blue-color mx-0">
                    <a class="col-12 col-md-6 py-3 d-flex justify-content-center" href="./index.php">Prehľad medailistov</a>
                    <a class="col-12 col-md-6 py-3 d-flex justify-content-center" href="./top_10.php">Top 10</a>
                </div>
            </div>
        </div>

        <?php require_once('alert_msg.php') ?>

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
                            <input type="text" class="form-control" name="login" id="reg-login" minlength="6" maxlength="32" required>
                        </div>
    
                        <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                            <label for="reg-password" class="form-label">Heslo</label><br>
                            <input type="password" class="form-control" name="password" id="reg-password" minlength="6" maxlength="512" required>
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
                            <button type="submit" name="action" value="user_create" class="btn btn-success btn-lg">Vytvoriť účet</button>
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