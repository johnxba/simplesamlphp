#!/usr/bin/env php
<?php

declare(strict_types=1);

/*
  Interactive script to generate password hashes.

  generally recommended algorithm order:
  PASSWORD_ARGON2ID - requires php 7.3.0 or higher and ARGON2 supported compile
  PASSWORD_ARGON2I  - requires php 7.2.0 or higher and ARGON2 supported compile
  PASSWORD_BCRYPT   - php default at this time (July 2023)

  OWASP recommended minimum for ARGON2:
  46MB, t=1, p=1
  19MB, t=2, p=1

  We use 64MiB, t=4, p=1.

  references:
  https://www.rfc-editor.org/rfc/rfc9106.html#name-recommendations
  https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#argon2id
*/

// This is the base directory of the SimpleSAMLphp installation
$baseDir = dirname(__FILE__, 2);

// Add library autoloader
require_once($baseDir . '/src/_autoload.php');

//  parameters for ARGON2, ignored for BCRYPT
//  a salt is automatically generated by password_hash()
$options = [
  'memory_cost' => '65536', // 64MiB
  'time_cost' => 4,
  'threads' => 1,
];

// automatically select preferred supported algorithm
if (defined('PASSWORD_ARGON2ID')) { // supported since php 7.3.0
  $algo = PASSWORD_ARGON2ID;
}
else
{ if (defined('PASSWORD_ARGON2I')) { // supported since php 7.2.0
    $algo = PASSWORD_ARGON2I;
  }
  else
  { if (defined('PASSWORD_BCRYPT')) { // supported since php 5.5.0
      $algo = PASSWORD_BCRYPT;
    }
    else {
      "php 5.5.0 or higher is required\n";
      exit(1);
    }
  }
}

//  get password and remove line endings \n or \r\n
do {
  echo "\nEnter password: ";
  $password = fgets(STDIN);

  // php adds newline when pressing enter key
  $length = strlen($password);
  if ($length>0) {
    if ($password[$length-1]=="\n") {
      $password = substr($password, 0, -1); // removes trailing \n line ending
      if ($length>1 && $password[$length-2]=="\r") {
        $password = substr($password, 0, -1); // trailing \r\n line ending removed
      }
      $length = strlen($password);
    }
  }
} while ($length==0); // retry if password is empty

$passwordHash = password_hash($password, $algo, $options); // ARGON2 options are ignored if algo is bcrypt

echo "\nPassword hash:\n" . $passwordHash . "\n\n";

?>
