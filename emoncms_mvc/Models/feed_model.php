<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Creates a feed entry and relates the feed to the user
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function create_feed($userid,$name)
  {
    $result = db_query("INSERT INTO feeds (name) VALUES ('$name')");				// Create the feed entry
    $result = db_query("SELECT id FROM feeds WHERE name='$name'");				// Select the same feed to find the auto assigned id
    if ($result) {
      $array = db_fetch_array($result);
      $feedid = $array['id'];											// Feed id
      db_query("INSERT INTO feed_relation (userid,feedid) VALUES ('$userid','$feedid')");	// Create a user->feed relation

      // create feed table
      $feedname = "feed_".$feedid;
      $result = db_query(
      "CREATE TABLE $feedname
      (
        time DATETIME,
        data float
      )");

      return $feedid;												// Return created feed id
    } else return 0;
  }

    function get_user_feeds($userid)
    {
        $result = db_query("SELECT * FROM feed_relation WHERE userid = '$userid'");
        $feeds = array();
        if ($result)
        {
          while ($row = db_fetch_array($result)) {

            $feedid = $row['feedid'];
            // 2) get feed name of id
            $feed_result = db_query("SELECT * FROM feeds WHERE id = '$feedid'");
            $feed_row = db_fetch_array($feed_result);
            $feeds[] = array($feed_row['id'],$feed_row['name'],$feed_row['time'],$feed_row['value']);
          }
        }
        return $feeds;
    }

  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Gets a feeds ID from it's name and user ID
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function get_feed_id($user,$name)
  {
    $result = db_query("SELECT * FROM feed_relation WHERE userid='$user'");
    while ($row = db_fetch_array($result))
    {
      $feedid = $row['feedid'];
      $result = db_query("SELECT name FROM feeds WHERE id='$feedid'");
      $row_name = db_fetch_array($result);
      if ($key == $row_name['name']) return $feedid;
    }
    return 0;
  }

  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Gets a feeds name from its ID
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function get_feed_name($feedid)
  {
    $result = db_query("SELECT name FROM feeds WHERE id='$feedid'");
    if ($result) { $array = db_fetch_array($result); return $array['name']; } 
    else return 0;
  }

  //---------------------------------------------------------------------------
  // Function feed insert
  //---------------------------------------------------------------------------
  function insert_feed_data($feedid,$time,$value)
  {                   
    $feedname = "feed_".trim($feedid)."";
    $time = date("Y-n-j H:i:s", $time);                        
    db_query("INSERT INTO $feedname (`time`,`data`) VALUES ('$time','$value')");
    db_query("UPDATE feeds SET value = '$value', time = '$time' WHERE id='$feedid'");
  }

  //---------------------------------------------------------------------------
  // Get all feed data (it might be best not to call this on a really large dataset use function below to select data @ resolution)
  //---------------------------------------------------------------------------
  function get_all_feed_data($feedid)
  {
    $feedname = "feed_".trim($feedid)."";
    $data = array();   
    $result = db_query("select * from $feedname ORDER BY time");
    while($array = db_fetch_array($result))
    {
      $time = strtotime($array['time'])*1000;
      $kwhd = $array['data'];    
      $data[] = array($time , $kwhd);
    }
    return $data;
  }

  //---------------------------------------------------------------------------
  // Get feed data - within date range and @ specified resolution
  //---------------------------------------------------------------------------
  function get_feed_data($feedid,$start,$end,$resolution)
  {
    $feedname = "feed_".trim($feedid)."";
    $start = date("Y-n-j H:i:s", ($start/1000));		//Time format conversion
    $end = date("Y-n-j H:i:s", ($end/1000));  			//Time format conversion

    //This mysql query selects data from the table at specified resolution
    if ($resolution>1){
      $result = db_query(
      "SELECT * FROM 
      (SELECT @row := @row +1 AS rownum, time,data FROM ( SELECT @row :=0) r, $feedname) 
      ranked WHERE (rownum % $resolution = 1) AND (time>'$start' AND time<'$end') order by time Desc");
    }
    else
    {
      //When resolution is 1 the above query doesnt work so we use this one:
      $result = db_query("select * from $feedname WHERE time>'$start' AND time<'$end' order by time Desc"); 
    }

    $data = array();                                     //create an array for them
    while($row = db_fetch_array($result))             // for all the new lines
    {
      $dataValue = $row['data'] ;                        //get the datavalue
      $time = (strtotime($row['time']))*1000;            //and the time value - converted to unix time * 1000
      $data[] = array($time , $dataValue);               //add time and data to the array
    }
    return $data;
  }




?>
