<?php
  require '/UIRS/includes/gen_config.php';
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

    <?php

      $in_error_msg = null;
      $in_data      = null;

      try
      {
        if(!(isset($_GET["i"]))) throw new ProcessIncidentException("No incident specified");

        $iid     = LocationAPI::validate_incident_id($_GET["i"]);
        if(!$iid) throw new ProcessIncidentException("Invalid incident identifier");

        $in_data = LocationAPI::get_incident_by_incident_id($conn, $iid);
        if(!$in_data) throw new ProcessIncidentException("This incident does not exist");
      }
      catch(ProcessIncidentException $e)
      {
        $in_error_msg = $e->message();
      } 
      catch(PDOException $e)
      {
        $in_error_msg = "A system error occured (003), please try again";
      }
      catch(DatabaseConnException $e)
      {
        $UIRS_FATAL_ERROR = "The system is experiencing technical difficulties. We apologise for the inconvenience. (E002)";
      }

    ?>

    <nav>

      <?php include 'public_nav.php'; ?>

    </nav>

    <header class="pub-i-m-header-cont">
      
      <div class="pub-main pub-i-m-header-inner">

        <div class="pub-i-m-header-left">
        
          <span class="pub-landing-title pub-i-m-lt"> 
            <?php 
              if(!($in_data)) {
                echo $in_error_msg;
              } else {
                echo $in_data["incident_title_long"];
              };
            ?>
          </span>

          <span class="pub-i-m-id">
            <?php echo "Posted by: " . $in_data["org_title"]; ?>
          </span>

        </div>

        <div class="pub-i-m-header-right">

          <img src=<?php echo '"' . $in_data["org_icon"] . '" alt="' . $in_data["org_title"] . ' logo"'?> />
        
        </div>


      </div>

    </header>

    <main class="pub-main">

      <?php include 'public_error_banner.php'; ?>

      <span class="pub-i-m-id"> 
        Last updated: 
        <?php 
          echo DBAPI::dt_to_human_readable($in_data["incident_last_updated"]);
        ?>
        <div class="pub-i-m-share">
          Share via 
          <a target="_blank" href="https://twitter.com/intent/tweet?text=<?php echo $in_data["incident_title_short"] . ", read more at " . DOMAIN_NAME . "/incident?i=".$in_data['incident_id'] ?>">
            Twitter
          </a>
          or
          <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo DOMAIN_NAME . "/incident?i=".$in_data['incident_id'] ?>">
            Facebook
          </a>
        </div>
      </span>

      <div class='pub-in-pop-times pub-i-m-times' style="margin-top: 1rem;">
        <span class='pub-in-pop-times-lbl'>Starts: </span>
        <span class='pub-in-pop-times-value'> <?php echo DBAPI::dt_to_human_readable($in_data["incident_start"]) ?>  </span>
      </div>
      <div class='pub-in-pop-times pub-i-m-times' style="margin-bottom: 1rem;">
        <span class='pub-in-pop-times-lbl'>Ends (TBC): </span>
        <span class='pub-in-pop-times-value'> <?php echo DBAPI::dt_to_human_readable($in_data["incident_end"]) ?>    </span>
      </div>

      <a href="javascript:window.history.back();" style="display: inline-block; margin-bottom: 1rem;">🡸 Back</a>

      <div class="pub-hr"></div>

      <span class="pub-i-m-subtitle"> Description </span>

      <section class="pub-desc">
        <?php echo $in_data["incident_description"] ?>
      </section>

      <span class="pub-i-m-subtitle"><?php if(isset($in_data["incident_restrictions"]) && $in_data["incident_restrictions"] !== "          ") echo "Restrictions"; ?></span>

      <section class="pub-desc">
        <?php echo $in_data["incident_restrictions"] ?>
      </section>

      <span class="pub-i-m-subtitle"><?php if($in_data["incident_lat"]) echo "Location"; ?></span>

      <div id="pub-in-map-cont" class="pub-in-map-cont">

      </div>

      <script>
        console.log("Adding map...");
        var map_cont = document.getElementById('pub-in-map-cont');
        map_cont.innerHTML = '<div id="in-map"></div>';
        map_cont.style.backgroundColor = 'lightgrey';

        map_zoom = 13;

        <?php 
          if($in_data)  {
            echo  'var map_lat  = ' . $in_data['incident_lat'] . ';' .
                  'var map_long = ' . $in_data['incident_long']. ';';
          }
        ?>

        function add_map()  {
          map_cont.style.display = "block";
          var map = L.map('in-map').setView([map_lat, map_long], map_zoom);

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          }).addTo(map);

          L.marker([map_lat, map_long]).addTo(map)
                    .bindPopup(' <?php echo $in_data["incident_title_short"] ?> ')
                    .openPopup();
        }

        <?php
          if($in_data)  {
            echo "add_map();";
          };
        ?>
      </script>

      <a href="javascript:window.history.back();" style="display: inline-block; margin-bottom: 1rem;">🡸 Back</a>

    </main>

    <footer>

      <?php include 'public_footer.php'; ?>

    </footer>

  </body>

</html>