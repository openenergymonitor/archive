<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  function user_controller()
  {
    $form = $_POST['form'];

    if ($form == 'login')
    {
      $username = db_real_escape_string($_POST['username']);
      $password = db_real_escape_string($_POST['pass1']);
      $result = user_logon($username,$password);
      if ($result == 0) $content = "Invalid username or password"; else $show_register_box = 0;
    } 

    if ($form == 'logout') user_logout();

    if ($form == 'register')
    {
      $username = db_real_escape_string($_POST['username']);
      $pass1 = db_real_escape_string($_POST['pass1']);

      $error = '';
      if (get_user_id($username)!=0) $error .= "Username already exists<br/>";
      if (strlen($username) < 4 || strlen($username) > 30) $error .= "Username must be between 4 and 30 characters long<br/>";
      if (strlen($pass1) < 4 || strlen($pass1) > 30) $error .= "Passwords must be between 4 and 30 characters long<br/>";

      $content = $error;
      if (!$error) {
        create_user($username,$pass1);
        $result = user_logon($username,$pass1);
      }
        
    }
    if (!$_SESSION['valid']) $content .= view("user/login.php", array());
    if ($_SESSION['valid']) $content = controller('home');

    return $content;
  }

?>
