<?php
  include '/UIRS/includes/gen_config.php';

  Token_API::verify_session($conn);

  $in_error_msg     = null;
  // $in_input_errors  = [
  //   "incident_title_short" => "Title too short",
  //   "incident_title_long" => "Title too long",
  //   "incident_start" => "Not a date!",
  //   "incident_end" => "Still not a date :(",
  //   "incident_description" => "Nice description :)",
  //   "incident_restrictions" => "Nicer restrictions!",
  //   "incident_regions" => "Invalid regions"
  // ];
  $in_input_errors  = [];
  $in_data          = null;

  function insert_incident($conn, &$in_input_errors, &$in_data)  {
    // Received data to insert

    $n_incident = [
      "id"              => null,
      "title_short"     => null,
      "title_long"      => null,
      "active"          => null,
      "level"           => null,
      "start_date"      => null,
      "start_time"      => null,
      "start_timestamp" => null,   
      "end_date"        => null,
      "end_time"        => null,
      "end_timestamp"   => null,
      "description"     => null,
      "restrictions"    => null,
      "regions"         => []
    ];

    if(isset($_GET["i"])) {
      $n_incident["id"] = $_GET["i"];
      load_incident($conn, $in_data);
    }


    // Validate short title
    $pat = "/^[a-zA-Z0-9_ ]{1,254}$/"; // Matches 1-254 characters of plaintext
    $n_incident["title_short"] = sanitize_input($_POST["in_title_short"]);
    if(!preg_match($pat, $n_incident["title_short"])) $in_input_errors["incident_title_short"] = "Please provide a short title between 0 and 254 characters";
    $in_data["incident_title_short"] = $n_incident["title_short"];
    
    // Validate long title
    // Using same pattern as before
    $n_incident["title_long"] = sanitize_input($_POST["in_title_long"]);
    if(!preg_match($pat, $n_incident["title_long"])) $in_input_errors["incident_title_long"] = "Please provide a long title between 0 and 254 characters";
    $in_data["incident_title_long"] = $n_incident["title_long"];


    // Validate severity level
    $pat = "/[0-3]$/";
    $n_incident["level"] = sanitize_input($_POST["in_level"]);
    if(!preg_match($pat, $n_incident["level"])) $in_input_errors["incident_level"] = "Please select a valid level between 0 and 3";
    $in_data["incident_level"] = $n_incident["level"];


    // Validate active level
    if(isset($_POST["in_active"]) and $_POST["in_active"] == "on")
    {
      $n_incident["active"] = 1;
    }
    else
    {
      $n_incident["active"] = 0;
    }
    $in_data["incident_active"] = $n_incident["active"];


    // Validate start date
    $pat = "/^[2-9][0-9][0-9][0-9]-[0-1][0-9]-[0-3][0-9]$/";
    $n_incident["start_date"] = sanitize_input($_POST["in_start_date"]);
    if(!preg_match($pat, $n_incident["start_date"])) $in_input_errors["incident_start"] = "Please provide a date in the format YYYY-MM-DD and time in HH:MM";

    // Validate end date
    $n_incident["end_date"] = sanitize_input($_POST["in_end_date"]);
    if(!preg_match($pat, $n_incident["end_date"])) $in_input_errors["incident_end"] = "Please provide a date in the format YYYY-MM-DD and time in HH:MM";

    // Validate start time
    $pat = "/^[0-2][0-9]:[0-5][0-9]$/";
    $n_incident["start_time"] = sanitize_input($_POST["in_start_time"]);
    if(!preg_match($pat, $n_incident["start_time"])) $in_input_errors["incident_start"] = "Please provide a date in the format YYYY-MM-DD and time in HH:MM";

    // Validate end time
    $n_incident["end_time"] = sanitize_input($_POST["in_end_time"]);
    if(!preg_match($pat, $n_incident["end_time"])) $in_input_errors["incident_end"] = "Please provide a date in the format YYYY-MM-DD and time in HH:MM";

    if(!isset($in_input_errors["incident_start"])) $n_incident["start_timestamp"]  = DBAPI::ymd_hm_to_dt($n_incident["start_date"], $n_incident["start_time"]);
    if(!isset($in_input_errors["incident_end"]))   $n_incident["end_timestamp"]    = DBAPI::ymd_hm_to_dt($n_incident["end_date"], $n_incident["end_time"]);


    // Validate description
    $n_incident["description"] = $_POST["in_description"];
    if(!$n_incident["description"]) $in_input_errors["incident_description"] = "Please provide a description";
    $in_data["incident_description"] = $n_incident["description"];

    // Validate restrictions
    $n_incident["restrictions"] = $_POST["in_restrictions"];
    if(!$n_incident["restrictions"]) $in_input_errors["incident_restrictions"] = "Please provide some detail on restrictions or effects";
    $in_data["incident_restrictions"] = $n_incident["restrictions"];


    $i_list = LocationAPI::get_all_regions($conn);
    $pat = "/\([A-Z0-9]{1,20}\)$/";

    function validate_region(&$i_list, $iid)
    {
      foreach($i_list as $i)
      {
        if($i["pcon_id"] == $iid) return $i["pcon_name"];
      }
      return null;
    }

    if(!isset($_POST["in_regions"])) {
      // Do nothing, this means the incident is not live yet
    }
    else
    {
      foreach($_POST["in_regions"] as $r)
      {
        preg_match($pat, $r, $matches);
        $iid = trim($matches[0], "()");
        if(!(validate_region($i_list, $iid)))
        {
          $in_input_errors["incident_regions"] = "You have submitted invalid regions";
        }
        else
        {
          array_push($n_incident["regions"], $iid);
        }
      }
    }

    /**
     * Returns true if no errors were found
     * 
     * @param array Error list
     * @return boolean True for no errors, false if errors found
     */
    function check_errors($in_input_errors)
    {
      foreach($in_input_errors as $err)
      {
        if($err) return false;
      }
      return true;
    }

    if(check_errors($in_input_errors))
    {
      // All checks completed, insert data
      // print_r($n_incident);

      if($n_incident["id"])
      {
        LocationAPI::update_incident( $conn,
                                      $n_incident["title_short"],
                                      $n_incident["title_long"],
                                      $n_incident["active"],
                                      $n_incident["level"],
                                      $n_incident["start_timestamp"],
                                      $n_incident["end_timestamp"],
                                      $n_incident["description"],
                                      $n_incident["restrictions"],
                                      $n_incident["regions"],
                                      $_SESSION["USER"]["org_id"],
                                      $n_incident["id"]);
      }
      else
      {
        LocationAPI::insert_incident( $conn,
                                      $n_incident["title_short"],
                                      $n_incident["title_long"],
                                      $n_incident["active"],
                                      $n_incident["level"],
                                      $n_incident["start_timestamp"],
                                      $n_incident["end_timestamp"],
                                      $n_incident["description"],
                                      $n_incident["restrictions"],
                                      $n_incident["regions"],
                                      $_SESSION["USER"]["org_id"]);
      }

    }

  }

  function load_incident($conn, &$in_data)
  {
    // Main entry
    if(!isset($_GET["i"]))
    {
      // New incident
      // Do nothing at the moment, because there is no data to pre-load
    }
    else if($iid = LocationAPI::validate_incident_id($_GET["i"]))
    {
      // Valid incident id

      $in_data = LocationAPI::get_incident_by_incident_id($conn, $iid);
      if(!$in_data) throw new ProcessIncidentException("Invalid incident identifier (incident does not exist)");
    }
    else
    {

    }
  }

  if(isset($_POST["auth_token"]) && Token_API::verify_form_token($_POST["auth_token"]))
  {
    // Data has been submitted to create a new incident
    try
    {
      insert_incident($conn, $in_input_errors, $in_data);
    }
    catch (CreateIncidentException $e)
    {
      $in_error_msg = $e->message();
    }
    catch (ProcessIncidentException $e)
    {
      $in_error_msg = $e->message();
    }
    catch (PDOException $e)
    {
      $in_error_msg = "A system error has occured while creating this incident. Please try again.";
    }
  }
  else
  {
    // Assume editing new or existing incident
    try
    {
      load_incident($conn, $in_data);
    }
    catch (ProcessIncidentException $e)
    {
      $in_error_msg = $e->message();
    }
    catch (PDOException $e)
    {
      $in_error_msg = "A system error has occured while loading this incident. Please try again.";
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
      UIRS | Edit Incident
    </title>

  </head>

  <body>

    <nav>

      <?php include 'public_nav.php'; ?>

    </nav>

    <main class="pub-main">

      <span class="pub-landing-title"> <?php if($in_error_msg) echo $in_error_msg ?> </span>

      <span class="pub-landing-title">Edit or create a new incident</span>

      <section class="pub-desc">
        Please note that incident details can be edited at any time once submitted.<br>
        If you would not like the incident to go public, then do select any regions.
      </section>

      <form id="adm-in-form" class="adm-in-cont" method="POST">
        <div class="adm-in-half-cont">
          
          <!-- INCIDENT SHORT TITLE -->
          <span class="adm-log-lbl">Short incident title</span>
          <input  class="adm-log-input-gen"
                  name="in_title_short"
                  style="<?php if(array_key_exists("incident_title_short", $in_input_errors)) echo 'border: 3px solid red' ?> "
                  value="<?php if($in_data) echo $in_data['incident_title_short'] ?>"/>
          <span class="adm-log-lbl adm-log-err"> <?php if(array_key_exists("incident_title_short", $in_input_errors)) echo $in_input_errors["incident_title_short"] ?> </span>
          
          <!-- INCIDENT FULL TITLE -->
          <span class="adm-log-lbl">Full incident title</span>
          <input  class="adm-log-input-gen"
                  name="in_title_long"
                  style="<?php if(array_key_exists("incident_title_long", $in_input_errors)) echo 'border: 3px solid red' ?> "
                  value="<?php if($in_data) echo $in_data['incident_title_long'] ?>"/>
          <span class="adm-log-lbl adm-log-err"> <?php if(array_key_exists("incident_title_long", $in_input_errors)) echo $in_input_errors["incident_title_long"] ?> </span>
          
          <!-- INCIDENT SEVERITY LEVEL -->
          <span class="adm-log-lbl">Incident severity level</span>
          <select class="adm-log-input-gen"
                  name="in_level"
                  style="<?php if(array_key_exists("incident_level", $in_input_errors)) echo 'border: 3px solid red' ?> "
                  >
                  <option value="0" <?php if(isset($in_data['incident_level']) && $in_data['incident_level'] == "0") echo 'selected=""'?>>0: Information</option>
                  <option value="1" <?php if(isset($in_data['incident_level']) && $in_data['incident_level'] == "1") echo 'selected=""'?>>1: Low        </option>
                  <option value="2" <?php if(isset($in_data['incident_level']) && $in_data['incident_level'] == "2") echo 'selected=""'?>>2: Medium     </option>
                  <option value="3" <?php if(isset($in_data['incident_level']) && $in_data['incident_level'] == "3") echo 'selected=""'?>>3: High       </option>
          </select>
          <span class="adm-log-lbl adm-log-err"> <?php if(array_key_exists("incident_level", $in_input_errors)) echo $in_input_errors["incident_level"] ?> </span>
        </div>

        <div class="adm-in-half-cont">
          <span class="adm-log-lbl adm-log-datetime">Time settings</span>
          
          <!-- INCIDENT ACTIVE CHECKBOX -->
          <div class="adm-in-time-cont" style=" <?php if(array_key_exists("incident_active", $in_input_errors)) echo 'border: 3px solid red' ?> ">
            <span class="adm-log-lbl adm-log-datetime">Set incident active</span>
            <input  class="adm-log-input-gen adm-log-datetime"
                    type="checkbox"
                    name="in_active"
                    <?php if(isset($in_data['incident_active']) && $in_data['incident_active']==1) echo ' checked=""' ?>/>
          </div>
          <span class="adm-log-lbl adm-log-err"><?php if(array_key_exists("incident_active", $in_input_errors)) echo $in_input_errors["incident_active"] ?></span>

          <!-- INCIDENT START DATE AND TIME -->
          <span class="adm-log-lbl">Start of incident</span>
          <div class="adm-in-time-cont" style=" <?php if(array_key_exists("incident_start", $in_input_errors)) echo 'border: 3px solid red' ?> ">
            <input  class="adm-log-input-gen adm-log-datetime" 
                    type="date" 
                    name="in_start_date"
                    value="<?php if($in_data) echo DBAPI::dt_to_ymd_string($in_data['incident_start']) ?>"/>
            <input  class="adm-log-input-gen adm-log-datetime"
                    type="time"
                    name="in_start_time"
                    value="<?php if($in_data) echo DBAPI::dt_to_hm_string($in_data['incident_start']) ?>"/>
          </div>

          <!-- INCIDENT END DATE AND TIME -->
          <span class="adm-log-lbl adm-log-err"> <?php if(array_key_exists("incident_start", $in_input_errors)) echo $in_input_errors["incident_start"] ?> </span>
          <span class="adm-log-lbl">End of incident</span>
          <div class="adm-in-time-cont" style=" <?php if(array_key_exists("incident_end", $in_input_errors)) echo 'border: 3px solid red' ?> ">
            <input  class="adm-log-input-gen adm-log-datetime"
                    type="date"
                    name="in_end_date"
                    value="<?php if($in_data) echo DBAPI::dt_to_ymd_string($in_data['incident_end']) ?>"/>
            <input  class="adm-log-input-gen adm-log-datetime"
                    type="time"
                    name="in_end_time"
                    value="<?php if($in_data) echo DBAPI::dt_to_hm_string($in_data['incident_end']) ?>"/>
          </div>
          <span class="adm-log-lbl adm-log-err"> <?php if(array_key_exists("incident_end", $in_input_errors)) echo $in_input_errors["incident_end"] ?> </span>
        </div>

        <!-- INCIDENT DESCRIPTION -->
        <div class="adm-in-half-cont">
          <span class="adm-log-lbl">Incident description (may contain HTML)</span>
          <textarea form="adm-in-form" 
                    class="adm-log-input-gen adm-in-textarea"
                    type="textbox"
                    name="in_description"
                    style=" <?php if(array_key_exists("incident_description", $in_input_errors)) echo 'border: 3px solid red' ?> "><?php if($in_data) echo $in_data['incident_description'] ?>
          </textarea>
          <span class="adm-log-lbl adm-log-err"> <?php if(array_key_exists("incident_description", $in_input_errors)) echo $in_input_errors["incident_description"] ?> </span>
        </div>

        <!-- INCIDENT RESTRICTIONS -->
        <div class="adm-in-half-cont">
          <span class="adm-log-lbl">Incident restrictions (may contain HTML)</span>
          <textarea form="adm-in-form" 
                    class="adm-log-input-gen adm-in-textarea"
                    type="textbox"
                    name="in_restrictions"
                    style=" <?php if(array_key_exists("incident_restrictions", $in_input_errors)) echo 'border: 3px solid red' ?> "><?php if($in_data) echo $in_data['incident_restrictions'] ?>
          </textarea>
          <span class="adm-log-lbl adm-log-err"> <?php if(array_key_exists("incident_restrictions", $in_input_errors)) echo $in_input_errors["incident_restrictions"] ?> </span>
        </div>

        <!-- INCIDENT REGIONS SEARCH -->
        <div class="adm-in-full-cont">
          <span class="adm-log-lbl">Search and add affected regions</span>
          <div class="adm-in-time-cont">
            <input  id="adm-log-regions-input" 
                    list="adm-log-regions-data"
                    class="adm-log-input-gen adm-log-regions-search"
                    style="<?php if(array_key_exists("incident_regions", $in_input_errors)) echo 'border: 3px solid red' ?>"/>
            <input type="button" class="adm-log-input-gen adm-log-regions-button" onclick="add_region()" value="Add region"/>
            <datalist id="adm-log-regions-data">
              <?php

                $i_list = LocationAPI::get_all_regions($conn);

                foreach($i_list as $i)  {
                  echo "<option value='" . $i["pcon_name"] . " (" . $i["pcon_id"] . ")'>";
                }

              ?>
            </datalist>
          </div>
          <span class="adm-log-lbl adm-log-err"> <?php if(array_key_exists("incident_regions", $in_input_errors)) echo $in_input_errors["incident_regions"] ?> </span>
        </div>

        <!-- INCIDENT SELECTED REGIONS VISIBLE -->
        <div class="adm-in-full-cont">
          <span class="adm-log-lbl">Selected regions</span>
          <div class="adm-in-time-cont">
            <select id="adm-log-regions-selected" form="adm-in-form" class="adm-log-input-gen adm-in-textarea" multiple="multiple">
              <?php

                if(isset($in_data["incident_id"]))
                {
                  $i_list = LocationAPI::get_regions_by_incident_id($conn, $in_data["incident_id"]);

                  foreach($i_list as $i)  {
                    echo "<option value='" . $i["pcon_name"] . " (" . $i["pcon_id"] . ")'>" . $i["pcon_name"] . " (" . $i["pcon_id"] . ")</option>";
                  }
                } 

              ?>
            </select>
            <input type="button" class="adm-log-input-gen adm-log-regions-button" onclick="remove_regions()" value="Remove highlighted regions"/>
          </div>

          <!-- INCIDENT SELECTED REGIONS HIDDEN -->
          <!-- Hidden element where all sections are selected and POST'd -->
          <select id="adm-log-regions-selected-hidden" name="in_regions[]" form="adm-in-form" multiple="multiple" style="display:none">
            <?php

              if(isset($in_data["incident_id"]))
              {
                $i_list = LocationAPI::get_regions_by_incident_id($conn, $in_data["incident_id"]);

                foreach($i_list as $i)  {
                  echo "<option value='" . $i["pcon_name"] . " (" . $i["pcon_id"] . ")' selected>" . $i["pcon_name"] . " (" . $i["pcon_id"] . ")</option>";
                }
              } 

            ?>
          </select>

        </div>

        <div class="adm-in-half-cont">
          <a href="/auth/console">Go back (disregard changes)</a>
        </div>

        <div class="adm-in-half-cont">
          <?php echo '<input type="hidden" name="auth_token" value="'.$_SESSION["AUTH_TOKEN"].'"></input>' ?>
          <input class="adm-log-input-gen" type="Submit" value="Save details and go live"/>
        </div>
      </form>

      <script>

        function add_region()
        {
          console.log("Adding region");

          var input               = document.getElementById("adm-log-regions-input");
          var ops                 = document.getElementById("adm-log-regions-data").options;
          var selected_ops        = document.getElementById("adm-log-regions-selected").options;
          var selected_ops_hidden = document.getElementById("adm-log-regions-selected-hidden").options;
          console.log(ops.length + " options")

          /**
           * Loop over all regions and make sure the input is valid
           */
          function is_valid_region()
          {
            for(var i = 0; i < ops.length; i++)
            {
              var o = ops.item(i);
              if(o.value === input.value)
              {
                return true;
              }
            }
            return false;
          }

          /** 
           * Loop over all selected regions and mmake sure the input isn't already selected
           */
          function already_selected()
          {
            for(var i = 0; i < selected_ops.length; i++)
            {
              var o = selected_ops.item(i);
              if(o.value === input.value)
              {
                return true;
              }
            }
            return false;
          }

          // If the region is valid and not already selected, then add it
          if(is_valid_region() && !already_selected())
          {
            console.log("Valid");
            var op = new Option(input.value, input.value);
            selected_ops.add(op, undefined);
            
            var op_hidden = new Option(input.value, input.value, true, true);
            selected_ops_hidden.add(op_hidden, undefined);
            selected_ops_hidden[selected_ops_hidden.length-1].selected = true;
            
            input.value = "";
          }
          else
          {
            console.log("Invalid");
          }

        }

        function remove_regions()
        {
          var ops = document.getElementById("adm-log-regions-selected").options;
          var selected_ops_hidden = document.getElementById("adm-log-regions-selected-hidden").options;
          console.log(ops.length + " options")

          for(var i = ops.length-1; i >= 0; i--)
          {
            var o = ops.item(i);
            if(o.selected)
            {
              console.log("Option selected");
              ops.remove(i);
              selected_ops_hidden.remove(i);
            }
          }
        }

      </script>

    </main>

    <footer>

      <?php include 'public_footer.php'; ?>

    </footer>

  </body>

</html>