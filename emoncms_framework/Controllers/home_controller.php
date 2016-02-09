<?php 
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  function home_controller()
  {
    if ($_SESSION['valid'])
    {
      $content = view("home_view.php", array());
    } 
    else 
    {
      $content = "You need to be logged in to view this page";
    }

    return $content;
  }


?>
