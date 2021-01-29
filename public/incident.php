<?php
  require '/UIRS/includes/gen_config.php';
  require 'api/token.class.php';
  require 'api/location.class.php'
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

      function proc_incident()  {
        /*
          Validate and sanitise the requested incident from uri

          Returns null if there is no data,
          otherwise returns the sanitized string
        */

        if(!(isset($_GET["i"]))) return null;

        $in_req_id = strip_tags($_GET["i"]); // Remove dangerous tags
        $in_req_id = htmlspecialchars($in_req_id);          // Escape any remaining special characters
        $in_req_id = str_replace(' ', '', $in_req_id);      // Remove spaces
        
        return $in_req_id;
      }

      function get_incident_details($conn) {

        $in_data = null;
        $in_req_id = proc_incident();
        // echo $in_req_id;

        $q = $conn->prepare(' SELECT incident.*, organisation.* 
                              FROM incident 
                              INNER JOIN organisation ON incident.org_id=organisation.org_id
                              WHERE incident_id=:in_req_id');
        $q->bindValue(':in_req_id', $in_req_id);
        $q->execute();

        // echo "rows: " . $q->rowCount() . "<br>";

        $q->setFetchMode(PDO::FETCH_ASSOC);
        foreach($q->fetchAll() as $k=>$v) {
          $in_data = $v;
        }

        return $in_data;

      }

      $in_error_msg = null;
      $in_data = get_incident_details($conn);
      if(!($in_data)) {
        $in_error_msg = "Error: This incident does not exist";
      }

      // print_r($in_data);

    ?>

    <header>

      <?php include 'public_nav.php'; ?>

    </header>

    <main class="pub-main">

      <span class="pub-landing-title"> 
        <?php 
          if(!($in_data)) {
            echo $in_error_msg;
          } else {
            echo $in_data["incident_title_long"];
          };
        ?>
      </span>

      <span class="pub-i-m-id"> Incident #<?php echo $in_data["incident_id"] . " Posted by: " . $in_data["org_title"]; ?> </span>
      <span class="pub-i-m-id"> Last updated: <?php echo $in_data["incident_last_updated"] ?> </span>

      <div class="pub-i-m-title-cont">
        <span class='pub-in-pop-org'>                  </span>

        <div class='pub-in-pop-times'>
          <span class='pub-in-pop-times-lbl'>Incident starts: </span>
          <span class='pub-in-pop-times-value'> <?php echo $in_data["incident_start"] ?>  </span>
        </div>
        <div class='pub-in-pop-times'>
          <span class='pub-in-pop-times-lbl'>Incident ends: </span>
          <span class='pub-in-pop-times-value'> <?php echo $in_data["incident_end"] ?>    </span>
        </div>
      </div>

      <div class="pub-hr"></div>

      <span class="pub-i-m-subtitle"> Restrictions </span>

      <section class="pub-desc">
        <?php echo $in_data["incident_restrictions"] ?>
      </section>

      <span class="pub-i-m-subtitle"> Description </span>

      <section class="pub-desc">
        <?php echo $in_data["incident_description"] ?>
      </section>

      <span class="pub-i-m-subtitle"> Location </span>

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

    </main>

    <footer>

      <?php include 'public_footer.php'; ?>

    </footer>

  </body>

</html>