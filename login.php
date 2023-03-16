<?php 
require_once('vendor/autoload.php');
require_once('PHPGangsta/GoogleAuthenticator.php');

if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || 
    (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true)){}
else {
    $client = new Google\Client();
    $client->setAuthConfig('client_secret.json');

    $redirect_uri = "https://site60.webte.fei.stuba.sk/webte2-zadanie1/redirect.php";
    $client->setRedirectUri($redirect_uri);

    $client->addScope("email");
    $client->addScope("profile");

    $auth_url = $client->createAuthUrl();

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'user_sign_in') {
        $sql = "SELECT id, full_name, login, email, password, 2fa_code, created_at FROM user WHERE login = ? OR email = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$_POST['identifier'], $_POST['identifier']])) {
            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch();
                $hashed_password = $row['password'];
    
                if (password_verify($_POST['password'], $hashed_password)) {
                    $g2fa = new PHPGangsta_GoogleAuthenticator();
    
                    if ($g2fa->verifyCode($row['2fa_code'], $_POST['2fa_code'], 1)) {
                        $_SESSION["logged_in"] = true;
                        $_SESSION["login"] = $row['login'];
                        $_SESSION["id"] = $row['id'];
                        $_SESSION["full_name"] = $row['full_name'];
                        $_SESSION["email"] = $row['email'];
                        $_SESSION["created_at"] = $row['created_at'];

                        $sql = "INSERT INTO login_session (email, login, source) VALUES (?,?,?)";
                        $stmt = $pdo->prepare($sql);
                        if ($stmt->execute([$_SESSION["email"], $_SESSION["login"], 'Native']))
                            $_SESSION['login_session_id'] = $pdo->lastInsertId();
                    }
                    else
                        $alert_msg=array(
                            "message"=>"<p>Neplatný kód 2FA.<p>",
                            "class"=>"danger"
                        );
                }
                else
                    $alert_msg=array(
                        "message"=>"<p>Nesprávne prihlasovacie údaje.<p>",
                        "class"=>"danger"
                    );
            }
            else
                $alert_msg=array(
                    "message"=>"<p>Nesprávne prihlasovacie údaje.</p>",
                    "class"=>"danger"
                );
        }
        else
            $alert_msg=array(
                "message"=>"<p>Nastala chyba. Zopakujte prihlásenie.<p>",
                "class"=>"danger"
            );
    }
}
?>