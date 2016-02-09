<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  function user_block_controller()
  {
    if ($_SESSION['valid']) {
      $name = get_user_name($_SESSION['userid']);
      $content = view("user/account_block.php", array('name' => $name));
    }

    return $content;
  }

?>
