<html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<!----------------------------------------------------------------------------------------------------

   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org

-------------------------------------------------------------------------------------->

 <?php
  error_reporting(E_ALL);
  ini_set('display_errors','On');
 
  $tablename = $_GET["power"];                 //Get the table name so that we know what graph to draw
  $kwhtable = $_GET["kwh"];                 //Get the table name so that we know what graph to draw
  $price = $_GET["price"];  
  if (!$price) $price = 0.14;

  $path = dirname("http://".$_SERVER['HTTP_HOST'].str_replace('modules/categories', '', $_SERVER['SCRIPT_NAME']))."/";

 ?>

 <head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!--[if IE]><script language="javascript" type="text/javascript" src="../excanvas.min.js"></script><![endif]-->
 <link rel="stylesheet" type="text/css" href="gstyle.css" />
    <script language="javascript" type="text/javascript" src="<?php echo $path; ?>flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="<?php echo $path; ?>flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="<?php echo $path; ?>flot/jquery.flot.selection.js"></script>
    <script language="javascript" type="text/javascript" src="date.format.js"></script>
 
 </head>
 <body>

   <!---------------------------------------------------------------------------------------------------
   // Time window buttons
   ---------------------------------------------------------------------------------------------------->
   <div style="float:right; margin-right:5px;">
   <button id="power" class="buttonLook" time="0.5">Power</button>
   <button id="kwhd" class="buttonLook" time="0.5">kWh/d</button>
   </div>

   <div id="item" style="font-size: 20px; font-family: arial;  font-weight: normal; color: #333;  position: absolute; top: 60px; left: -20px; text-align: right;">---</div>
   <div id="date" style="font-size: 20px; font-family: arial;  font-weight: normal; color: #333;  position: absolute; top: 100px; left: -20px; text-align: right;">---</div>

   <div id="powerGraph" style="font-family: arial; position: absolute; top: 35px; left: 0px;"></div>	
   <div id="kwhGraph" style="font-family: arial;  position: absolute; top: 35px; left: 0px;"></div>

   <div id="stat" style="font-size: 16px; font-family: arial;  font-weight: normal; color: #333;  position: absolute; top: 355px; left: 20px;">---</div>

   <script id="source" language="javascript" type="text/javascript">
   //--------------------------------------------------------------------------------------
   var table = "<?php echo $tablename; ?>";				//Fetch table name
   var kwhtable = "<?php echo $kwhtable; ?>";
   var price = "<?php echo $price; ?>";	
   var path = "<?php echo $path; ?>";	
   //----------------------------------------------------------------------------------------
   // These start time and end time set the initial graph view window 
   //----------------------------------------------------------------------------------------
   var timeWindow = (3600000*24.0);				//Initial time window
   var start = ((new Date()).getTime())-timeWindow;		//Get start time
   var end = (new Date()).getTime();				//Get end time

   var paverage;
   var npoints;
   var tkwh;
   var ndays;

   $(function () {



     var powerGraph = $("#powerGraph");
     var kwhGraph = $("#kwhGraph");

     //----------------------------------------------------------------------------------------
     // Get window width and height from page size
     //----------------------------------------------------------------------------------------
     var width = $('body').width();
     var height = $('body').height()-100;
     if (height<=0) height = 400;

     powerGraph.width(width);
     powerGraph.height(height);

     kwhGraph.width(width);
     kwhGraph.height(height);

     $('#item').width(width);
     $('#date').width(width);
     $('#stat').css("top", height+40)
      
     //----------------------------------------------------------------------------------------

     var power_graph_data = [];                              //data array declaration
     var kwh_graph_data = [];                              //data array declaration

     //getPowerData(table, start, end, 10);
     getkwhData(kwhtable);

     $('#powerGraph').fadeOut();
     //$('#stat').fadeOut();
     //--------------------------------------------------------------------------------------
     // Plot flot graph
     //--------------------------------------------------------------------------------------
     function plotPowerGraph(power_graph_data, powerGraph, start, end)
     {
          $.plot(powerGraph,[                    
          {
            data: power_graph_data ,				//data
            lines: { show: true, fill: true }		//style
            
          }], {

        xaxis: {   mode: "time", 
                  min: ((start)),
		  max: ((end))

        },
        //grid: {backgroundColor: "rgb(240,240,240)" },
        selection: { mode: "xy" }
     } ); 
     }
   

     //--------------------------------------------------------------------------------------
     // Fetch Data
     //--------------------------------------------------------------------------------------
     function getPowerData(table, start, end, resolution)
     {
       $.ajax({                                       //Using JQuery and AJAX
         url: path+'api/getData.php',                         //data.php loads data to be graphed
         data: "&table="+table+"&start="+start+"&end="+end+"&resolution="+resolution,    //
         dataType: 'json',                            //and passes it through as a JSON    
         success: function(data) 
         {

           paverage = 0;
           npoints = 0;

           for (var z in data)                     //for all variables
           {
             paverage += parseFloat(data[z][1]);
             npoints++;
           }  
             var timeB  = Number(data[0][0])/1000.0;
             var timeA  = Number(data[data.length-1][0])/1000.0;

             var timeWindow = (timeB-timeA);
             var timeWidth = timeWindow / npoints;

             var kwhWindow = (timeWidth * paverage)/3600000;

           paverage = paverage / npoints;

           $("#item").html((kwhWindow).toFixed(1)+" kWh | £"+(kwhWindow*price).toFixed(2));

           power_graph_data = [];   
           power_graph_data = data;
           plotPowerGraph(power_graph_data, powerGraph, start, end);
                $('#powerGraph').fadeIn();
         } 
       });
     }

     //--------------------------------------------------------------------------------------
     // Graph zooming
     //--------------------------------------------------------------------------------------
     powerGraph.bind("plotselected", function (event, ranges) 
     {
       // clamp the zooming to prevent eternal zoom
       if (ranges.xaxis.to - ranges.xaxis.from < 0.00001) ranges.xaxis.to = ranges.xaxis.from + 0.00001;
       if (ranges.yaxis.to - ranges.yaxis.from < 0.00001) ranges.yaxis.to = ranges.yaxis.from + 0.00001;
        
       start = ranges.xaxis.from;					//covert into usable time values
       end = ranges.xaxis.to;						//covert into usable time values

       var res = Math.round( ((end-start)/10000000) );			//Calculate resolution
       if (res<1) res = 1;
       $('.inc').html("Resolution: "+res);				//Output resolution

       getPowerData(table, start, end, res);					//Get new data and plot graph
     });

     //----------------------------------------------------------------------------------------------
     // Operate buttons
     //----------------------------------------------------------------------------------------------
     $('.viewWindow').click(function () 
     {
       
       var time = $(this).attr("time");					//Get timewindow from button
       start = ((new Date()).getTime())-(3600000*time);			//Get start time
       end = (new Date()).getTime();					//Get end time
	
       var res = Math.round( ((end-start)/10000000) );			//Calculate resolution
       if (res<1) res = 1;
       $('.inc').html("Resolution: "+res);				//Output resolution

       getData( start, end, res);					//Get new data and plot graph
     });
   //-----------------------------------------------------------------------------------------------

     $('#kwhd').click(function () 
     {
       $('#kwhGraph').fadeIn();
       $('#powerGraph').fadeOut();
       $('#inc').fadeOut();
        $("#item").html("---");
        $("#item").fadeIn();
        $("#date").html("---");
        $("#date").fadeIn();
        $('#stat').fadeIn();
     });

     $('#power').click(function () 
     {
       $('#inc').fadeIn();
       $('#kwhGraph').fadeOut();
       $('#powerGraph').fadeIn();
       $('#stat').fadeOut();
     });
  //--------------------------------------------------------------------------------------
     // Fetch Data
     //--------------------------------------------------------------------------------------
     function getkwhData(table)
     {
       $.ajax({                                      
         url: path+'api/getkwh.php', 
         data: "&table="+table,
         dataType: 'json',                             
         success: function(data) 
         {
           tkwh = 0;
           ndays=0;
           for (var z in data)                     //for all variables
           {
             tkwh += parseFloat(data[z][1]);
             ndays++;
           }   

 $("#stat").html("Total: "+(tkwh).toFixed(0)+" kWh : £"+(tkwh*price).toFixed(0) + " | Average: "+(tkwh/ndays).toFixed(1)+ " kWh : £"+((tkwh/ndays)*price).toFixed(2)+" | £"+((tkwh/ndays)*price*7).toFixed(0)+" a week, £"+((tkwh/ndays)*price*365).toFixed(0)+" a year | Unit price: £"+price);

           kwh_graph_data = [];   
           kwh_graph_data = data;
           plotGraphKWH();
         } 
       });
     }

     function plotGraphKWH()
     {
        $.plot(kwhGraph, [{data: kwh_graph_data}], 
        {
          bars: {
	    show: true,
	    align: "center",
            
	    barWidth: 3600*18*1000,
	    fill: true
          },

            
          grid: { show: true, hoverable: true, clickable: true },
         // grid: {backgroundColor: "rgb(240,240,240)" },
          xaxis: { mode: "time"}
        });
     }

     kwhGraph.bind("plothover", function (event, pos, item) {
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

        if (item!=null)
       {
          var mdate = new Date(item.datapoint[0]);
        

        $("#item").html((item.datapoint[1]).toFixed(1)+" kWh/d | £"+(item.datapoint[1]*price).toFixed(2)+" | £"+(item.datapoint[1]*price*365).toFixed(0)+"/y <br/ >");

          $("#date").html(mdate.format("ddd, mmm dS, yyyy"));
         }
     });

     kwhGraph.bind("plotclick", function (event, pos, item)
     {
       if (item!=null)
       {

        //start = item.datapoint[0];
        var timeWindow = (3600000*24.0);				//Initial time window
        start = item.datapoint[0];
        end = item.datapoint[0]+timeWindow;
        //power_graph_data =[];
        //plotPowerGraph(power_graph_data, powerGraph, start, end);
        getPowerData(table, start, end, 10);				//Get new data and plot graph
        
   
        $('#kwhGraph').fadeOut();

        $("#date").fadeOut();
        $('#stat').fadeOut();
       }
     });
  });
  //--------------------------------------------------------------------------------------
  </script>

  </body>
</html>  
