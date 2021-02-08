<?php
  include '/UIRS/includes/gen_config.php';

  Token_API::verify_session($conn);

  function get_incidents($conn) {
    $q = $conn->prepare(' SELECT * FROM incident WHERE org_id=:org_id ');
    $q->bindValue(':org_id', $_SESSION["USER"]["org_id"]);
    $q->execute();

    $q->setFetchMode(PDO::FETCH_ASSOC);
    return $q->fetchAll();
  }

?>

<!DOCTYPE html>

<html lang="en">

  <head>

    <?php

      include 'public_meta.php';

    ?>

    <title>
      UIRS | Admin Console
    </title>

  </head>

  <body>

    <nav>

      <?php include 'public_nav.php'; ?>

    </nav>

    <main class="pub-main">

      <span class="pub-landing-title">Admin Console</span>

      <section class="pub-desc">
        Welcome, <?php echo $_SESSION["USER"]["user_full_name"] ?>
      </section>

      <div class="pub-hr"></div>

      <section class="pub-desc">

        <span class="pub-i-m-subtitle"> Incidents </span>

        The table below shows incidents created by your organisation. Select an existing incident to edit it.
        <br><br>

        <div class="adm-gen-table">

          <div class="adm-gen-table-row">

            <?php 

              $th = ["ID", "Posted", "Short Title", "Active"];
              foreach($th as $h)  {
                echo '<div class="adm-gen-table-field adm-th" style="font-weight: bold;">' . $h . '</div>';
              }

            ?>

          </div>

          <a class="adm-gen-table-row" href="/auth/edit-incident">
            <div class="adm-gen-table-field">Click here to add a new incident</div>
          </a>

          <?php

            $incidents = get_incidents($conn);

            foreach($incidents as $e)  {
              // print_r($e);

              echo '<a class="adm-gen-table-row" href="/auth/edit-incident?i='.$e["incident_id"].'">';
              echo '<div class="adm-gen-table-field">'.$e["incident_id"].'</div>';
              echo '<div class="adm-gen-table-field">'.$e["incident_date"].'</div>';
              echo '<div class="adm-gen-table-field">'.$e["incident_title_short"].'</div>';
              echo '<div class="adm-gen-table-field">';
              if($e["incident_active"] == 1) {
                echo "Yes";
              } else {
                echo "False";
              }
              echo '</div>';
              echo '</a>';
            }

          ?>

        </div>

      </section>

    </main>

    <footer>

      <?php include 'public_footer.php'; ?>

    </footer>

  </body>

</html>