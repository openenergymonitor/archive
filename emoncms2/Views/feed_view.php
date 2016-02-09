<!--
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->

<h2>My feeds</h2>

<div class='lightbox' style='margin-bottom:20px'>

  <h3>API keys</h3>
  <table>
    <tr>
      <td><b>Read only access: </b><?php echo $apikey_read; ?></td>
      <td>
        <form action="" method="post">
          <input type="hidden" name="form" value="newapi_read">
          <input type="submit" value="new" >
        </form>
      </td>
    </tr>

    <tr>
      <td><b>Write only access: </b><?php echo $apikey_write; ?></td>
      <td>
        <form action="" method="post">
          <input type="hidden" name="form" value="newapi_write">
          <input type="submit" value="new" >
        </form>
      </td>
    </tr>
  </table>
</div>

<div class='lightbox' style='margin-bottom:20px'>
  <?php
  $testjson = $GLOBALS['path']."api/post?apikey=".$apikey_write."&json={power:252.4,temperature:15.4}"
  ?>

  <p><b>API url: </b><?php echo $GLOBALS['path']; ?>api/post</p>
  <p><b>Example: Copy this to your web browser or send from a nanode: </b><br/><?php echo $testjson; ?></p>
</div>

<div class='lightbox' style='margin-bottom:20px'>
  <h3>1) Inputs</h3>

  <?php if ($inputs) { ?>
    <table class='catlist'>
    <tr><th>Name</th><th>Last Value</th><th>Action</th></tr>
    <?php $i=0; foreach ($inputs as $input) { $i++; ?>

    <tr class="<?php echo 'd'.($i & 1); ?> " >
      <td><?php echo $input[1]; ?></td>
      <td><?php echo $input[3]; ?></td>      
      <td>
        <form action="" method="post">
          <input type="hidden" name="form" value="input">
	  <input type="hidden" name="id" value="<?php echo $input[0]; ?>">
          <input type="submit" value=">" class="buttonLook"/>
        </form>
      </td>
    </tr>
    
  <?php } echo "</table>"; } else { ?>
    <p>You have no inputs, to get started connect up your monitoring hardware</p>
  <?php } ?>
</div>


<div class='lightbox' style='margin-bottom:20px'>
  <h3>2) Input Configuration:   <?php echo get_input_name($inputsel); ?></h3>



  <?php 
  if (isset($input_processlist))
  {
  ?>

  <table class='catlist'><tr><th>Order</th><th>Process</th><th>Arg</th><th></th></tr>
  
  <?php $i = 0;
     

          foreach ($input_processlist as $input_process)    		// For all input processes
          {
            $i++;
            echo "<tr class='d" . ($i & 1) . "' >";
            echo "<td>".$i."</td><td>".$input_process[0]."</td><td>".$input_process[1]."</td>";
            echo "<td></td></tr>";
          }
        
   ?>
        <tr><td>New</td><td>
        <form action="" method="post">
        <input type="hidden" name="form" value="process">
        <input type="hidden" name="id" value="<?php echo $inputsel; ?>">
        <select class="processSelect" name="sel">

        <?php for ($i=1; $i<=count($process_list); $i++) { ?>
        <option value="<?php echo $i; ?>"><?php echo $process_list[$i][0]; ?></option>
        <?php } ?>

        </select></td>
        <td><input type="text" name="arg" class="processBox" style="width:100px;" /></td>
        <td><input type="submit" value="add" /></form></td>
        </tr></table>

  <?php } ?>

</div>

<div class='lightbox' style='margin-bottom:20px'>
  <h3>3) Feeds</h3>

  <?php if ($feeds) { ?>
  <table class='catlist'><tr><th>id</th><th>Name</th><th>updated</th><th>Value</th><th>Visualise</th></tr>
  <?php 
    $i = 0;
    foreach ($feeds as $feed)
    {
      $timenow = time();
      $time = strtotime($feed[2]);
      $sec = ($timenow - $time);
      $min = number_format($sec/60,0);
      $hour = number_format($sec/3600,0);

      $updated = $sec."s ago";
      if ($sec>180) $updated = $min." mins ago";
      if ($sec>(3600*2)) $updated = $hour." hours ago";
      if ($hour>24) $updated = "inactive";


      $color = "rgb(255,125,20)";
      if ($sec<30) $color = "rgb(240,180,20)";
      if ($sec<10) $color = "rgb(50,200,50)";

      $i++;
      ?>
      <tr class="<?php echo 'd'.($i & 1); ?> " >
      <td><?php echo $feed[0]; ?></td>
      <td><?php echo $feed[1]; ?></td>
      <td style="color:<?php echo $color; ?>"><?php echo $updated; ?></td>
      <td><?php echo $feed[3]; ?></td>
      <td>
      <form action="graph" method="post">
        <input type="hidden" name="form" value="graph">
        <input type="hidden" name="feedid" value="<?php echo $feed[0]; ?>">
        <input type="hidden" name="feedname" value="<?php echo $feed[1]; ?>">
        <select name="sel">
          <option value="1">Realtime</option>
          <option value="2">Raw data</option>
          <option value="3">Bar graph</option>
        </select>
        <input type="submit" value="view" class="buttonLook"/>
      </form>
      </td>
      </tr>
    <?php } ?>
    </table>
    <?php } else { ?>
      <p>You have no feeds</p>
    <?php } ?>
    </div>




