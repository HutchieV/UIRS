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

    static function ts_to_human_readable($ts)
    {
      return date('l, jS F Y', $ts);
    }

    static function dt_to_human_readable($dt)
    {
      $ts = self::dt_to_timestamp($dt);
      if(!$ts) return null;
      return date('l, jS F Y', $ts);
    }

  }

?>