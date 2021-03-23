<?php
  require '/UIRS/includes/gen_config.php';

  $rid          = null;
  $r_data       = null;
  $email_error  = null;

  $sub_rid      = null;
  $sub_token    = null;
  $sub_ref      = null;
  $sub_done     = false;

  if(isset($_POST["region"]))
  {
    // User has submitted an email

    try
    {
      $sub_rid = sanitize_input($_POST["region"]);
      if(!(LocationAPI::get_region_by_region_id($conn, $sub_rid))) throw new CreateSubscriptionException("A system error occured (002), please try again");
      $email_error = "A system error occured (002), please try again";

      $sub_email = sanitize_input($_POST["email"]);
      if(!filter_var($sub_email, FILTER_VALIDATE_EMAIL)) throw new CreateSubscriptionException("Please enter a valid email address");
      $email_error = "Please enter a valid email address";

      $sub_token = TokenAPI::get_hash($sub_email);

      // If this email is already in the DB...
      if($sub_row = DBAPI::get_subscription($conn, null, $sub_email))
      {
        // Get the regions it is subscribed to...
        $ex_sub_regions = DBAPI::get_sub_regions($conn, null, $sub_email);
        foreach($ex_sub_regions as $r)
        {
          if($r["pcon_id"] == $sub_rid)
          {
            // If it is already subscribed to this region, throw an error
            throw new CreateSubscriptionException("This email address is already subscribed to updates from this region");
          }
        }
      } 
      // If it is not already in the DB, add the sub
      else
      {
        $sub_row = DBAPI::insert_subscription($conn, $sub_token, $sub_email);
        if(!($sub_row)) throw new CreateSubscriptionException("A system error occured (003), please try again");

        $sub_ref = $sub_row["sub_id"] . "-" . substr($sub_token, -5);

        // MailAPI::send_verification_email($conn, $sub_ref, $sub_row);
      }
      
      $sub_link_id = DBAPI::insert_sub_region($conn, $sub_row["sub_id"], $sub_rid);
      $sub_done = true;

    } catch(CreateSubscriptionException $e) {
      $email_error = $e->message();
    } catch(PDOException $e)  {
      $email_error = "A system error occured (003), please try again";
    } catch(DatabaseConnException $e) {
      $UIRS_FATAL_ERROR = "The system is experiencing technical difficulties. We apologise for the inconvenience. (E002)";
    }
  }
  if(isset($_GET["r"]))
  {
    // User has been directed to this page from index
    $rid = sanitize_input($_GET["r"]);

    try
    {
      $r_data = LocationAPI::get_region_by_region_id($conn, $rid);
      if(!$r_data) throw new ProcessPostcodeException("A system error occured (001), please try again");
    
    } catch(ProcessPostcodeException $e) {
      $email_error = $e->message();
    } catch(PDOException $e)  {
      $email_error += "A system error occured (003), please try again";
    } catch(DatabaseConnException $e) {
      $UIRS_FATAL_ERROR = "The system is experiencing technical difficulties. We apologise for the inconvenience. (E003)";
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
      UIRS | Subscribe to email notifications
    </title>

  </head>

  <body>

    <nav>

      <?php include 'public_nav.php'; ?>
    
    </nav>

    <main class="pub-main">

    <?php include 'public_error_banner.php'; ?>

    <section class="pub-desc pub-underline" <?php echo (($sub_done) ? "style=display:block;" : "style=display:none;") ?>>
      <span class="pub-i-m-subtitle">Subscribe to email notifications</span>

      You have successfully subscribed to email notifications for the <strong><?php if(isset($r_data)) echo $r_data["pcon_name"] ?></strong> region.
      <br><br>
      <?php if($sub_ref) echo "As this is your first subscription, your reference is <strong>".$sub_ref."</strong>. Please remember to verify your email address.";?> 
    </section>

    <section class="pub-desc" <?php echo ((!$sub_done && !$rid) ? "style=display:block;" : "style=display:none;") ?>>
      <span class="pub-i-m-subtitle">Subscribe to email notifications</span>

      It looks like we have encountered a technical error, or you have entered this page from the wrong source.
      Please <a href="/">click here</a> to return to the system homepage.
    </section>

    <section class="pub-desc pub-underline" <?php if($sub_done) echo "style=display:none;"?>>
      <span class="pub-i-m-subtitle">Subscribe to email notifications</span>

      <label for="pub-c-p-input">Enter your email address below to receive incident updates for the <strong><?php if(isset($r_data)) echo $r_data["pcon_name"] ?></strong> region.</label><br><br>
      <strong>You will receive an email asking you to confirm your subscription, please click the link in the email to confirm
      you wish to receive updates.</strong>
    </section>

    <section class="pub-desc pub-underline" <?php if($sub_done) echo "style=display:none;"?>>
      <section class="pub-email-icons-cont">

        <div class="pub-email-icons-row pub-email-icons-row-form">
          <form class="pub-email-form" method="POST">
            <input type="hidden" name="region" value="<?php if(isset($rid)) echo $rid; ?>"></input>
            <input  id="pub-c-p-input" 
                    type="email" 
                    placeholder="john.doe@example.com" 
                    name="email"
                    <?php if($email_error) echo "style='border: 2px solid red;'" ?>></input>
            <input id="pub-c-p-submit" type="submit" Value="Submit"></input>
          </form>
        </div>

        <div class="pub-email-icons-row" <?php echo ($email_error ? "style=display:block;" : "style=display:none;") ?>>
          <label class="adm-log-err"><?php if($email_error) echo $email_error ?></label>
        </div>

        <div class="pub-email-icons-row">
          <!-- <div class="pub-email-icon-cont"><img class="pub-email-icon" src="/content/images/icon_enter.svg"/></div> -->
          <div class="pub-email-icon-cont">✉</div>
          <div class="pub-email-icon-desc">1. Enter your email address</div>
        </div>
      
        <div class="pub-email-icons-row">
          <!-- <div class="pub-email-icon-cont"><img class="pub-email-icon" src="/content/images/icon_verify.svg"/></div> -->
          <div class="pub-email-icon-cont">🔗</div>
          <div class="pub-email-icon-desc">2. We'll send you a link, click it to verify your address</div>
        </div>

        <div class="pub-email-icons-row">
          <!-- <div class="pub-email-icon-cont"><img class="pub-email-icon" src="/content/images/icon_done.svg"/></div> -->
          <div class="pub-email-icon-cont">✔</div>
          <div class="pub-email-icon-desc">3. You'll be set to receive updates on incidents in your area</div>
        </div>
      </section>
    </section>
  
    <section class="pub-desc pub-desc-contd">
      <span class="pub-i-m-subtitle">Rights and privacy</span>

      If you provide us with your email address we will only use it to share updates in your chosen regions.<br><br>
      Once you have submitted your email address, you will be provided with a unique ID that you will be required to provide if you
      wish you exercise your rights under <a href="https://eur-lex.europa.eu/legal-content/EN/TXT/PDF/?uri=CELEX:32016R0679">GDPR</a>, which includes
      the right to view, update and erase your data. Alternatively, every email we send will include a link to automatically unsubscribe from this service.
      <br><br>
      If you wish to get in touch about the service, including exercising your rights under GDPR, please contact us via <a href="mailto:contact@uirs.org">contact@uirs.org</a>.
    </section>

    </main>

    <footer>

      <?php include 'public_footer.php'; ?>

    </footer>

  </body>

</html>