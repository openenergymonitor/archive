<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  function woodfire_view($catid, $relation)
  {

    $userid = $_SESSION['userid'];
    $variables['title'] = "Wood Fire";

    // Description of category
    $out = "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<p>A cord of wood is a wood-pile that is 16 foot long by 4 foot high by 2 foot
wide. A cord of wood weights approximately 1089kg. If the wood has a moisture content of 20% then the energy contained in 1089kg of wood is roughly 18GJ. This equates to 5000kWh per cord. Or 14kWh/d if you use one cord a year.</p>";

    // Get category data
    $result = db_query("SELECT * FROM cat_firewood WHERE userid='$userid'");
    $row = mysql_fetch_array($result);

    $cord = 0;	//define to avoid notice

    // On recieve of form submit data
    if (isset($_POST['kwh'])) 
    {
      $cord = $_POST['kwh'];	
      $kwhd = $cord*14.0;
      if ($row)
      {
        db_query("UPDATE cat_firewood SET cord = '$cord' WHERE userid = '$userid'"); // Check if category exists
        if ($relation)
        {
          $share = $kwhd/2.0;
          db_query("UPDATE cat_relation SET kwhd = '$share' WHERE userid = '$userid' AND catid = '$catid'"); // Check if category exists
        }
      }
      else
      {
         db_query("INSERT INTO cat_firewood (userid,cord) VALUES ('$userid','$cord')");
         db_query("INSERT INTO cat_relation (userid,catid,kwhd) VALUES ('$userid','$catid',$kwhd)");
      }
    } 
    else
    {
      if ($row) $cord = $row['cord'];
    }

    $out .= "<b>Your current entry:</b>";
    $out .= "<h2>".($cord*1.0)." cords/year = ".($cord*14.0)." kWh/d</h2>";


    //------------------------------------------------------------
    // Entry form
    //------------------------------------------------------------
    $out .= "<form name='add' action='woodfire' method='post'>";
    $out .= 'Update number of <a href="http://en.wikipedia.org/wiki/Cord_(unit)">cords of wood</a> you use in a year: <input type="text" name="kwh" style="width:100px" value=""/>';
    $out .= ' <input type="submit" value="Ok" />';
    $out .= "</form>";

    $out .="</div>"; 
    $variables['content'] = $out;
    return $variables;
  }

  function woodfire_setup()
  {
    db_query(
    "CREATE TABLE cat_firewood
    (
      userid int,
      cord float
    )");
  }

?>
