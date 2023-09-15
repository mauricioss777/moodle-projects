<?php

error_log( print_r($_POST, true) );
error_log( print_r(json_decode(file_get_contents('php://input'), true), true) );
