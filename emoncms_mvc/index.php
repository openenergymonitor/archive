<?php

  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org

    Author: Trystan Lea: trystan.lea@googlemail.com
    If you have any questions please get in touch, try the forums here:
    http://openenergymonitor.org/emon/forum
  */

  session_start();

  //error_reporting(E_ALL);
  ini_set('display_errors','off');

  $path = dirname("http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'])."/";

  require "Includes/core.inc.php";
  require "Includes/db.php";
  require "Models/user_model.php";

  $args = explode('/', $_GET['q']);
  if (!$args[0]) $args[0] = "home";

  $e = db_connect();
  if ($e == 2) {echo "no settings.php"; die;}
  if ($e == 3) {echo "db settings error"; die;}
  if ($e == 4) require "Includes/setup.php";

  if ($args[0]=='api') 
  {
    print controller('api');
  }
  else
  {
    $user = controller('user_block');
    $menu = controller('menu_block');
    $content = controller($args[0]);
    if (!$content) $content = "no content";
    print view("theme/theme.php", array('menu' => $menu, 'user' => $user, 'content' => $content));
  }


?>
