<?php
  require '/UIRS/includes/gen_config.php';
  
  $pc_error_msg = null;
  $pc           = null;
  $pc_data      = null;
  $r_data       = null;
  $in_count     = null;
  $in_data      = null;

  function process_postcode($conn, &$pc, &$pc_data, &$r_data, &$in_data)
  {
    $pc = LocationAPI::validate_postcode($_POST["postcode"]);
    if(!$pc) throw new ProcessPostcodeException("Invalid postcode");

    $pc_data = LocationAPI::get_postcode_by_postcode($conn, $pc);
    if(!$pc_data) throw new ProcessPostcodeException("Unknown postcode");

    $r_data = LocationAPI::get_region_by_postcode($conn, $pc);
    if(!$r_data) throw new ProcessPostcodeException("A system error occured (001), please try again");

    header('Location: /?r=' . $r_data["pcon_id"]);

    $in_data = LocationAPI::get_incidents_by_region_id($conn, $r_data["pcon_id"]);
    // if(!$in_data) throw new ProcessPostcodeException("A system error occured (002), please try again");

    return true;
  }

  if(isset($_POST["postcode"]))
  {
    try {
      if(!process_postcode($conn, $pc, $pc_data, $r_data, $in_data))
      {
        $pc_error_msg = "Unknown postcode";
      }
      else
      {
        $in_count = count($in_data);
      }
    } catch(ProcessPostcodeException $e) {
      $pc_error_msg = $e->message();
    } catch(PDOException $e)  {
      $pc_error_msg = "A system error occured (003), please try again";
    }
  }
  else if(isset($_GET["r"]))
  {
    $rid = $_GET["r"];
    $rid = sanitize_input($rid);

    try
    {
      $r_data = LocationAPI::get_region_by_region_id($conn, $rid);
      if(!$r_data) throw new ProcessPostcodeException("A system error occured (001), please try again");
    
      $in_data  = LocationAPI::get_incidents_by_region_id($conn, $r_data["pcon_id"]);
      $in_count = count($in_data);
    } catch(ProcessPostcodeException $e) {
      $pc_error_msg = $e->message();
    } catch(PDOException $e)  {
      $pc_error_msg = "A system error occured (003), please try again";
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
      UIRS | Home
    </title>

  </head>

  <body>

    <nav>

      <?php include 'public_nav.php'; ?>

    </nav>

    <header class="pub-i-m-header-cont">
      
      <div class="pub-main pub-i-m-header-inner">

        <span class="pub-landing-title">Welcome to the Unified Incident Reporting System</span>

      </div>

    </header>

    <main class="pub-main">

      <section class="pub-desc pub-underline">
      <span class="pub-i-m-subtitle">What is the UIR System?</span>

        This portal provides access to a nationwide database of future and on-going incidents reported by health boards, councils, 
        police departments and national governments. To view data for your region, enter your postcode in the search bar below and
        click search.
      </section>

      <section class="pub-console-cont">

        <section class="pub-console-postcode-cont">
          <form class="pub-console-postcode-form" method="POST">
            <input id="pub-c-p-input" type="text" placeholder="Enter your postcode" name="postcode" <?php if($pc) echo "value='$pc'"; ?>></input>
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
          <div class="pub-pcon-cont" <?php if(!($r_data)) echo "style='visibility: hidden; margin: 0'" ?>>
            <?php 
              if($r_data) {
                echo  '<span>' . 
                      $in_count . 
                      ' incident(s) in the <strong>' . 
                      $r_data["pcon_name"] . 
                      '</strong> region (' . 
                      $r_data["pcon_id"] . 
                      ')</span>';
              }
            ?>
          </div>
          <div class="pub-in-cont" <?php if(!($in_data)) echo "style='display: none; margin: 0'" ?>>
            <?php
              if($in_data)  {
                foreach($in_data as &$i)  {
                  switch($i["incident_level"])
                  {
                    case 0:
                      $bcol = "#e6e6e6";
                      break;
                    case 1:
                      $bcol = "#175278";
                      break;
                    case 2:
                      $bcol = "#6b44a3";
                      break;
                    case 3:
                      $bcol = "#ff2e17";
                      break;
                    default: 
                      $bcol = "#1c8d4f";
                      break;
                  }

                  echo "<div class='pub-in-pop-cont' style='border-left:10px solid ". $bcol ."'>
                          <span class='pub-in-pop-title'>".$i["incident_title_short"]."</span>
                          <span class='pub-in-pop-org'>".$i["org_title"]."</span>
                          <div class='pub-in-pop-times'>
                            <span class='pub-in-pop-times-lbl'>Starts: </span><span class='pub-in-pop-times-value'>".DBAPI::dt_to_human_readable($i["incident_start"])."</span>
                          </div>
                          <div class='pub-in-pop-times'>
                            <span class='pub-in-pop-times-lbl'>Ends (TBC): </span><span class='pub-in-pop-times-value'>".DBAPI::dt_to_human_readable($i["incident_end"])."</span>
                          </div>
                          <div class='pub-in-pop-link-cont'>
                            <a href='/incident?i=".$i["incident_id"]."' class='pub-in-pop-link'>Read more ➔</a>
                          </div>
                        </div>";
                }
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
          var rid     = null;
          <?php 
            if($pc_data) echo "pc_data = true;"; 
            if(isset($rid)) echo "rid = '" . $rid . "';";
          ?>

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
            if($r_data)  {
              echo 'var map_zoom = 11;
                    var map_lat  = 55.88439941                    ;
                    var map_long = -3.30739999                    ;';
            } else {
              echo 'var map_zoom = 5;
                    var map_lat  = 55;
                    var map_long = -4;';
            }
          ?>

          var map_zoom = 5;
          var map_lat  = 55;
          var map_long = -4;

          var map = L.map('map').setView([map_lat, map_long], map_zoom);
          // var map = L.map('map', { scrollWheelZoom: false, zoomControl: false, dragging: false }).setView([map_lat, map_long], map_zoom);

          function get_bounds() {

            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {   
              if (this.readyState == 4 && this.status == 200) {
                var gj_data = JSON.parse(this.responseText);
                var style="";

                if(rid) 
                {
                  for(var x=0; x<gj_data.features.length; x++)
                  {
                    if(gj_data.features[x].properties.pcon17cd == rid)
                    {
                      map.setView([gj_data.features[x].properties.lat, gj_data.features[x].properties.long], 11);

                      style = {
                        style: function(feature) {
                          switch (feature.properties.pcon17cd) {
                              case  rid: return { fillColor:  "green",
                                                  color:      "lightgreen"};
                              default:   return { color:      "4f4f4f"};
                          }
                        }
                      }
                    }
                  }
                }

                var gj = L.geoJSON(gj_data, style).addTo(map);
              }
            };
            xhttp.open("GET", "/content/pcon_dec_2017.geojson", true);
            xhttp.send();

          }

          if(rid) get_bounds(); 

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

          <?php
            if($in_data)  {
              foreach((array)$in_data as &$a)  {
                if($a['incident_lat'] and $a['incident_long'])  {
                  echo 'L.marker([' . $a['incident_lat'] . ',' . $a['incident_long'] .']).addTo(map)
                          .bindPopup(\'<a href="/incident?i=' . $a["incident_id"] . '" class="pub-in-pop-link">' . $a["incident_title_short"] . ' ➔</a> \')
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