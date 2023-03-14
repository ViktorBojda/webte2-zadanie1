<?php
if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || 
    (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)) {
    echo '
        <span class="align-self-center px-3 text-white">Vitaj ' . $_SESSION['full_name'] . '</span>
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