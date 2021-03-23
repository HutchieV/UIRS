<?php

  class DBAPI {
  
    /**
     * Get a PDO DB connection object
     * 
     * @return object A PDO DB connection object
     */
    static function get_db_conn()  {
      $host     = "localhost";
      $username = "uirs_backend";
      $password = "uirs_backend_pword";
      $database = "uirs";
  
      try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        return $conn;
      } catch(PDOException $e)  {
        // echo "Connection failed: " . $e->getMessage();
        return null;
      }
    }

    public static function conn_test($conn) {
      if (!isset($conn)) throw new DatabaseConnException("Conn was not set");
    }

    /**
     * Logs a request to the server
     * 
     * @param object A PDO connection object
     * @param string The requested URI
     * @param string The originating IP address
     * @param string The session id of the client
     * @param string The request headers
     */
    static function log_req($conn, $uri, $ip, $session_id, $headers)
    {
      self::conn_test($conn);

      if (!$_SERVER['HTTP_USER_AGENT']) {
        $_SERVER['HTTP_USER_AGENT'] = null;
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

    /**
     * Returns an email subscription row, searched either
     * by id or email
     * 
     * @param object A PDO connection object
     * @param int The sub's unique id
     * @param string The user's email address
     * @return array The subscription row
     */
    static function get_subscription($conn, $id=null, $email=null)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' SELECT * 
                            FROM subscription 
                            WHERE sub_id=:sub_id OR sub_email=:sub_email');
      $q->bindValue(':sub_id', $id);
      $q->bindValue(':sub_email', $email);
      $q->execute();

      return $q->fetch();
    }

    /**
     * Insert a new email subscription
     * 
     * @param object A PDO connection object
     * @param int The sub's unique id
     * @param string The user's email address
     * @return array The subscription row
     */
    static function insert_subscription($conn, $token, $email)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' INSERT INTO subscription
                              (sub_token, sub_email, sub_created, sub_verified) 
                            VALUES
                              (:sub_token, :sub_email, NOW(), "false")');
      $q->bindValue(':sub_token', $token);
      $q->bindValue(':sub_email', $email);
      $q->execute();
      
      return self::get_subscription($conn, $token, $email);
    }

    /**
     * Get the regions a subscription is linked to
     * 
     * @param object A PDO connection object
     * @param int A sub's unique ID
     * @param string A user's email address
     * @return array An array containing all the regions a subscription is linked to 
     */
    static function get_sub_regions($conn, $sub_id=null, $email=null)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' SELECT  subscription.*, subscription_pcon.* 
                            FROM    subscription_pcon
                            JOIN    subscription ON subscription.sub_id=subscription_pcon.sub_id 
                            WHERE   subscription.sub_id=:sub_id OR subscription.sub_email=:sub_email
                            ');
      $q->bindValue(':sub_id', $sub_id);
      $q->bindValue(':sub_email', $email);
      $q->execute();

      return $q->fetchAll();
    }

    /**
     * Link a subscription with a region
     * 
     * @param object A PDO connection object
     * @param int A sub's unique ID
     * @param string A region's unique ID
     * @return int The ID of the link's row
     */
    static function insert_sub_region($conn, $sub_id, $rid)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' INSERT INTO subscription_pcon
                              (sub_id, pcon_id) 
                            VALUES
                              (:sub_id, :pcon_id)');
      $q->bindValue(':sub_id',  $sub_id);
      $q->bindValue(':pcon_id', $rid);
      $q->execute();

      return $conn->lastInsertId();
    }

    /**
     * Convert a MySQL datetime format string into
     * a UNIX timestamp
     * 
     * @param string A string in MySQL datetime format
     * @return int|null The datetime as a UNIX timestamp, null if fails
     */
    static function dt_to_timestamp($dt)
    {
      $ts = strtotime($dt);
      return $ts;
    }

    /**
     * Convert a UNIX timestamp into a human readable
     * string
     * 
     * @param int A UNIX timestamp
     * @return string|null A human-readable string 'l, jS F Y'
     */
    static function ts_to_human_readable($ts)
    {
      return date('l, jS M Y', $ts);
    }

    /**
     * Convert a MySQL datetime format string into
     * a human readable string
     * 
     * @param string A string in MySQL datetime format
     * @return string|null The datetime in a human readable format 'l, jS F Y'
     */
    static function dt_to_human_readable($dt)
    {
      $ts = self::dt_to_timestamp($dt);
      if(!$ts) return null;
      return date('D, jS M Y \a\t H:i', $ts);
    }

    /**
     * Convert a MySQL datetime format string into
     * a YYYY-MM-DD format string
     * 
     * @param string A string in MySQL datetime format
     * @return string|null The datetime in the format YYYY-MM-DD
     */
    static function dt_to_ymd_string($dt)  {
      $ts = self::dt_to_timestamp($dt);
      if(!$ts) return null;
      return date('Y-m-d', $ts);
    }

    /**
     * Convert a MySQL datetime format string into
     * a hh:mm format string
     * 
     * @param string A string in MySQL datetime format
     * @return string|null The datetime in the format hh:mm
     */
    static function dt_to_hm_string($dt)  {
      $ts = self::dt_to_timestamp($dt);
      if(!$ts) return null;
      return date('H:i', $ts);
    }

    /**
     * Combine a YYYY-MM-DD and hh:mm string to create a 
     * UNIX timestamp
     * 
     * @param string A time string in the format YYYY-MM-DD
     * @param string A time string in the format hh:mm
     * @return string|null The timestamp of both combined
     */
    static function ymd_hm_to_ts($ymd, $hm)  {
      $ymd = DateTime::createFromFormat('Y-m-d H:i', $ymd . "00:00");

      $small_t  = preg_split('[:]', $hm);
      $hm       = DateTime::createFromFormat('U', "0");
      $hm->setTime($small_t[0], $small_t[1]);

      return $ymd->getTimestamp() + $hm->getTimestamp();
    }

    /**
     * Combine a YYYY-MM-DD and hh:mm string to create a 
     * UNIX timestamp
     * 
     * @param string A time string in the format YYYY-MM-DD
     * @param string A time string in the format hh:mm
     * @return string|null The timestamp of both combined
     */
    static function ymd_hm_to_dt($ymd, $hm)  {
      $ymd = DateTime::createFromFormat('Y-m-d H:i', $ymd . "00:00");

      $small_t  = preg_split('[:]', $hm);
      $hm       = DateTime::createFromFormat('U', "0");
      $hm->setTime($small_t[0], $small_t[1]);

      return date('Y-m-d H:i:s', $ymd->getTimestamp() + $hm->getTimestamp());
    }

  }

?>