<?php
  // Non-production error reporting
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  require 'api/db.class.php';
  require 'api/token.class.php';
  require 'api/location.class.php';
  require 'api/exceptions.class.php';

  // echo PHP_VERSION_ID;

  set_include_path(dirname(__FILE__));

  CONST BCRYPT_WORK_FACTOR  = 12;
  CONST DOMAIN_NAME         = "uirs.localhost";

  if(PHP_VERSION_ID > 70299) {
    session_set_cookie_params([
      'secure'    => 'true',
      'httponly'  => 'true',
      'samesite'  => 'strict'
    ]);
  }

  session_start();
  session_regenerate_id();

  function sanitize_input($i)  {
    /*
      Sanitize input
    */

    $i = strip_tags($i);        // Remove dangerous tags
    $i = htmlspecialchars($i);  // Escape any remaining special characters

    return $i;
  }

  function get_ip_address()   {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
      if (array_key_exists($key, $_SERVER) === true){
        foreach (explode(',', $_SERVER[$key]) as $ip){
          $ip = trim($ip); // Just to be safe

          if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
              return $ip;
          }
        }
      }
    }
  }

  function log_req($conn) {
    if (!$_SERVER['HTTP_USER_AGENT']) {
      return $_SERVER['HTTP_USER_AGENT'] = null;
    }

    $q = $conn->prepare(' INSERT INTO req_log
                            (log_route, log_ip, log_session, log_request) 
                          VALUES
                            (:log_route, :log_ip, :log_session, :log_request)');
    $q->bindValue(':log_route', $_SERVER['REQUEST_URI']);
    $q->bindValue(':log_ip', get_ip_address());
    $q->bindValue(':log_session', session_id());
    $q->bindValue(':log_request', json_encode(getallheaders())); // NB: Does not store POST data
    $q->execute();
  }

  $conn = DBAPI::get_db_conn();
  log_req($conn);

?>