
  <!--
   All Energy stack generator code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Energy stack generator
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  -->

<html>
  <head>
    <script type="text/javascript" src="stacks.js"></script>
    <script type="text/javascript" src="jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="jquery-ui-1.8.5.custom.min.js"></script>
    <link rel="stylesheet" type="text/css" href="style.css" />
  </head>


  <body>
  
  <div style="font-family: 'Trebuchet MS'; margin:20px;">
  <h2>Energy stack generator</h2>

  <table>
  <tr>
  <td style="vertical-align:top">

  <p><b>Home context</b></p>
  <table>
    <tr>
      <td style="width:400px">How many people live in your house?</td>
      <td><input id="people" type="text" name="kwh" value="1"/></td><td><span class="units"> people</span></td>
    </tr>
    <tr>
      <td style="width:400px">What is the floor area of your house?</td>
      <td><input id="floorarea" type="text" name="kwh" style="width:100px" value="0"/></td><td><span class="units"> m2</span></td>
    </tr>
  </table>

  <p><b>Electricity consumption</b></p>
  <table>
    <tr>
      <td style="width:400px">What is your average annual electricity consumption?</td>
      <td><input id="avelec" type="text" name="kwh" style="width:100px" value="0"/></td><td><span class="units"> kWh/year</span></td>
    </tr>
    <tr>
      <td style="width:400px">Is your tariff a green tariff?</td>
      <td style="width:100px;text-align:right;"><input id="greenelec" type="checkbox" name="" /></td><td></td>
    </tr>
  </table>

  <p><b>Heating</b></p>
  <table>
    <tr>
      <td style="width:400px">What is your average annual heating consumption?</td>
      <td><input id="avheat" type="text" name="kwh" style="width:100px" value="0"/></td><td><span class="units"> kWh/year</span></td>
    </tr>
    <tr>
      <td style="width:400px">What is the efficiency of your heating system?</td>
      <td><input id="heateff" type="text" name="kwh" style="width:100px" value="50"/></td><td><span class="units"> %</span></td>
    </tr>
  </table>

  <p><b>Wood heating</b></p>
  <table>
    <tr>
      <td style="width:400px">How many m3 of wood do you use a year?</td>
      <td><input id="woodm3" type="text" name="kwh" style="width:100px" value="0"/></td><td><span class="units"> m3</span></td>
    </tr>
    <tr>
      <td style="width:400px">What is the efficiency of your wood heating system?</td>
      <td><input id="woodeff" type="text" name="kwh" style="width:100px" value="50"/></td><td><span class="units"> %</span></td>
    </tr>
  </table>

  <p><b>Transport</b></p>
  <table>
    <tr>
      <td style="width:400px">Average annual milage:</td>
      <td><input id="carmilage" type="text" name="kwh" style="width:100px" value="0"/></td><td><span class="units"> miles</span></td>
    </tr>
    <tr>
      <td style="width:400px">Average occupancy:</td>
      <td><input id="carnop" type="text" name="kwh" style="width:100px" value="1"/></td><td><span class="units"> people</span></td>
    </tr>
    <tr>
      <td style="width:400px">Miles-per-gallon:</td>
      <td><input id="mpg" type="text" name="kwh" style="width:100px" value="35"/></td><td><span class="units"> mpg</span></td>
    </tr>
  </table>

  </td>

  <td style="vertical-align:top">
  <h2><div id="out"></div></h2>
   <canvas id="can" width="300" height="600"></canvas> 
  </div>
  </td>
  </tr></table>



  <script type="application/javascript">
  $(function() {

  var canvas = document.getElementById("can");
  var ctx = canvas.getContext("2d");

  $("#people").change(function() { energycalc(); });
  $("#people").click(function()  { energycalc(); });
  $("#avelec").change(function() { energycalc(); });
  $("#avelec").click(function()  { energycalc(); });
  $("#greenelec").click(function()  { energycalc(); });

  $("#avheat").change(function() { energycalc(); });
  $("#avheat").click(function()  { energycalc(); });

  $("#heateff").change(function() { energycalc(); });
  $("#heateff").click(function()  { energycalc(); });

  $("#woodm3").change(function() { energycalc(); });
  $("#woodm3").click(function()  { energycalc(); });

  $("#woodeff").change(function() { energycalc(); });
  $("#woodeff").click(function()  { energycalc(); });

  $("#carmilage").change(function() { energycalc(); });
  $("#carmilage").click(function()  { energycalc(); });
  $("#carnop").change(function() { energycalc(); });
  $("#carnop").click(function()  { energycalc(); });
  $("#mpg").change(function() { energycalc(); });
  $("#mpg").click(function()  { energycalc(); });

  function energycalc()
  {
    var occupancy = $("#people").attr("value");
    var green = $("#greenelec").attr("checked");
    var elec = ($("#avelec").attr("value")/365.0)/occupancy;
    var heat = ($("#avheat").attr("value")/365.0)/occupancy;
    var heateff = $("#heateff").attr("value");

    var woodheat = (($("#woodm3").attr("value")*1380)/365.0)/occupancy;
    var woodeff = $("#woodeff").attr("value");

    var carmilage = $("#carmilage").attr("value");
    var carnop = $("#carnop").attr("value");
    var mpg = $("#mpg").attr("value");

    var kwhpermile = 1.0 / ((mpg / 4.55)/9.7); 
    var carkwh = (carmilage / 365.0 / carnop) * kwhpermile;

    stack(elec,green,heat,heateff,carkwh,woodheat,woodeff);
  }

 

  function stack(elec,green,heat,heateff,carkwh,woodheat,woodeff)
  {
    ctx.clearRect(0,0,300,600);
    var mov; var x = 0;

    x += 50;  mov = 530; 
    mov = drawCat(ctx,x,mov,carkwh,"Car:",0);

    mov = drawCat(ctx,x,mov,heat*(heateff/100),"Heating:",0);
    mov = drawCat(ctx,x,mov,woodheat*(woodeff/100),"Wood:",1);
    mov = drawCat(ctx,x,mov,elec,"Electric:",green);
    mov = drawCat(ctx,x,mov,woodheat*(1.0-(woodeff/100)),"Loss:",3);
    mov = drawCat(ctx,x,mov,heat*(1.0-(heateff/100)),"Loss:",2);
  }

  });
  </script>

  </body>
</html>
