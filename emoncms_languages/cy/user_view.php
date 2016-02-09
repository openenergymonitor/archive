<!--
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->

<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

global $path, $session; 

?>

<div class='lightbox' style="margin-bottom:20px; margin-left:3%; margin-right:3%;">

  <h2>Cyfrif: <?php echo $user['username']; ?></h2>

  <div class="widget-container-nc" style="width:600px;">

  <table><tr>
  <td width="180"><h3>Iaith</h3></td>
  <td><p>Dewiswch eich iaith ffafriol:</p></td>
  <td>
  <form action="setlang" method="get">

  <select name="lang">
    <option value="en">English</option>
    <option selected value="cy">Cymraeg</option>
  </select>

        
  <input type="submit" value="gosod" class="button05">
  </form>
  </td>
  </tr>
  </table>

  </div>
  <div style="clear:both;"></div><br/>
  <div class="widget-container-nc" style="width:600px;">

  <h3>Allweddi API</h3>
  <table>
    <tr>
      <td><b>Mynediad darllen yn unig: </b><?php echo $user['apikey_read']; ?></td>
      <td>
        <form action="newapiread" method="post">
          <input type="submit" value="Newydd" class="button05">
        </form>
      </td>
    </tr>

    <tr>
      <td><b>Mynediad llawn: </b><?php echo $user['apikey_write']; ?></td>
      <td>
        <form action="newapiwrite" method="post">
          <input type="submit" value="Newydd" class="button05">
        </form>
      </td>
    </tr>
  </table>

  </div>
  <div style="clear:both;"></div><br/>

  <?php
  $testjson = $GLOBALS['path']."api/post?apikey=".$user['apikey_write']."&json={power:252.4,temperature:15.4}"
  ?>
  <div class="widget-container-nc"  style="width:600px;">
  <p><b>API url: </b><?php echo $GLOBALS['path']; ?>api/post</p>
  <p><b>Enghraifft: Copïwch i'ch porwr gwe neu anfonwch o nanode: </b><br/><?php echo $testjson; ?> <a href="<?php echo $testjson; ?>">trio fi</a></p>
  </div>
  <div style="clear:both;"></div><br/>

<div class="widget-container-nc"  style="width:600px;">
<h3>Newid cyfrinair</h3>
<form action="changepass" method="post">
<p><b>Hen gyfrinair:</b><br/>
<input class="inp01" type="password" name="oldpass" style="width:250px"/></p>
<p><b>Cyfrinair newydd:</b><br/>
<input class="inp01" type="password" name="newpass" style="width:250px"/></p>
<input type="submit" class="button04" value="Newid" /> 
</form>
</div>
  <div style="clear:both;"></div><br/>

<div class="widget-container-nc"  style="width:600px;">
<h3>Ystadegau cyfrif</h3>

<table>
  <tr><td>Defnyddiad ddisg:</td><td><?php echo number_format($stats['memory']/1024.0,1); ?> KiB</td></tr>
  <tr><td>Hitiau fynnu:</td><td><?php echo $stats['uphits']; ?></td></tr>
  <tr><td>Hitiau lawr:</td><td><?php echo $stats['dnhits']; ?></td></tr>
</table>
</div>
</div>





