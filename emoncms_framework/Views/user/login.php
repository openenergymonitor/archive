<!--
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->
<div class="lightbox" style="background-color:#fff;">
<h2>Sign me up</h2>
<form action="<?php echo $GLOBALS['path']; ?>user" method="post">
  <input type="hidden" name="form" value="register"/>

  <table>
  <tr><td>Email:</td><td><input type="text" name="username" /></td></tr>
  <tr><td>Password:</td><td><input type="password" name="pass1" /></td></tr>
  <tr><td></td><td><input type="submit" name="form" value="login" /> or <input type="submit" name="form" value="register" /></td></tr>
  </table>

</form>
</div>
