
<html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
-->
  <?php $path = "YOUR EMONCMS DIRECTORY"; ?>

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <script language="javascript" type="text/javascript" src="<?php echo $path; ?>flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="<?php echo $path; ?>flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="<?php echo $path; ?>flot/date.format.js"></script>
    <script language="javascript" type="text/javascript" src="kwhd_functions.js"></script>

    <?php
      require "../../Includes/db.php";
      require "../../Models/feed_model.php";
      $data = get_all_feed_data($_GET["kwhd"]);
      $power = $_GET["power"];
    ?>

  </head>
  <body style="margin: 0px; padding:10px; font-family: arial;">

    
    <div id="test" style="height:100%; width:100%; position:relative; ">
      <div id="placeholder" style="font-family: arial;"></div>
      <div id="loading" style="position:absolute; top:0px; left:0px; width:100%; height:100%; background-color: rgba(255,255,255,0.5);"></div>
      <h2 style="position:absolute; top:0px; left:40px;"><span id="out"></span></h2>
    </div>

    <script id="source" language="javascript" type="text/javascript">
      var data = <?php echo json_encode($data); ?>;    
      var power = <?php echo $power; ?>;    
      var path = "<?php echo $path; ?>";  

      // API key
      var apikey = 'YOUR APIKEY';

      $(function () 
      {
        $('#placeholder').width($('#test').width());
        $('#placeholder').height($('#test').height());

        $('#loading').hide();
        var view = 0;
 
        var days = [];
        var months = [];
        months = get_months(data);
        bargraph(months,3600*24*20);

        $("#placeholder").bind("plotclick", function (event, pos, item)
        {
          if (item!=null)
          {
            if (view==1)
            {
              $('#loading').show();
              $("#out").html("Loading...  please wait about 5s");
      
              var d = new Date();
              d.setTime(item.datapoint[0]);
              inst = get_inst_day(item.datapoint[0]);
              view = 2;
            }
            if (view==0)
            {
              var d = new Date();
              d.setTime(item.datapoint[0]);
              days = get_days_month(data,d.getMonth(),d.getFullYear());
              bargraph(days,3600*22);
              view = 1;
              $("#out").html("");
            }
          }
          else
          {
            
            if (view==1) { $("#out").html(""); view = 0; bargraph(months,3600*24*20); }     
            if (view==2) { $("#out").html(""); view = 1; bargraph(days,3600*22); }      
          }
        });

        $("#placeholder").bind("plothover", function (event, pos, item)
        {
          if (item!=null)
          {
            var d = new Date();
            d.setTime(item.datapoint[0]);
            var mdate = new Date(item.datapoint[0]);
            if (view==0) $("#out").html(item.datapoint[1].toFixed(1)+" kWh/d | "+mdate.format("mmm yyyy"));
            if (view==1) $("#out").html(item.datapoint[1].toFixed(1)+" kWh/d | "+mdate.format("dS mmm yyyy"));
            if (view==2) $("#out").html(item.datapoint[1].toFixed(1)+" kW | "+mdate.format("dS mmm yyyy"));
          }
        });

        function bargraph(data,barwidth)
        {
          $.plot($("#placeholder"), [data], 
          {
            bars: { show: true,align: "center",barWidth: (barwidth*1000),fill: true },
            grid: { show: true, hoverable: true, clickable: true },
            xaxis: { mode: "time"}
          });
        }

        function get_inst_day(time)
        {
          var feedid = power;
          var start = time;
          var end = time + 3600000 * 24;
          var res = 1;
          $.ajax({                                      
            url: path+'api/getfeed',                         
            data: "&apikey="+apikey+"&feedid="+feedid+"&start="+start+"&end="+end+"&resolution="+res,
            dataType: 'json',                           
            success: function(datag) 
            {
           
              $.plot($("#placeholder"),
                [{data: datag, lines: { fill: true }}],
                {xaxis: { mode: "time", min: (start), max: (end)},
                grid: { show: true, hoverable: true, clickable: true },
                selection: { mode: "xy" }
              }); 
              $('#loading').hide();
              $("#out").html("");
            } 
          });
        }  
     });
    </script>
  </body>
</html>

