<?php

  $p_plain = readline("Enter a password: ");
  echo "Password: " . $p_plain . "\n";

  $p_cost = 12;

  $p_bcrypt = password_hash($p_plain, PASSWORD_BCRYPT, $options = [ 'cost' => $p_cost ]);
  echo "BCRYPT: " . $p_bcrypt . "\n";

?>