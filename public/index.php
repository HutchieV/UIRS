<!DOCTYPE html>

<html lang="en">

  <head>

    <?php
      include '/UIRS/includes/gen_config.php';
      include 'public_meta.php';
      include 'api/proc_postcode_req.php';
    ?>

    <title>
      UIRS | Home
    </title>

  </head>

  <body>

    <header>

      <?php include 'public_nav.php'; ?>

    </header>

    <main class="pub-main">

      <span class="pub-landing-title">Welcome to the Unified Incident Reporting System</span>

      <section class="pub-desc pub-underline">
        This portal provides access to a nationwide database of on-going incidents as reported by health boards, councils, 
        police departments and national governments. To view data for your region, enter your postcode in the search bar below and
        click search.
      </section>

      <section class="pub-console-cont">

        <section class="pub-console-postcode-cont">
          <form class="pub-console-postcode-form" method="POST">
            <input id="pub-c-p-input" type="text" placeholder="Postcode" name="postcode" <?php if($pc) echo "value='$pc'"; ?>></text>
            <input id="pub-c-p-submit" type="submit" Value="Search"></input>
          </form>
          <div <?php if(!($pc_error_msg)) echo "style='visibility: hidden; margin: 0'"; ?>class="pub-c-p-error-cont">
            <span class="pub-c-p-error-label">
              <?php if($pc_error_msg) echo $pc_error_msg; ?>
            </span>
          </div>
          <div class="pub-c-p-sub-cont">
            <?php if(!($pc_data)) echo "Enter a postcode to view incidents in your region" ?>
          </div>
          <div class="pub-pcon-cont" <?php if(!($pc_data)) echo "style='visibility: hidden; margin: 0'" ?>>
            <?php 
              if($pc_data) {
                echo  '<span>' . 
                      $in_count . 
                      ' incident(s) in the <strong>' . 
                      $pc_data["pcon_name"] . 
                      '</strong> region (' . 
                      $pc_data["pcon_id"] . 
                      ')</span>';
              }
            ?>
          </div>
          <div class="pub-in-cont" <?php if(!($pc_data)) echo "style='visibility: hidden; margin: 0'" ?>>
            <?php
              foreach($in_data as &$i)  {
                echo "<div class='pub-in-pop-cont'>
                        <span class='pub-in-pop-title'>".$i["incident_title_short"]."</span>
                        <span class='pub-in-pop-org'>".$i["org_title"]."</span>
                        <div class='pub-in-pop-times'>
                          <span class='pub-in-pop-times-lbl'>Incident starts: </span><span class='pub-in-pop-times-value'>".$i["incident_start"]."</span>
                        </div>
                        <div class='pub-in-pop-times'>
                          <span class='pub-in-pop-times-lbl'>Incident ends: </span><span class='pub-in-pop-times-value'>".$i["incident_end"]."</span>
                        </div>
                        <div class='pub-in-pop-link-cont'>
                          <a href='/incident?i=".$i["incident_id"]."' class='pub-in-pop-link'>Read more ➔</a>
                        </div>
                      </div>";
              }
            ?>
          </div>
        </section>

        <section id="pub-console-map-cont" class="pub-console-map-cont">
          <p><strong>Error</strong></p>
          <p>It looks like your browser doesn't have JavaScript enabled. Don't worry, you can still enter your postcode to view current incidents.</p>
        </section>

        <script>
          var pc_data = false;
          <?php if($pc_data) echo "pc_data = true;"; ?>

          function show_sub_cont()  {
            var sub_cont = document.getElementsByClassName('pub-c-p-sub-cont')[0];
            if(pc_data) {
              sub_cont.innerHTML = `<span class="pub-c-p-sub-label">Notifications: </span>
                                    <button class="pub-c-p-sub-btn">
                                      Disabled
                                    </button>`
            }
            console.log("JavaScript enabled, displaying subscription option");
          }

          show_sub_cont();

          console.log("Adding map...");
          var map_cont = document.getElementById('pub-console-map-cont');
          map_cont.innerHTML = '<div id="map"></div>';
          map_cont.style.backgroundColor = 'lightgrey';

          <?php 
            if($pc_data)  {
              echo 'var map_zoom = 13;
                    var map_lat  = ' . $pc_data['postcode_lat']  . ';
                    var map_long = ' . $pc_data['postcode_long'] . ';';
            } else {
              echo 'var map_zoom = 5;
                    var map_lat  = 55;
                    var map_long = -4;';
            }
          ?>

          var map = L.map('map').setView([map_lat, map_long], map_zoom);
          // var map = L.map('map', { scrollWheelZoom: false, zoomControl: false, dragging: false }).setView([map_lat, map_long], map_zoom);

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

          <?php
            if($pc_data)  {
              echo 'L.marker([' . $pc_data['postcode_lat'] . ',' . $pc_data['postcode_long'] .']).addTo(map)
                      .bindPopup(\'' . $pc_data['pcon_name'] . '\')
                      .openPopup();';
            };

            if($in_data)  {
              foreach((array)$in_data as &$a)  {
                if($a['incident_lat'] and $a['incident_long'])  {
                  echo 'L.marker([' . $a['incident_lat'] . ',' . $a['incident_long'] .']).addTo(map)
                          .bindPopup(\'' . $a['incident_title_short'] . '\')
                          .openPopup();';
                }
              }
            }
          ?>

          console.log("Map added");

          function windowResized()  {
            map.invalidateSize()
          }

          window.onresize = windowResized;
        </script>

      </section>

    </main>

    <footer>

      <?php include 'public_footer.php'; ?>

    </footer>

  </body>

</html>