<?php

// Developed by: Mauricio S. Silva
// Mail: mss@univates.br
//
// Official documentation: https://docs.moodle.org/dev/version.php


$plugin->version = 2021061601;
$plugin->requires = 2016052301; // Moodle 3.1.1 is required.
$plugin->component = 'mod_alfacertificados';
$plugin->maturity = MATURITY_ALPHA;

$plugin->dependencies = array(
    'local_alfa' => 2020102603
);
