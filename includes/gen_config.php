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


  $config = parse_ini_file(dirname(__FILE__) . "/../config.ini", true);
  set_include_path(dirname(__FILE__));

  define("BCRYPT_WORK_FACTOR",  $config["security"]["bcrypt_work_factor"]);
  define("DOMAIN_NAME",         $config["server"]["hostname"]);

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

  // Get a new connection object and log the request
  $conn = DBAPI::get_db_conn();
  DBAPI::log_req($conn, $_SERVER['REQUEST_URI'], get_ip_address(), session_id(), json_encode(getallheaders()));

?>