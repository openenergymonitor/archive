<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  function electric_view($catid, $relation)
  {
    $userid = $_SESSION['userid'];
    $out = "";

    $variables['title'] = "Electric";

    $result = db_query("SELECT * FROM cat_electric WHERE userid='$userid'"); // Check if category exists
    $row = mysql_fetch_array($result);

    $powerfeed_id = $row['power'];
    $kwhfeed_id = $row['kwh'];

    if (isset($_POST['power']))
    {
      $powerfeed_id = $_POST['power'];
      $kwhfeed_id = $_POST['kwh'];

      if (!$relation)
      {
        db_query("INSERT INTO cat_relation (userid,catid) VALUES ('$userid','$catid')");
        db_query("INSERT INTO cat_electric (userid,power,kwh) VALUES ('$userid','$powerfeed_id','$kwhfeed_id')");
        $relation = 1;
      }
      if ($relation)
      {
        db_query("UPDATE cat_electric SET power = '$powerfeed_id',kwh = '$kwhfeed_id' WHERE userid = '$userid'");
      }
    }

    $powerfeed = "feed_".trim($powerfeed_id)."";
    $kwhfeed = "feed_".trim($kwhfeed_id)."";

    if ($powerfeed_id && $kwhfeed_id)
    {
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Stats</h3>";

    // kWh/d today
    $result = db_query("SELECT * FROM $kwhfeed ORDER BY time DESC LIMIT 30");
    if ($result)
    {
    $row = mysql_fetch_array($result);

    $todaykwh = $row['data'];
    $todaykwh = number_format($todaykwh,1,'.','');

    $kwhsum =0;
    $i=0;
    while ($row = mysql_fetch_array($result))
    {
      $i++;
      $kwhsum += $row['data'];
    }
    if ($i>0) $kwhd = $kwhsum / $i ; else $kwhd = 0;
    $kwhd = number_format($kwhd,1,'.','');
    $share = $kwhd / 3.0;

    $out .= "<p>kWh today so far: ".$todaykwh." kWh</p>";
    $out .= "<p>Average kWh/d over the last month: ".$kwhd." kWh/d</p>";
    $out .= "<p>Your share: ".$share." kWh/d</p>";

    if ($share==0) $out .= "<p><i>You will need at least one day of data to see anything here</i></p>";      

    }
    $out .= "</div>";
    // Update category kWh/d value


    if ($relation)
    {
      db_query("UPDATE cat_relation SET kwhd = '$share' WHERE userid = '$userid' AND catid = '$catid'");
    }

    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .='<iframe style="width:100%; height:500px;" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="'.$GLOBALS['systempath'].'modules/categories/igraph3.php?power='.$powerfeed_id.'&kwh='.$kwhfeed_id.'&price=0.12"></iframe>';
    $out .= "</div>";
    }

    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Configure</h3>";
    $out .="<p><i>Select your energy monitor feeds from the drop-down menus</i></p>";


    //-----------------------------------------------------------------------------
    // FEED SELECTION
    //-----------------------------------------------------------------------------
    $result = db_query("SELECT * FROM feeds");

    $feed_list = array();
    $powerlist = '';
    $kwhlist = '';
    while ($row = mysql_fetch_array($result))
    {
      if ($row['id'] == $powerfeed_id) {$powerlist .= "<option SELECTED>".$row['name']."</option>";} else {$powerlist .= "<option value=".$row['id']." >".$row['name']."</option>";}

     // if (strpos($row['name'], "kwhd")==TRUE) 
      //{
        if ($row['id'] == $kwhfeed_id) {$kwhlist .= "<option SELECTED>".$row['name']."</option>";} else {$kwhlist .= "<option value=".$row['id']." >".$row['name']."</option>";}
      //}
    }

    $out .= "<form name='add' action='electric' method='post'>";
    $out .= "<p>Select Power feed: <select name='power'>".$powerlist."</select></p>";
    $out .="<p>Select kWh feed: <select name='kwh'>".$kwhlist."</select></p>";
    $out .= ' <input type="submit" value="Ok" />';
    $out .= "</form></div>";

    $variables['content'] = $out;
    return $variables;
  }

  function electric_setup()
  {
   db_query(
    "CREATE TABLE cat_electric
    (
      userid int,
      power text,
      kwh text
    )");
  }

?>
