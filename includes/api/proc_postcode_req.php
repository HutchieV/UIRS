<?php

  $pc_error_msg = null;
  $pc           = null;
  $pc_data      = null;

  function proc_postcode(&$pc_error_msg, &$pc)  {

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

  if($_POST)  {

    if($_POST["postcode"])  {

      try {
        if(proc_postcode($pc_error_msg, $pc)) {
          $q = $conn->prepare(' SELECT      postcode.postcode_id, postcode.postcode_lat, postcode.postcode_long, 
                                            postcode.pcon_id, pcon.pcon_id, pcon_name
                                FROM        postcode
                                INNER JOIN  pcon ON postcode.pcon_id=pcon.pcon_id
                                WHERE       postcode.postcode_id=:pc');
          $q->bindParam(':pc', $pc);
          $q->execute();

          $result = $q->setFetchMode(PDO::FETCH_ASSOC);
          foreach($q->fetchAll() as $k=>$v) {
            $pc_data = $v;
          }
          if(!($pc_data)) {
            $pc_error_msg = "Error: Unknown postcode";
          }
        };
      } catch(PDOException $e)  {
        $pc_error_msg = "Error: System error occured";
      }

    }

  }

?>