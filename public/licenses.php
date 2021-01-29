<?php
  include '/UIRS/includes/gen_config.php';
  include 'api/token.class.php';
?>

<!DOCTYPE html>

<html lang="en">

  <head>

    <?php
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

      <span class="pub-landing-title">Software & Data Licenses</span>

      <section class="pub-desc">
        <br>
        Postcode data: Office for National Statistics licensed under the <a href="https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/">Open Government Licence v.3.0</a>
        <br>
        Contains Royal Mail data © Royal Mail copyright and database right 2020
        <br>
        Contains OS data © Crown copyright and database right 2020
      </section>

    </main>

    <footer>

      <?php include 'public_footer.php'; ?>

    </footer>

  </body>

</html>