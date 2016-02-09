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
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <script language="javascript" type="text/javascript" src="YOUR DIRECTORY PATH/flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="YOUR DIRECTORY PATH/flot/jquery.flot.js"></script>
  </head>

  <body style="margin: 0px; padding:10px; font-family: arial;">

    <div id="graph_bound" style="height:100%; width:100%; position:relative; ">
      <div id="graph"></div>
    </div>
 
    <script id="source" language="javascript" type="text/javascript">
    $(function () 
    {
      $('#graph').width($('#graph_bound').width());
      $('#graph').height($('#graph_bound').height());

      // API key
      var apikey = 'YOUR APIKEY';

      // View feed id 1
      var feedid = 5;

      // View 7 day range
      var date = new Date;
      var time = date.getTime();
      var start = time-(3600000*24);
      var end = time;
      
      // @ a resolution of 1 (all data)
      var res = 1;

      vis_feed_data(apikey,feedid,start,end,res);

      function vis_feed_data(apikey,feedid,start,end,res)
      {
        $.ajax({                                      
          url: 'YOUR DIRECTORY PATH/api/getfeed',                         
          data: "&apikey="+apikey+"&feedid="+feedid+"&start="+start+"&end="+end+"&resolution="+res,
          dataType: 'json',                           
          success: function(data) 
          { 
            var barwidth = 3600*22;
            $.plot($("#graph"), [data], 
            {
              bars: { show: true,align: "center",barWidth: (barwidth*1000),fill: true },
              grid: { show: true, hoverable: true, clickable: true },
              xaxis: { mode: "time"}
            });
          } 
        });
      }

    });
    </script>

  </body>
</html>
