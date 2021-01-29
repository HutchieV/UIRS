<?php

  class Token_API {

    static function get_time_now() {
      return date("Y-m-d H:i:s");
    }

    static function get_time_plus($str)  {
      $date = new DateTime("now");
      date_add($date, date_interval_create_from_date_string($str));
      return $date->format("Y-m-d H:i:s");
    }

    static function gen_new_token($length)  {
      return bin2hex(random_bytes($length));
    }

    static function set_auth_token($a_token)  {
      setcookie("AUTH_TOKEN", $a_token, null, "/", DOMAIN_NAME, true, true);
    }

    static function destroy_auth_token()  {
      setcookie("AUTH_TOKEN", "null", 1, "/", DOMAIN_NAME, true, true);
    }

    static function destroy_auth_session()  {
      self::destroy_auth_token();
      session_destroy();
    }

    static function auth_new_token($conn, $user_id, $valid_from, $valid_until) {

      $a_token = self::gen_new_token(64);

      $q = $conn->prepare(' INSERT INTO token (token_value, token_created, token_valid_from, token_valid_to, user_id)
                            VALUES (:token_value, NOW(), :token_vf, :token_vt, :user_id)');
      $q->bindValue(":token_value", $a_token);
      $q->bindValue(":token_vf",    $valid_from);
      $q->bindValue(":token_vt",    $valid_until);
      $q->bindValue(":user_id",     $user_id);
      $q->execute();

      if( $q->rowCount() == 0 ) return null;
      return $a_token;

    }

    static function verify_token($conn, $user_id, $token)  {

      $q = $conn->prepare(' SELECT user_id 
                            FROM token 
                            WHERE token_value=:token_value AND
                            token_valid_from < NOW() AND
                            token_valid_to   > NOW()');
      $q->bindValue(":token_value", $token);
      $q->execute();

      $q->setFetchMode(PDO::FETCH_ASSOC);
      foreach($q->fetchAll() as $k=>$v) {
        if($user_id == $v["user_id"]) return true;
      }

      return false;

    }

    static function verify_form_token($f_token) {
      if(hash_equals($f_token, $_SESSION["AUTH_TOKEN"])) return true;
      return false;
    }

    static function verify_session($conn, $redirect=true)  {
      /**
      *   @return if $redirect = TRUE, returns TRUE on a verified session, FALSE otherwise 
      */

      if( !isset($_SESSION["USER"]) || !isset($_COOKIE["AUTH_TOKEN"]) || !isset($_SESSION["AUTH_TOKEN"])
          || !(self::verify_token($conn, $_SESSION["USER"]["user_id"], $_COOKIE["AUTH_TOKEN"]))
          || $_COOKIE["AUTH_TOKEN"] != $_SESSION["AUTH_TOKEN"])  {
        if($redirect) {
          header('Location: /auth/login?r=expired');
          exit();
        } else {
          return false;
        }
      };

      return true;
    }

  }

?>