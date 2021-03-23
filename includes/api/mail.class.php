<?php

  class MailAPI {
  
    static function send_verification_email($conn, $sub_ref, $sub_row) {

      $msg = "Test email";
      $msg = wordwrap($msg, 70);

      $headers = array(
        'From' => 'admin@hutchie.scot',
        'Reply-To' => 'admin@hutchie.scot',
        'X-Mailer' => 'PHP/' . phpversion()
      );

      $accepted = mail("ben@hutchie.scot", "UIRS Test email", $msg, $headers);

      echo "Accepted: " . $accepted;

      if(!$accepted)
      {
        echo error_get_last()['message'];
      }

    }  
  
  }

?>