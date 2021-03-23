<?php
  include '/UIRS/includes/gen_config.php';

  if( !isset($_POST["auth_token"])
      || !isset($_SESSION["AUTH_TOKEN"])
      || !TokenAPI::verify_form_token($_POST["auth_token"]) )  {
    header('Location: /auth/csrf');
  } else if ( TokenAPI::verify_session($conn, false) )  {
    TokenAPI::destroy_auth_session();
    header('Location: /auth/login?r=logout');
    exit();
  } else {
    header('Location: /auth/login?r=expired');
    exit();
  }

?>