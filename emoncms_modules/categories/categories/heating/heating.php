<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  $kwhd = 0;

  function heating_view($catid, $relation)
  {
    $userid = $_SESSION['userid'];

    global $kwhd;

    if (isset($_POST))
    {
      $litres = 0;
      if (isset($_POST['date'])) $date = $_POST['date'];
      if (isset($_POST['litres'])) $litres = $_POST['litres'];
      if (isset($_POST['cost'])) $cost = $_POST['cost'];
      if (isset($_POST['vat'])) $vat = $_POST['vat'];


      if ($litres!=0){
        // If category is not yet related to the user
        if (!$relation)
        {
          db_query("INSERT INTO cat_relation (userid,catid) VALUES ('$userid','$catid')");
        }

        addHeatingOilDelivery($date,$litres,$cost,$vat);
      }
    }

    $data = getHeatingOilData();
    $output = renderHeatingOilData($data);

    $variables['title'] = "Heating";

    $share = ($kwhd/2);

    db_query("UPDATE cat_relation SET kwhd = '$share' WHERE userid = '$userid' AND catid = '$catid'");

    $out = "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "Your share: ".$share." kWh/d | House total: ".$kwhd." kWh/d | £".(365*$kwhd*0.044)."/year</div>";

    $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
    $out .= "<table border = '0' cellpadding='8' style='text-align:center;'>";
    $out .= "<tr>";
    $out .= "<th width='100px'>Date</th>";
    $out .= "<th>Litres</th>";
    $out .= "<th>Unit Price</th>";
    $out .= "<th>VAT Rate</th>";
    $out .= "<th>VAT Value</th>";
    $out .= "<th>Goods Value</th>";
    $out .= "<th>Total</th>";
    $out .= "<th>kWh/d</th>";
    $out .= "</tr>";

    $out .= $output;

    $out .= "</table>";
    $out .= "</div>";

    $out .= "<div class='lightbox' style='height:40px; padding-top:15px; padding-bottom:15px;'>";
    $out .= "<form name='add' action='heating' method='post'>";
    $out .= '<input type="text" name="date" class="inputText" style="width:100px" value="0000-00-00"/>';
    $out .= '<input type="text" name="litres" class="inputText" style="width:80px"  value="litres"/>';
    $out .= '<input type="text" name="cost" class="inputText" style="width:50px"  value="cost"/>';
    $out .= '<input type="text" name="vat" class="inputText" style="width:50px"  value="tax"/>';
    $out .= '<input type="submit" value="Add" class="greenButton" style="float:right;" />';
    $out .= "</form>";
    $out .= "</div>";

    $variables['content'] = $out;
    return $variables;
  }

  function addHeatingOilDelivery($date,$litres,$cost,$vat)
  {

    $userid = $_SESSION['userid'];

    db_query("INSERT INTO cat_heating (userid,date,litres,cost,vat) VALUES ('$userid','$date','$litres','$cost','$vat')");
  }

  //----------------------------------------------------------------------------------------
  // Get Heating oil data
  //----------------------------------------------------------------------------------------
  function getHeatingOilData()
  {
    $userid = $_SESSION['userid'];

    $result = db_query("SELECT * FROM cat_heating WHERE `userid` = '$userid' ORDER BY date");

    $data = array(); 
    if ($result)
    {
                                       
    while ($row = mysql_fetch_array($result))         
    {

      $litres = $row['litres']; 
      
      $cost = $row['cost'];
      $tax = $row['vat'];

      $goodsValue = ($cost/100) * $litres;
      $vatValue = $goodsValue*($tax/100);

      $total = $goodsValue + $vatValue;

      $data[] = array($row['date'] , $row['litres'],$cost,$tax,$vatValue,$goodsValue,$total);             
    }}
    return $data;
  }

  //----------------------------------------------------------------------------------------
  // Render Heating oil data
  //----------------------------------------------------------------------------------------
  function renderHeatingOilData($data)
  {
    global $kwhd;
  
    $val = 0;
    $output = "";
    for ($i=0; $i<sizeof($data); $i++)
    {
      $lval = $val;
      $val = $data[$i][0];

      $litres = $data[$i][1];
      if ($i>0)
      {
        $kwhd = 0;
        $days = dateDiff($lval, $val);
 	if ($days>0) $kwhd = round((($litres/$days)*10.27),0);
      }

      $output .= "<tr>";
      $output .= "<td>".$data[$i][0]."</td>";	// Date
      $output .= "<td>".$litres."</td>";		// Litres
      $output .= "<td>".$data[$i][2]."p</td>";	// Unit price
      $output .= "<td>".$data[$i][3]."%</td>";	// VAT rate
      $output .= "<td>£".round($data[$i][4])."</td>";	// VAT value
      $output .= "<td>£".round($data[$i][5])."</td>";	// Good value
      $output .= "<td>£".round($data[$i][6])."</td>";	// Total
      if ($i>0) $output .= "<td>".$kwhd."</td>";
      $output .= "</tr>";
    } 

    return $output;
  }

  //----------------------------------------------------------------------------------------
  // Date difference
  //----------------------------------------------------------------------------------------
  function dateDiff($start, $end) 
  {
    $start_ts = strtotime($start);
    $end_ts = strtotime($end);
    $diff = $end_ts - $start_ts;
    return round($diff / 86400);
  }

  function heating_setup()
  {
    db_query(
    "CREATE TABLE cat_heating
    (
      userid int,
      date text,
      litres int,
      cost int,
      vat float
    )");
  }

?>
