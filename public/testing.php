<?php

  // Non-production error reporting
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  set_include_path("/UIRS/includes/");
  require 'api/db.class.php';
  require 'api/token.class.php';
  require 'api/location.class.php';

  // echo "<strong>Notice:</strong> Error printing enabled for this page.<br><br>";

?>

<!DOCTYPE html>

<html lang="en">

  <head>

    <style>

      :root {
        border-style: border-box;
        font-family:  Helvetica, Arial, sans-serif
      }

      * {
        padding: 0;
        margin:  0;
      }

      body {
        background-color: black;
      }

      table {
        width:          80%;
        margin:         2.5rem;
        margin-left:    auto;
        margin-right:   auto;
        text-align:     center;
        border:         1px solid black;
      }

      table tr {
        display:          flex;
        justify-content:  space-evenly;
      }

      table th {
        width:            100%;
        border:           1px solid black;
        padding:          0.5rem;
        background-color: grey;
        color:            white;
      }

      table td {
        width:            100%;
        border:           1px solid black;
        background-color: white;
        padding:          0.5rem;
      }

    </style>
    
  </head>

  <body>

    <table>

      <tr><th colspan=4><h2>Database Testing</h2></th></tr>
      <tr><th><h3>Test</h3></th><th><h3>Test Data</h3></th><th><h3>Expected</h3></th><th><h3>Actual</h3></th></tr>
      <tr><th colspan=4><h3>get_db_conn</h3></th></tr>

      <tr>
        <td>Get a connection object</td>
        <td>n/a</td>
        <td>true</td>
        <td>
          <?php 
            $conn = DBAPI::get_db_conn();
            echo (!($conn) ? 'false' : 'true');
          ?>
        </td>
      </tr>  

    </table>

    <table>

      <tr><th colspan=4><h2>Location Testing</h2></th></tr>
      <tr><th><h3>Test</h3></th><th><h3>Test Data</h3></th><th><h3>Expected</h3></th><th><h3>Actual</h3></th></tr>
      <tr><th colspan=4><h3>validate_postcode</h3></th></tr>

      <tr>
        <td>No input</td>
        <td></td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::validate_postcode("")) ? 'false' : 'true');
          ?>
        </td>
      </tr>

      <tr>
        <td>Short postcode</td>
        <td>EH</td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::validate_postcode("EH")) ? 'false' : 'true');
          ?>
        </td>
      </tr>  

      <tr>
        <td>Invalid characters postcode</td>
        <td>&lt;html></td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::validate_postcode("<html>")) ? 'false' : 'true');
          ?>
        </td>
      </tr>

      <tr>
        <td>Long postcode</td>
        <td>EH1590284</td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::validate_postcode("EH1590284")) ? 'false' : 'true');
          ?>
        </td>
      </tr>

      <tr>
        <td>Valid, non-existent postcode</td>
        <td>XXXXXX</td>
        <td>true</td>
        <td>
          <?php 
            echo (!(LocationAPI::validate_postcode("XXXXXX")) ? 'false' : 'true');
          ?>
        </td>
      </tr>

      <tr>
        <td>Valid, existing postcode</td>
        <td>E50AA</td>
        <td>true</td>
        <td>
          <?php 
            echo (!(LocationAPI::validate_postcode("E50AA")) ? 'false' : 'true');
          ?>
        </td>
      </tr>

    <!-- =============================================================================== -->
    <tr><th colspan=3><h3>get_postcode_by_postcode</h3></th></tr>

    <tr>
      <td>No input</td>
      <td></td>
      <td>false</td>
      <td>
        <?php 
          echo (!(LocationAPI::get_postcode_by_postcode($conn, "")) ? 'false' : 'true');
        ?>
      </td>
    </tr>

    <tr>
      <td>Short postcode</td>
      <td>EH</td>
      <td>false</td>
      <td>
        <?php 
          echo (!(LocationAPI::get_postcode_by_postcode($conn, "EH")) ? 'false' : 'true');
        ?>
      </td>
    </tr>  

    <tr>
      <td>Invalid characters postcode</td>
      <td>&lt;html></td>
      <td>false</td>
      <td>
        <?php 
          echo (!(LocationAPI::get_postcode_by_postcode($conn, "<html>")) ? 'false' : 'true');
        ?>
      </td>
    </tr>

    <tr>
      <td>Long postcode</td>
      <td>EH1590284</td>
      <td>false</td>
      <td>
        <?php 
          echo (!(LocationAPI::get_postcode_by_postcode($conn, "EH1590284")) ? 'false' : 'true');
        ?>
      </td>
    </tr>

    <tr>
      <td>Valid, non-existent postcode</td>
      <td>XXXXXX</td>
      <td>false</td>
      <td>
        <?php
          $r = LocationAPI::get_postcode_by_postcode($conn, "XXXXXX");
          echo (!($r) ? 'false' : print_r($r));
        ?>
      </td>
    </tr>

    <tr>
      <td>Valid, existing postcode</td>
      <td>E50AA</td>
      <td>array</td>
      <td>
        <?php
          $r = LocationAPI::get_postcode_by_postcode($conn, "E50AA");
          echo (!($r) ? 'false' : print_r($r));
        ?>
      </td>
    </tr>

      <!-- =============================================================================== -->
      <tr><th colspan=3><h3>get_region_by_postcode</h3></th></tr>

      <tr>
        <td>No input</td>
        <td></td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::get_region_by_postcode($conn, "")) ? 'false' : 'true');
          ?>
        </td>
      </tr>

      <tr>
        <td>Short postcode</td>
        <td>EH</td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::get_region_by_postcode($conn, "EH")) ? 'false' : 'true');
          ?>
        </td>
      </tr>  

      <tr>
        <td>Invalid characters postcode</td>
        <td>&lt;html></td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::get_region_by_postcode($conn, "<html>")) ? 'false' : 'true');
          ?>
        </td>
      </tr>

      <tr>
        <td>Long postcode</td>
        <td>EH1590284</td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::get_region_by_postcode($conn, "EH1590284")) ? 'false' : 'true');
          ?>
        </td>
      </tr>

      <tr>
        <td>Valid, non-existent postcode</td>
        <td>XXXXXX</td>
        <td>false</td>
        <td>
          <?php
            $r = LocationAPI::get_region_by_postcode($conn, "XXXXXX");
            echo (!($r) ? 'false' : print_r($r));
          ?>
        </td>
      </tr>

      <tr>
        <td>Valid, existing postcode</td>
        <td>E50AA</td>
        <td>array</td>
        <td>
          <?php
            $r = LocationAPI::get_region_by_postcode($conn, "E50AA");
            echo (!($r) ? 'false' : print_r($r));
          ?>
        </td>
      </tr>

      <!-- =============================================================================== -->
      <tr><th colspan=3><h3>get_region_by_region_id</h3></th></tr>

      <tr>
        <td>No input</td>
        <td></td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::get_region_by_region_id($conn, "")) ? 'false' : 'true');
          ?>
        </td>
      </tr>

      <tr>
        <td>Invalid characters / region</td>
        <td>EH74839&lt;html></td>
        <td>false</td>
        <td>
          <?php 
            echo (!(LocationAPI::get_region_by_region_id($conn, "EH74839<html>")) ? 'false' : 'true');
          ?>
        </td>
      </tr>  

      <tr>
        <td>Valid, non-existent region</td>
        <td>E99999999</td>
        <td>false</td>
        <td>
          <?php
            $r = LocationAPI::get_region_by_region_id($conn, "E99999999");
            echo (!($r) ? 'false' : print_r($r));
          ?>
        </td>
      </tr>

      <tr>
        <td>Valid, existing region</td>
        <td>E14000720</td>
        <td>array</td>
        <td>
          <?php
            $r = LocationAPI::get_region_by_region_id($conn, "E14000720");
            echo (!($r) ? 'false' : print_r($r));
          ?>
        </td>
      </tr>

        <!-- =============================================================================== -->
        <tr><th colspan=3><h3>get_incidents_by_region_id</h3></th></tr>

    <tr>
      <td>No input</td>
      <td></td>
      <td>false</td>
      <td>
        <?php 
          echo (!(LocationAPI::get_incidents_by_region_id($conn, "")) ? 'false' : 'true');
        ?>
      </td>
    </tr>

    <tr>
      <td>Invalid characters / region</td>
      <td>EH74839&lt;html></td>
      <td>false</td>
      <td>
        <?php 
          echo (!(LocationAPI::get_incidents_by_region_id($conn, "EH74839<html>")) ? 'false' : 'true');
        ?>
      </td>
    </tr>  

    <tr>
      <td>Valid, non-existent region</td>
      <td>E99999999</td>
      <td>false</td>
      <td>
        <?php
          $r = LocationAPI::get_incidents_by_region_id($conn, "E99999999");
          echo (!($r) ? 'false' : print_r($r));
        ?>
      </td>
    </tr>

    <tr>
      <td>Valid, existing region without incidents</td>
      <td>E14000565</td>
      <td>false</td>
      <td>
        <?php
          $r = LocationAPI::get_incidents_by_region_id($conn, "E14000565");
          echo (!($r) ? 'false' : print_r($r));
        ?>
      </td>
    </tr>

    <tr>
      <td>Valid, existing region with incidents</td>
      <td>S14000025</td>
      <td>array</td>
      <td>
        <?php
          $r = LocationAPI::get_incidents_by_region_id($conn, "S14000025");
          echo (!($r) ? 'false' : print_r($r));
        ?>
      </td>
    </tr>

    </table>

  </body>

</html>