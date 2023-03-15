<?php
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
            <li><a class="dropdown-item" href="add_person.php">Pridať športovca</a></li>
            <li><a class="dropdown-item" href="edit_person.php">Upraviť športovca</a></li>
            <li><a class="dropdown-item" href="logout.php">Odhlásiť sa</a></li>
        </ul>
    </div>'; 
?>