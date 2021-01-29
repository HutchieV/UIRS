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

  }

?>