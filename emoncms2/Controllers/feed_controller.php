<?php 
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org

    Last update: 29th July 2011 - new input processing implementation
    Author: Trystan Lea trystan.lea@googlemail.com
  */
function feed_controller()
{

  if (!$_SESSION['valid']) return "Sorry, you must be logged in to see this page";

  require "Models/process_model.php";
  require "Models/input_model.php";
  require "Models/feed_model.php";

  $userid = $_SESSION['userid'];

  $apikey_write = get_apikey_write($userid);
  $apikey_read = get_apikey_read($userid);

  if ($_POST["form"] == "newapi_write" || !$apikey_write)
  { 
    $apikey_write = md5(uniqid(rand(), true));
    set_apikey_write($userid, $apikey_write);
  }

  if ($_POST["form"] == "newapi_read" || !$apikey_read)
  { 
    $apikey_read = md5(uniqid(rand(), true));
    set_apikey_read($userid, $apikey_read);
  }

  if ($_POST["form"] == "input")
  { 
    $inputid = intval($_POST["id"]);
    $input_processlist = get_input_processlist_desc($inputid);
  }

  if ($_POST["form"] == "process")
  { 
    $inputid = intval($_POST["id"]);
    $processid = intval($_POST["sel"]);			// get process type
    $arg = $_POST["arg"];

    $process = get_process($processid);
    if ($process[1] == 0) $arg = floatval($arg);
    if ($process[1] == 1) $arg = get_input_id($userid,$arg);
    if ($process[1] == 2)
    {
      $id = get_feed_id($userid,$arg);
      if ($id==0)  $id = create_feed($userid,$arg);
      $arg = $id;
    }
    add_input_process($inputid,$processid,$arg);
    $input_processlist = get_input_processlist_desc($inputid);
  }

  $inputs = get_user_inputs($userid);
  $feeds = get_user_feeds($userid);

  $process_list = get_process_list();
  // Render view
  $content = view("feed_view.php",array('apikey_read' => $apikey_read,'apikey_write' => $apikey_write, 'inputs' => $inputs, 'inputsel' => $inputid, 'feeds' => $feeds, 'input_processlist' => $input_processlist, 'process_list'=>$process_list));

  return $content;
}

?>


