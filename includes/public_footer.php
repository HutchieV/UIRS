<div class="pub-footer-cont">
  <div class="pub-footer-f-cont">
    <div class="pub-footer-item">
      <span class="pub-footer-subheader">Site map</span>
      <a href="/">Home</a><br>
      <a href="/auth/login">Login</a><br>
      <a href="/licenses">Licenses</a>
    </div>

    <?php 
      if(TokenAPI::verify_session($conn, false))  {
        echo '<div class="pub-footer-item">
                <span class="pub-footer-subheader">Admin</span>
                <span>Logged in as ' . $_SESSION["USER"]["user_username"]. '</span><br>
                <a href="/auth/login">Admin Home</a><br>
                <form action="/auth/logout" method="POST">
                  <input type="hidden" name="auth_token" value="'.$_SESSION["AUTH_TOKEN"].'"></input>
                  <input class="adm-logout-btn" type="submit" value="Logout"></input>
                <form>
              </div>'; 
      }
    ?>

    <div class="pub-footer-item">
      <span class="pub-footer-subheader">Copyright</span>
      Created by and © <a href="https://github.com/BCJMilne">Ben Milne</a> (<a href="/licenses">license exceptions</a>) as a dissertation project for <a href="https://www.hw.ac.uk">Heriot-Watt University</a> (2020-21)
    </div>

  </div>
</div>