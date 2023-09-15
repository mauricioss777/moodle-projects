<?php

require_once('../../config.php');

require_login();

unset( $_SESSION['local_pages_messages'] );

if(isset($_SERVER['HTTP_REFERER'])) {
    redirect($_SERVER['HTTP_REFERER']);
}else{
    redirect($CFG->wwwroot);
}
