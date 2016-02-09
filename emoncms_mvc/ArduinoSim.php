
<html>
<!--
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

 <?php

  $path = dirname("http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'])."/";

 ?>

 <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
 </head>
 <body>

   <h2>Arduino Simulator</h2>
   <input type="button" id="up" value="+" />
   <input type="button" id="down" value="-" />

   <script id="source" language="javascript" type="text/javascript">
   var path = "<?php echo $path; ?>";

   // API key
   var apikey = '2801299ebc762cac8c6e8e865f2bf2b3';
   var value = 150;

   $(function () {

   setInterval ( doSome, 2000 );

   function doSome()
   {
     $.ajax({                                      
          url: path+'api/post',                         
          data: "&apikey="+apikey+"&json={power:"+value+"}",                          
          success: function(data) 
          { 
          } 
        });
   }

   $('#up').click(function () 
   {
     value = value + 100;
   });

   $('#down').click(function () 
   {
     value = value - 100;
   });

  });
  //--------------------------------------------------------------------------------------
  </script>

  </body>
</html>  
