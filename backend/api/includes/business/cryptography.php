<?php

define('CIPHER_IV', hex2bin(md5('Z6TIc6A2JkdYT0GF')));
define('CIPHER_KEY', 'D76a73P&!*7486D6');
 
 
function decrypt($code) {
 
  $key = CIPHER_KEY;

  $td = mcrypt_module_open("rijndael-128", "", "cbc", CIPHER_IV);
 
  mcrypt_generic_init($td, $key, CIPHER_IV);
  $decrypted = mdecrypt_generic($td, $code);

  mcrypt_generic_deinit($td);
  mcrypt_module_close($td);
 
  return rtrim($decrypted);
}
 
function encrypt($str) {

  $key = CIPHER_KEY;
  $td = mcrypt_module_open("rijndael-128", "", "cbc", CIPHER_IV);
 
  mcrypt_generic_init($td, $key, CIPHER_IV);
  $encrypted = mcrypt_generic($td, $str);
 
  mcrypt_generic_deinit($td);
  mcrypt_module_close($td);
 
  return $encrypted;
}


?>