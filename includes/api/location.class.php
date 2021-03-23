<?php

  class LocationAPI
  {

    public static function conn_test($conn) {
      if (!isset($conn)) throw new DatabaseConnException("Conn was not set");
    }

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
     * Validate a given incident id.
     * 
     * @param  string $pc The input incident id
     * @return string|null Returns the incident id if validated, null otherwise 
     */
    static function validate_incident_id($pc)
    {
      if(!(isset($_GET["i"]))) return null;

      $in_req_id = strip_tags($_GET["i"]);            // Remove dangerous tags
      $in_req_id = htmlspecialchars($in_req_id);      // Escape any remaining special characters
      $in_req_id = str_replace(' ', '', $in_req_id);  // Remove spaces
      
      return $in_req_id;
    }


    /**
     * Get postcode data for a given postcode.
     * 
     * @param $pc The input postcode
     * @return array|null Returns the postcode data if found, null otherwise
     */
    static function get_postcode_by_postcode($conn, $pc)
    {
      self::conn_test($conn);

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
      self::conn_test($conn);

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
     * @param  string $rid The input region id
     * @return array|null Returns the region data if found, null otherwise
     */
    static function get_region_by_region_id($conn, $rid)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' SELECT * 
                            FROM pcon
                            WHERE pcon_id=:r
                            LIMIT 1');
      $q->bindValue(':r', $rid);
      $q->execute();

      return $q->fetch();
    }

    /**
     * Get all region details for a given incident.
     * 
     * @param  object $conn An open PDO database connection
     * @param  string $iid The input incident id
     * @return array|null Returns the region data if found, null otherwise
     */
    static function get_regions_by_incident_id($conn, $iid)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' SELECT pcon.* 
                            FROM pcon
                            INNER JOIN incident_location ON incident_location.pcon_id=pcon.pcon_id
                            INNER JOIN incident ON incident.incident_id=incident_location.incident_id
                            WHERE incident.incident_id=:iid');
      $q->bindValue(':iid', $iid);
      $q->execute();

      return $q->fetchAll();
    }

    static function insert_incident($conn, $title_short, $title_long, $active, $level, $start_timestamp, $end_timestamp,
                                    $description, $restrictions, $regions, $org_id, $lat=null, $long=null)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' INSERT INTO incident 
                              (
                                incident_title_short,
                                incident_title_long,
                                incident_date,
                                incident_active,
                                incident_level,
                                incident_restrictions,
                                incident_description,
                                incident_start,
                                incident_end,
                                incident_last_updated,
                                incident_lat,
                                incident_long,
                                org_id
                              )
                              VALUES
                              (
                                :title_short,
                                :title_long,
                                NOW(),
                                :active,
                                :level,
                                :restrictions,
                                :description,
                                :start_timestamp,
                                :end_timestamp,
                                NOW(),
                                :lat,
                                :long,
                                :org_id
                              )');

      $q->bindValue(':title_short',     $title_short);
      $q->bindValue(':title_long',      $title_long);
      $q->bindValue(':active',          $active);
      $q->bindValue(':level',           $level);
      $q->bindValue(':restrictions',    $restrictions);
      $q->bindValue(':description',     $description);
      $q->bindValue(':start_timestamp', $start_timestamp);
      $q->bindValue(':end_timestamp',   $end_timestamp);
      $q->bindValue(':lat',             $lat);
      $q->bindValue(':long',            $long);
      $q->bindValue(':org_id',          $org_id);
      $q->execute();

      $iid = $conn->lastInsertId();
      // echo "Last insert id: " . $iid;

      self::delete_all_incident_locations_by_incident($conn, $iid);
      foreach($regions as $rid)
      {
        self::insert_incident_location($conn, $iid, $rid);
      }
    }

    static function update_incident( $conn, $title_short, $title_long, $active, $level, $start_timestamp, $end_timestamp,
                                            $description, $restrictions, $regions, $org_id, $iid, $lat=null, $long=null)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' UPDATE incident SET 
                            incident_title_short=:title_short,
                            incident_title_long=:title_long,
                            incident_active=:active,
                            incident_level=:level,
                            incident_restrictions=:restrictions,
                            incident_description=:description,
                            incident_start=:start_timestamp,
                            incident_end=:end_timestamp,
                            incident_last_updated=NOW(),
                            incident_lat=:lat,
                            incident_long=:long,
                            org_id=:org_id
                            WHERE incident_id=:iid');
      $q->bindValue(':title_short',     $title_short);
      $q->bindValue(':title_long',      $title_long);
      $q->bindValue(':active',          $active);
      $q->bindValue(':level',           $level);
      $q->bindValue(':restrictions',    $restrictions);
      $q->bindValue(':description',     $description);
      $q->bindValue(':start_timestamp', $start_timestamp);
      $q->bindValue(':end_timestamp',   $end_timestamp);
      $q->bindValue(':lat',             $lat);
      $q->bindValue(':long',            $long);
      $q->bindValue(':org_id',          $org_id);
      $q->bindValue(':iid',             $iid);
      $q->execute();
      // echo "Last insert id: " . $conn->lastInsertId();

      self::delete_all_incident_locations_by_incident($conn, $iid);
      foreach($regions as $rid)
      {
        self::insert_incident_location($conn, $iid, $rid);
      }
    }

    static function delete_all_incident_locations_by_incident($conn, $iid)
    {
      self::conn_test($conn);

      $q = $conn->prepare('DELETE FROM incident_location WHERE incident_id=:iid');
      $q->bindValue(':iid', $iid);
      $q->execute();
    }

    static function insert_incident_location($conn, $iid, $rid)
    {
      self::conn_test($conn);

      $q = $conn->prepare('INSERT INTO incident_location VALUES (:iid, :rid)');
      $q->bindValue(':iid', $iid);
      $q->bindValue(':rid', $rid);
      $q->execute();

      return $conn->lastInsertId();
    }

    /**
     * Get incident details by region id.
     * Also returns related org data.
     * 
     * @param  object $conn An open PDO database connection
     * @param  string $rid The input region id
     * @return array|null Returns incident data if found, null otherwise
     */
    static function get_incidents_by_region_id($conn, $rid)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' SELECT incident.*, organisation.*
                            FROM incident
                            INNER JOIN incident_location ON incident_location.incident_id=incident.incident_id
                            INNER JOIN organisation ON incident.org_id=organisation.org_id
                            WHERE incident_location.pcon_id=:rid
                            ORDER BY incident_level DESC');
      $q->bindValue(':rid', $rid);
      $q->execute();
      
      return $q->fetchAll();
    }

    /**
     * Get incident details by incident id.
     * Also returns related org data.
     * 
     * @param  object $conn An open PDO database connection
     * @param  string $iid The input incident id
     * @return array|null Returns incident data if found, null otherwise
     */
    static function get_incident_by_incident_id($conn, $iid)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' SELECT incident.*, organisation.*
                            FROM incident
                            INNER JOIN organisation ON incident.org_id=organisation.org_id
                            WHERE incident.incident_id=:iid
                            LIMIT 1');
      $q->bindValue(':iid', $iid);
      $q->execute();
      
      return $q->fetch();
    }

    /**
     * Get org details by incident id.
     * 
     * @param  object $conn An open PDO database connection
     * @param  string $iid The input region id
     * @return array|null Returns org data if found, null otherwise
     */
    static function get_org_by_incident_id($conn, $iid)
    {
      self::conn_test($conn);

      $q = $conn->prepare(' SELECT organisation.* 
                            FROM organisation
                            INNER JOIN incident ON incident.org_id=organisation.org_id
                            WHERE incident.incident_id=:iid');
      $q->bindValue(':iid', $iid);
      $q->execute();
      
      return $q->fetch();
    }

    /**
     * Get a list of all regions.
     * 
     * @param object $conn An open PDO database connection
     * @return array Returns an array of all regions
     */
    static function get_all_regions($conn)
    {
      self::conn_test($conn);
      
      $q = $conn->prepare('SELECT * FROM pcon');
      $q->execute();
      
      return $q->fetchAll();
    }

  }

?>