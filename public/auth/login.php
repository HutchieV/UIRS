<?php
  include '/UIRS/includes/gen_config.php';

  if(Token_API::verify_session($conn, false))  {
    header('Location: /auth/console');
    exit();
  }

  function get_hash($conn, $u)  {

    $p = null;
    $q = $conn->prepare(' SELECT * FROM user
                          WHERE user_username=:username');
    $q->bindValue(':username', $u);
    $q->execute();

    $q->setFetchMode(PDO::FETCH_ASSOC);
    foreach($q->fetchAll() as $k=>$v) {
      $p = $v;
    }

    return $p;

  }

  $adm_u_err   = null;
  $adm_u_place = null;
  $adm_p_err   = null;
  $adm_gen_err = null;

  if(isset($_GET["r"])) {
    if($_GET["r"] == "expired") {
      $adm_gen_err = "Session expired, please sign in again";
    } else if($_GET["r"] == "logout") {
      $adm_gen_err = "Successfully logged out";
    }
  }

  if( isset($_POST["adm_log_p"]) && !isset($_POST["adm_log_u"]) )  {
    $adm_u_err = "Please enter a username"; 
  } else if( isset($_POST["adm_log_u"]) && !isset($_POST["adm_log_p"]) ) {
    $adm_p_err = "Please enter a password";
  } else if( isset($_POST["adm_log_u"]) && isset($_POST["adm_log_p"]) ) {

    $u = sanitize_input($_POST["adm_log_u"]);
    if($_POST["adm_log_u"] != $u) {
      $adm_u_err = "Invalid username";
    } else  {

      $p = get_hash($conn, $u);
      if(password_verify( $_POST["adm_log_p"], $p["user_password"] ))  {

        $a_token = Token_API::auth_new_token($conn, $p["user_id"], 
                                      Token_API::get_time_now(),
                                      Token_API::get_time_plus("1 days"));

        sleep(1);

        if(Token_API::verify_token($conn, $p["user_id"], $a_token)) {
        } else  {
          $adm_gen_err = "A system error was encountered, please try again";
        };
                                
        setcookie("AUTH_TOKEN", $a_token, null, "/", DOMAIN_NAME, true, true);
        $_SESSION["USER"]       = $p;
        $_SESSION["AUTH_TOKEN"] = $a_token;
        header('Location: /auth/console');
        exit();

      } else  {

        $adm_u_place = $u;
        $adm_p_err = "Unknown account or incorrect password";

      };

    }

  }
?>

<!DOCTYPE html>

<html lang="en">

  <head>

    <?php
      include 'public_meta.php';
    ?>

    <title>
      UIRS | Admin Login
    </title>

  </head>

  <body>

    <nav>

      <?php include 'public_nav.php'; ?>

    </nav>

    <main class="pub-main">

      <span class="pub-landing-title">Administration Login</span>

      <section class="pub-desc">

        <div class="adm-login-cont">
          <div class="adm-log-msg">
            This is a secure system.
            <br><br>
            Unauthorised login attempts will be logged.
          </div>

          <form class="adm-log-form" action="/auth/login" method="POST">
          <span class="adm-log-lbl adm-log-err"><?php if($adm_gen_err) echo $adm_gen_err ?></span>
            <span class="adm-log-lbl">Username:</span>
            <input  id="adm-log-u-input" 
                    class="adm-log-input-gen"
                    type="text" name="adm_log_u" 
                    <?php if($adm_u_err) echo "style='border: 2px solid red;'" ?>
                    <?php if($adm_u_place) echo "value='$adm_u_place'"; ?>
            ></input>
            <span class="adm-log-lbl adm-log-err"><?php if($adm_u_err) echo $adm_u_err ?></span>
            <span class="adm-log-lbl">Password:</span>
            <input  id="adm-log-p-input"
                    class="adm-log-input-gen"
                    type="password"
                    name="adm_log_p"
                    <?php if($adm_p_err) echo "style='border: 2px solid red;'" ?>
              ></input>
              <span class="adm-log-lbl adm-log-err"><?php if($adm_p_err) echo $adm_p_err ?></span>
            <input id="adm-log-submit" type="Submit" value="Submit"></input>
          </form>
        </div>

      </section>

    </main>

    <footer>

      <?php include 'public_footer.php'; ?>

    </footer>

  </body>

</html>