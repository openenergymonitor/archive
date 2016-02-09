<?php 
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
function api_controller()
{
  require "Models/input_model.php";
  require "Models/feed_model.php";
  require "Models/process_model.php";

  $args = $GLOBALS['args'];

  $userid = get_apikey_user($_GET['apikey']);			// basic apikey authentication
  if ($userid==0) { echo "valid apikey required"; die; }

  if ($args[1] == 'inputs') $data = get_user_inputs($userid);
  if ($args[1] == 'feeds') $data = get_user_feeds($userid);

  if ($args[1] == 'getfeed')
  {
    $feedid = $_GET['feedid'];
    $start = $_GET['start'];
    $end = $_GET['end'];
    $resolution = $_GET['resolution'];
    $data = get_feed_data($feedid,$start,$end,$resolution);
  }

  if ($args[1] == 'post')
  {
  //--------------------------------------------------------------------------------------------------------------
  // 1) Validate apikey, time and json arguments
  //--------------------------------------------------------------------------------------------------------------
  $json = validate_json($_GET["json"]);				// get and validate json

  $time = time();
  if (isset($_GET["time"])) $time = intval($_GET["time"]);	// use sent timestamp if present

  //--------------------------------------------------------------------------------------------------------------
  // 2) Register incoming inputs
  //--------------------------------------------------------------------------------------------------------------
  $datapairs = explode(",", $json);				// Seperate JSON string into individual data pairs. 
  $inputs = array();
  foreach ($datapairs as $datapair)       
  {
    $datapair = explode(":", $datapair);
    $name = $datapair[0]; 
    $value = $datapair[1];		

    $id = get_input_id($userid,$name);				// If input does not exist this return's a zero
    if ($id==0) {
      create_input_timevalue($userid,$name,$time,$value);			// Create input if it does not exist
    } else {			
      $inputs[] = array($id,$time,$value);	
      set_input_timevalue($id,$time,$value);			// Set time and value if it does
    }
  }
  
  //--------------------------------------------------------------------------------------------------------------
  // 3) Process inputs according to input processlist
  //--------------------------------------------------------------------------------------------------------------
  foreach ($inputs as $input)            
  {
    $id = $input[0];
    $processlist = explode(",", get_input_processlist($id));				
    $value = $input[2];
    foreach ($processlist as $inputprocess)    			        
    {
      $inputprocess = explode(":", $inputprocess); 		// Divide into process id and arg
      $processid = $inputprocess[0];				// Process id
      $arg = $inputprocess[1];	 				// Can be value or feed id

      if ($processid == 1) $value *= $arg;			// 1. Scale
      if ($processid == 2) $value += $arg;			// 2. Offset
      if ($processid == 3) $value = times_input($arg,$value);	// 3. Multiply with another input
      if ($processid == 4) insert_feed_data($arg,$time,$value);	// 4. Log
      if ($processid == 5) power_to_kwh($arg,$time,$value);
      if ($processid == 6) power_to_kwhd($arg,$time,$value);
      if ($processid == 7) kwhinc_to_kwhd($arg,$value);
      if ($processid == 8) input_ontime($arg,$value);
    }
  }
  $data = "ok";
  }

  return json_encode($data);
}

?>


