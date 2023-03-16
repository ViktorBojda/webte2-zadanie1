<?php 
if (!empty($alert_msg['message']))
    echo "
    <div class='alert alert-{$alert_msg['class']} alert-dismissible fade show' role='alert'>
        {$alert_msg['message']}
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
?>