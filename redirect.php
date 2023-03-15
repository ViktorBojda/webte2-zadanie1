<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once 'vendor/autoload.php';
require_once 'config.php';

$client = new Google\Client();
$client->setAuthConfig('client_secret.json');

$redirect_uri = "https://site60.webte.fei.stuba.sk/webte2-zadanie1/redirect.php";
$client->setRedirectUri($redirect_uri);

$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);
    
    $oauth = new Google\Service\Oauth2($client);
    $account_info = $oauth->userinfo->get();

    $g_fullname = $account_info->name;
    $g_id = $account_info->id;
    $g_email = $account_info->email;
    $g_name = $account_info->givenName;
    $g_surname = $account_info->familyName;

    $sql = "SELECT id FROM user WHERE email = ? AND external_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$g_email, $g_id]);

    if ($stmt->rowCount() == 1)
        $u_id = $stmt->fetchColumn();
    else {
        $sql = "INSERT INTO user (full_name, email, external_type, external_id) VALUES (?,?,?,?)";
        $stmt = $pdo->prepare($sql);

        if (!$stmt->execute([$g_fullname, $g_email, 'Google', $g_id])) {
            echo "Nastala chyba. Prosím zopakujte prihlásenie.";
            exit;
        }

        $sql = "SELECT id FROM user WHERE email = ? AND external_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$g_email, $g_id]);
        $u_id = $stmt->fetchColumn();
    }

    unset($stmt);
    unset($pdo);

    $_SESSION['access_token'] = $token['access_token'];
    $_SESSION['email'] = $g_email;
    $_SESSION['id'] = $u_id;
    $_SESSION['full_name'] = $g_fullname;
    $_SESSION['name'] = $g_name;
    $_SESSION['surname'] = $g_surname;
}

exit(header('Location: index.php'));
?>