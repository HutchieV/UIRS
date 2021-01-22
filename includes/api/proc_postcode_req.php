<?php

  $pc_error_msg = null;
  $pc           = null;
  $pc_data      = null;
  $in_count     = null;
  $in_data      = null;

  function proc_postcode(&$pc_error_msg, &$pc)  {
    /*
      Validate and sanitise the postcode input
    */

    $pc = strip_tags($_POST["postcode"]); // Remove dangerous tags
    $pc = htmlspecialchars($pc);          // Escape any remaining special characters
    $pc = str_replace(' ', '', $pc);      // Remove spaces

    if(strlen($pc) > 8 || strlen($pc) < 5) {
      $pc_error_msg = "Error: Invalid postcode";
      return false;
    }

    $pc = strtoupper($pc);

    return true;
  }

  function get_region($conn, &$pc_error_msg, &$pc, &$pc_data)  {
    /*
      Request postcode and region data
    */

    if(proc_postcode($pc_error_msg, $pc)) {
      $q = $conn->prepare(' SELECT      postcode.postcode_id, postcode.postcode_lat, postcode.postcode_long, 
                                        postcode.pcon_id, pcon.pcon_id, pcon_name
                            FROM        postcode
                            INNER JOIN  pcon              ON postcode.pcon_id=pcon.pcon_id
                            WHERE       postcode.postcode_id=:pc');
      $q->bindParam(':pc', $pc);
      $q->execute();

     $q->setFetchMode(PDO::FETCH_ASSOC);
      foreach($q->fetchAll() as $k=>$v) {
        $pc_data = $v;
      }
    };
  }

  function get_active_incidents($conn, &$pc_error_msg, &$pc, &$pc_data, &$in_count, &$in_data) {
    /*
      Request active incident data for the region
    */

    if(proc_postcode($pc_error_msg, $pc)) {
      $q = $conn->prepare(' SELECT      incident.incident_id, incident.incident_date, incident.incident_title_short, 
                                        incident.incident_restrictions, incident.incident_start, incident.incident_end,
                                        incident.incident_lat, incident.incident_long, organisation.org_title
                            FROM        pcon
                            INNER JOIN  incident_location ON pcon.pcon_id=incident_location.pcon_id
                            INNER JOIN  incident          ON incident_location.incident_id=incident.incident_id
                            INNER JOIN  organisation      ON incident.org_id=organisation.org_id
                            WHERE       pcon.pcon_id=:pcon_id AND
                                        incident.incident_active=1');
      $q->bindParam(':pcon_id', $pc_data["pcon_id"]);
      $q->execute();

      // echo $q->rowCount() . " for region " . $pc_data["pcon_id"];
      $in_count = $q->rowCount();

      $q->setFetchMode(PDO::FETCH_ASSOC);
      foreach($q->fetchAll() as $k=>$v) {
        $in_data[$v["incident_id"]] = $v;
      }
    };
  }

  if($_POST)  {

    if($_POST["postcode"])  {

      try {
        get_region($conn, $pc_error_msg, $pc, $pc_data);

        if(!($pc_data)) {
          $pc_error_msg = "Error: Unknown postcode";
        } else  {
          get_active_incidents($conn, $pc_error_msg, $pc, $pc_data, $in_count, $in_data);
        }
      } catch(PDOException $e)  {
        $pc_error_msg = "Error: System error occured";
      }

    }

  }

?>