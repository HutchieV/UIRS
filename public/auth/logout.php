<?php
  include '/UIRS/includes/gen_config.php';
  include 'api/token.class.php';

  if( !isset($_POST["auth_token"])
      || !isset($_SESSION["AUTH_TOKEN"])
      || !Token_API::verify_form_token($_POST["auth_token"]) )  {
    header('Location: /auth/csrf');
  } else if ( Token_API::verify_session($conn, false) )  {
    Token_API::destroy_auth_session();
    header('Location: /auth/login?r=logout');
    exit();
  } else {
    header('Location: /auth/login?r=expired');
    exit();
  }

?>