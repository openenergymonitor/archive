<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

  function car_view($catid, $relation)
  {

    $userid = $_SESSION['userid'];


    if (isset($_POST['date'])) $date = $_POST['date']; else $date = 0;
    if (isset($_POST['desc'])) $desc = $_POST['desc']; else $desc = 0;
    if (isset($_POST['miles'])) $miles = $_POST['miles']; else $miles = 0;
    if (isset($_POST['share'])) $share = $_POST['share']; else $share = 0;

    //------------------------------------------------------------
    // Introduction : The maths
    //------------------------------------------------------------
    $out = '';
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Energy in driving: the maths</h3>";
    $out .= "<p>David MacKay's <a href='http://www.inference.phy.cam.ac.uk/withouthotair/c3/page_29.shtml' >car driving chapter</a>:</p></div>";

    //------------------------------------------------------------
    // Add data
    //------------------------------------------------------------
    if ($miles!=0)
    {
      // 1) Insert flight data
      db_query("INSERT INTO cat_car (userid,date,`desc`,miles,`share`) VALUES ('$userid','$date','$desc','$miles','$share')");

      // If category is not yet related to the user
      if (!$relation)
      {
        db_query("INSERT INTO cat_relation (userid,catid) VALUES ('$userid','$catid')");
        $relation = 1;
      }
    }

    // Get flight information
    if ($relation)
    {
      $result = db_query("SELECT * FROM cat_car WHERE userid='$userid' ORDER BY date ASC");
  


    //------------------------------------------------------------
    // Your data
    //------------------------------------------------------------
    $i =0;
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Your data:</h3>";
    $out .= "<table class='catlist'><tr><th>Date</th><th>Description</th><th>Miles</th><th>Share</th><th>kWh each</th><th>cost each</th><th>kWh total</th></tr>";
    $total_miles = 0; $share_kwh = 0;
    while ( $row = mysql_fetch_array($result))
    { $i++;					// $i used to give alternate color table + number of flights
      if ($i==1) $start = $row['date'];		// To calculate history length
      $end = $row['date'];			// --

      $total_miles +=$row['miles'];			// Sum to get total miles
      $share_kwh += ($row['miles']/$row['share']);	// Sum to get total share kwh

      $out .= 
      "<tr class='d".($i & 1)."'>
      	<td>".$row['date']."</td>
      	<td>".$row['desc']."</td>
	<td>".$row['miles']."</td>
	<td>".$row['share']."</td>
	<td>".number_format(($row['miles']/$row['share']),1,'.','')."</td>
	<td>£".number_format((($row['miles']/$row['share'])*0.12),2,'.','')."</td>
	<td>".number_format(($row['miles']*1.0),1,'.','')."</td>
      </tr>";
    }
    $out .= "</table>";
    $out .= "</div>";

    // Stats calculations
    //$years = car_years($start, $end);						// Calculate number of years
    //$times_around_world = number_format(($total_miles/24900),2,'.','');		// Just for fun
    //if ($years>0) $kwhd = number_format(((($total_miles*1.0)/$years)/365),2,'.',''); 	// Average historic kwh/d

    // Stats box
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Stats:</h3>";
    //$out .= "<p>Number of journeys taken: ".$i."</p>";
    $out .= "<p>Total miles driven: ".$total_miles." miles.</p>";

    $out .= "<p>Share kwh: ".number_format($share_kwh,1,'.','')." kWh.</p>";

    $days = car_days($start, $end)+1;
 
    $out .= "<p>Total days in driving history: ".$days."</p>";

    $kwhd =number_format(( $share_kwh / $days),1,'.','');

    $out .= "<p>Average kWh/d over driving history: ".$kwhd." kWh/d</p>";
    $out .= "</div>";

    // Update category kWh/d value
    if ($relation)
    {
      db_query("UPDATE cat_relation SET kwhd = '$kwhd' WHERE userid = '$userid' AND catid = '$catid'");
    }
  }
    //------------------------------------------------------------
    // Entry form
    //------------------------------------------------------------
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Add a journey:</h3>";
    $out .= "<form name='add' action='car' method='post'>";
    $out .= "<table>"; 
    $out .= '<tr><td>Date:</td><td><input type="text" name="date" style="width:100px;" value=""/></td></tr>';
    $out .= '<tr><td>Description:</td><td><input type="text" name="desc" style="width:100px;" value=""/></td></tr>';
    $out .= '<tr><td>Miles:</td><td><input type="text" name="miles" style="width:100px;" value=""/></td></tr>';
    $out .= '<tr><td>Share:</td><td><input type="text" name="share" style="width:100px;" value=""/></td></tr>';
    $out .= '<tr><td></td><td><input type="submit" value="Ok" /></td></tr>';
    $out .= "</table>";
    $out .= "</form>";
    $out .= "</div>";



    $variables['title'] = "Car driving";
    $variables['content'] = $out;
    return $variables;
  }

  function car_days($start, $end) 
  {
    $start_ts = strtotime($start);
    $end_ts = strtotime($end);
    $diff = $end_ts - $start_ts;
    return round($diff / 86400);
  }

  function car_setup()
  {
    db_query(
    "CREATE TABLE cat_car
    (
      userid int,
      date text,
      `desc` text,
      miles float,
      `share` int
    )");
  }

?>
