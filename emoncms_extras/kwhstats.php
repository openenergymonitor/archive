<?php

  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

  //----------------------------------------------------------------------
  // kWh/d statistics calculation script
  //
  // Calculates last 365, 30, 7 day average
  //
  // Places these stats in the feeds table
  // which has the following fields:
  //
  // feedid | time | value | today | yesterday | week | month | year
  //----------------------------------------------------------------------

  require "../emoncore/Includes/db.php";
  $e = db_connect();
  require "../emoncore/Models/feed_model.php";

  calc_feed_stats(1);
  // repeat for every feed you want to process
?>
