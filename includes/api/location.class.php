<?php

  class LocationAPI
  {

    /**
     * Validate a given postcode.
     * 
     * @param  string $pc The input postcode
     * @return string|null Returns the postcode if validated, null otherwise 
     */
    static function validate_postcode($pc)
    {
      $pc = strip_tags($pc);            // Remove dangerous tags
      $pc = htmlspecialchars($pc);      // Escape any remaining special characters
      $pc = str_replace(' ', '', $pc);  // Remove spaces
  
      if(strlen($pc) > 8 || strlen($pc) < 5) {
        return null;
      }
  
      $pc = strtoupper($pc);
  
      return $pc;
    }


    /**
     * Get postcode data for a given postcode.
     * 
     * @param $pc The input postcode
     * @return array|null Returns the postcode data if found, null otherwise
     */
    static function get_postcode_by_postcode($conn, $pc)
    {
      $pc = self::validate_postcode($pc);
      if(!$pc) return null;

      $q = $conn->prepare(' SELECT * 
                            FROM postcode
                            WHERE postcode_id=:pc
                            LIMIT 1');
      $q->bindValue(':pc', $pc);
      $q->execute();
      
      return $q->fetch();
    }


    /**
     * Get the region a postcode is located in.
     * Validates postcode before querying.
     * 
     * @param  object $conn An open PDO database connection
     * @param  string $pc The input postcode
     * @return array|null Returns the region data if found, null otherwise
     */
    static function get_region_by_postcode($conn, $pc)
    {
      $pc = self::validate_postcode($pc);
      if(!$pc) return null;

      $q = $conn->prepare(' SELECT pcon.* 
                            FROM pcon
                            INNER JOIN postcode ON postcode.pcon_id=pcon.pcon_id 
                            WHERE postcode.postcode_id=:pc
                            LIMIT 1');
      $q->bindValue(':pc', $pc);
      $q->execute();
      
      return $q->fetch();
    }


    /**
     * Get region details by its region id.
     * 
     * @param  object $conn An open PDO database connection
     * @param  string $r The input region id
     * @return array|null Returns the region data if found, null otherwise
     */
    static function get_region_by_region_id($conn, $rid)
    {
      $q = $conn->prepare(' SELECT * 
                            FROM pcon
                            WHERE pcon_id=:r
                            LIMIT 1');
      $q->bindValue(':r', $rid);
      $q->execute();

      return $q->fetch();
    }

    /**
     * Get incident details by region id.
     * 
     * @param  object $conn An open PDO database connection
     * @param  string $r The input region id
     * @return array|null Returns incident data if found, null otherwise
     */
    static function get_incidents_by_region_id($conn, $rid)
    {
      $q = $conn->prepare(' SELECT incident.* 
                            FROM incident
                            INNER JOIN incident_location ON incident_location.incident_id=incident.incident_id
                            WHERE incident_location.pcon_id=:rid');
      $q->bindValue(':rid', $rid);
      $q->execute();
      
      return $q->fetchAll();
    }

  }

?>