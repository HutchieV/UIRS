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

      <section class="pub-desc">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc non lorem nec risus viverra luctus. 
        Nulla facilisi. Nullam porttitor ligula vitae tortor feugiat ornare. Nunc bibendum elementum neque eget eleifend. 
        Etiam rutrum enim et diam rhoncus, at imperdiet tellus feugiat. Suspendisse potenti. 
      </section>

      <section class="pub-console-cont">

        <section class="pub-console-postcode-cont">
          <form class="pub-console-postcode-form" method="POST">
            <input id="pub-c-p-input" type="text" placeholder="Postcode" name="postcode" <?php if($pc) echo "value='$pc'"; ?>></text>
            <input id="pub-c-p-submit" type="submit" Value="Submit"></input>
          </form>
          <div <?php if(!($pc_error_msg)) echo "style='visibility: hidden; margin: 0'"; ?>class="pub-c-p-error-cont">
            <span class="pub-c-p-error-label">
              <?php if($pc_error_msg) echo $pc_error_msg; ?>
            </span>
          </div>
          <div class="pub-pcon-cont" <?php if(!($pc_data)) echo "style='visibility: hidden; margin: 0'" ?>>
            <?php if($pc_data) echo $pc_data["pcon_name"] . ' Region'; ?>
          </div>
          <div class="pub-c-p-sub-cont">
            <span class="pub-c-p-sub-label">Notifications: </span>
            <button class="pub-c-p-sub-btn">
              Disabled
            </button>
          </div>
        </section>

        <section id="pub-console-map-cont" class="pub-console-map-cont">
          <p><strong>Oops!</strong></p>
          <p>It looks like your browser doesn't have JavaScript enabled. Don't worry, you can still enter your postcode to view current incidents.</p>
        </section>

        <script>
          var subCont = document.getElementsByClassName('pub-c-p-sub-cont')[0];
          subCont.style.visibility = 'visible';
          console.log("JavaScript enabled, displaying subscription option");

          console.log("Adding map...");
          var mapCont = document.getElementById('pub-console-map-cont');
          mapCont.innerHTML = '<div id="map"></div>';
          mapCont.style.backgroundColor = 'lightgrey';

          <?php 
            if($pc_data)  {
              echo 'var mapZoom = 13;
                    var mapLat  = ' . $pc_data['postcode_lat']  . ';
                    var mapLong = ' . $pc_data['postcode_long'] . ';';
            } else {
              echo 'var mapZoom = 5;
                    var mapLat  = 55;
                    var mapLong = -4;';
            }
          ?>

          var map = L.map('map', { scrollWheelZoom: false, zoomControl: false, dragging: false }).setView([mapLat, mapLong], mapZoom);

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

          <?php
            if($pc_data)  {
              echo 'L.marker([' . $pc_data['postcode_lat'] . ',' . $pc_data['postcode_long'] .']).addTo(map)
                      .bindPopup(\'' . $pc_data['pcon_name'] . '\')
                      .openPopup();';
            };
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