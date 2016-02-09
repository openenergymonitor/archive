<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

  function flying_view($catid, $relation)
  {

    $userid = $_SESSION['userid'];

    if (isset($_POST['date'])) $date = $_POST['date']; else $date =0;
    if (isset($_POST['desc'])) $desc = $_POST['desc']; else $desc =0;
    if (isset($_POST['miles'])) $miles = $_POST['miles']; else $miles =0;

    //------------------------------------------------------------
    // Introduction : The maths
    //------------------------------------------------------------
    $out = '';
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Energy in flying: the maths</h3>";
    $out .= "<p>From David MacKay's <a href='http://www.inference.phy.cam.ac.uk/withouthotair/c5/page_35.shtml' >planes chapter</a>:</p>";
    $out .= "<p>A Boeing 747-400 with 240 000 litres of fuel carries 416 passengers about
8 800 miles (14 200 km). And fuel’s calorific value is 10 kWh per litre. So the energy cost of one full-distance roundtrip
on such a plane, if divided equally among the passengers, is</p>
<p>2 × 240 000 litre / 416 passengers × 10 kWh/litre ≈ 12 000 kWh per passenger</p>

<p>To do the calculations below we need kWh per mile, which is: 12 000 kWh / ( 2 x 8800 ) = <b>0.68kWh per mile</b></p>
</div>
";

    //------------------------------------------------------------
    // Add data
    //------------------------------------------------------------
    if ($miles!=0)
    {
      // 1) Insert flight data
      db_query("INSERT INTO cat_flight (userid,date,description,miles) VALUES ('$userid','$date','$desc','$miles')");

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
      $result = db_query("SELECT * FROM cat_flight WHERE userid='$userid' ORDER BY date ASC");

    $start = 0; $end=0;

    //------------------------------------------------------------
    // Your data
    //------------------------------------------------------------
    $i =0;
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Your data:</h3>";
    $out .= "<table class='catlist'><tr><th>Date</th><th>Description</th><th>Miles</th><th>kWh</th></tr>";
    $total_miles = 0;
    while ($row = mysql_fetch_array($result))
    { $i++;					// $i used to give alternate color table + number of flights
      if ($i==1) $start = $row['date'];		// To calculate history length
      $end = $row['date'];			// --

      $total_miles +=$row['miles'];		// Sum to get total miles

      $out .= 
      "<tr class='d".($i & 1)."'>
      	<td>".$row['date']."</td>
	<td>".$row['description']."</td>
	<td>".$row['miles']."</td>
	<td>".($row['miles']*0.68)." kWh</td>
      </tr>";
    }
    $out .= "</table>";
    $out .= "</div>";

    // Stats calculations
    $years = years($start, $end)+1;						// Calculate number of years
    $times_around_world = number_format(($total_miles/24900),2,'.','');		// Just for fun
    $kwhd = number_format(((($total_miles*0.68)/$years)/365),2,'.',''); 	// Average historic kwh/d

    // Stats box
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Stats:</h3>";
    $out .= "<p>Number of flights taken: ".$i."</p>";
    $out .= "<p>Total miles flown: ".$total_miles." miles. Thats ".$times_around_world." times around the world!</p>";
    $out .= "<p>Years in flying history: ".number_format(($years),2,'.','')."</p>";
    $out .= "<p>Average kWh/d over flying history: ".$kwhd." kWh/d</p>";
    $out .= "</div>";

      db_query("UPDATE cat_relation SET kwhd = '$kwhd' WHERE userid = '$userid' AND catid = '$catid'");
      
    }

    //------------------------------------------------------------
    // Entry form
    //------------------------------------------------------------
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Add a flight:</h3>";
    $out .= "<form name='add' action='flying' method='post'>";
    $out .= "<table>"; 
    $out .= '<tr><td>Date:</td><td><input type="text" name="date" style="width:100px;" value=""/></td></tr>';
    $out .= '<tr><td>Description:</td><td><input type="text" name="desc" style="width:100px;" value=""/></td></tr>';
    $out .= '<tr><td>Miles:</td><td><input type="text" name="miles" style="width:100px;" value=""/></td></tr>';
    $out .= '<tr><td></td><td><input type="submit" value="Ok" /></td></tr>';
    $out .= "</table>";
    $out .= "</form>";
    $out .= "</div>";

    //------------------------------------------------------------
    // Distance widget
    //------------------------------------------------------------
    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<h3>Calculate your flight distance:</h3>";
    $out .= "<p>Courtesy of wolfram alpha scripts</p>";
    $out .= '<script type="text/javascript" id="WolframAlphaScript95f2b84de5660ddf45c8a34933a2e66f" src="http://www.wolframalpha.com/widget/widget.jsp?id=95f2b84de5660ddf45c8a34933a2e66f"></script>';
    $out .= "</div>";

    $variables['title'] = "Flying";
    $variables['content'] = $out;
    return $variables;
  }

  function years($start, $end) 
  {
    $start_ts = strtotime($start)/100;
    $end_ts = strtotime($end)/100;
    $diff = $end_ts - $start_ts;
    return round($diff / 315360);
  }


  function flying_setup()
  {
    db_query(
    "CREATE TABLE cat_flight
    (
      userid int,
      date text,
      description text,
      miles int
    )");
  }


?>
