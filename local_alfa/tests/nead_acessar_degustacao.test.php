<?php

require_once('../../../config.php');

$key = $DB->get_record('config_plugins', ['plugin' => 'auth_ssounivates', 'name' => 'token'])->value;

$randomsalt = uniqid(mt_rand(), true);
$system     = '';
$username   = 'usuario_degustacao';
$wantsurl   = 'https://www.univates.br/virtual/course/view.php?id=33385';

switch ($_GET['ambiente']) {
    case 'ti':
        $wantsurl   = 'https://www.univates.br/virtual/course/view.php?id=33385';
        break;
    case 'licenciatura':
        $wantsurl   = 'https://www.univates.br/virtual/course/view.php?id=33458';
        break;
    case 'gestao':
        $wantsurl   = 'https://www.univates.br/virtual/course/view.php?id=33347';
        break;
}

$maxlifetime= time()+60;//1min

//sempre nesta ordem
$ssoinfo = $randomsalt.','.$system.','.$username.','.$wantsurl.','.$maxlifetime;
$ssoinfoencrypted = encrypt($ssoinfo,$key);
header('Location: https://www.univates.br/virtual/auth/ssounivates/jump.php?ssounivates='.$ssoinfoencrypted);
exit;

/*
 * Criptografa o objeto
 */
function encrypt( $text,  $key) {
  
  $iv      = random_bytes(16); // iv size for aes-256-cbc
  $keys    = hash_pbkdf2('sha512', $key, $iv, 80000, 64, true);
  $encKey  = mb_substr($keys, 0, 64, '8bit');
  $hmacKey = mb_substr($keys, 64, null, '8bit');

  $ciphertext = openssl_encrypt($text, 'aes-192-cfb8', $encKey,
    OPENSSL_RAW_DATA, $iv
  );
  
  $hmac = hash_hmac('sha256', $iv . $ciphertext, $hmacKey);
  return urlencode(base64_encode($hmac . $ciphertext . $iv));
}
