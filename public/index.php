<!DOCTYPE html>

<html lang="en">

  <head>

    <?php
      include '/UIRS/includes/gen_config.php';
      include 'public_meta.php';
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

      <section class="pub-desc">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc non lorem nec risus viverra luctus. 
        Nulla facilisi. Nullam porttitor ligula vitae tortor feugiat ornare. Nunc bibendum elementum neque eget eleifend. 
        Etiam rutrum enim et diam rhoncus, at imperdiet tellus feugiat. Suspendisse potenti. 
      </section>

      <section class="pub-console-cont">

        <section class="pub-console-postcode-cont">
          <form class="pub-console-postcode-form">
            <input id="pub-c-p-input" type="text" placeholder="Postcode"></text>
            <input id="pub-c-p-submit" type="submit" Value="Submit"></input>
          </form>
        </section>

        <section id="pub-console-map-cont" class="pub-console-map-cont">
          <p><strong>Oops!</strong></p>
          <p>It looks like your browser doesn't have JavaScript enabled. Don't worry, you can still enter your postcode to view current incidents.</p>
        </section>

        <script>
          var mapCont = document.getElementById('pub-console-map-cont');
          mapCont.innerHTML = '<div id="map"></div>';
          mapCont.style.backgroundColor = 'lightgrey';

          var map = L.map('map').setView([55, -4], 5);

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

          function windowResized()  {
            map.invalidateSize()
          }

          window.onresize = windowResized;
        </script>

      </section>

    </main>

    <footer>

    </footer>

  </body>

</html>