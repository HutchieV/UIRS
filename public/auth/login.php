<!DOCTYPE html>

<html lang="en">

  <head>

    <?php
      include '/UIRS/includes/gen_config.php';
      include 'public_meta.php';
    ?>

    <title>
      UIRS | Incident Details
    </title>

  </head>

  <body>

    <header>

      <?php include 'public_nav.php'; ?>

    </header>

    <main class="pub-main">

      <span class="pub-landing-title">Administration Login</span>

      <section class="pub-desc">

        <div class="adm-login-cont">
          <div class="adm-log-msg">
            This is a secure system.
            <br><br>
            Unauthorised login attempts will be logged.
          </div>

          <form class="adm-log-form" method="POST">
            <span class="adm-log-lbl">Username:</span>
            <input id="adm-log-u-input" class="adm-log-input-gen" type="text" name="adm_log_u"></input>
            <span class="adm-log-lbl">Password:</span>
            <input id="adm-log-p-input" class="adm-log-input-gen" type="password" name="adm_log_p"></input>
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