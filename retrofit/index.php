<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<!----------------------------------------
By Trystan @openenergymon

next step:
- installation costs
- financial payback
- retro...fit theme?
----------------------------------------->
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script language="javascript" type="text/javascript" src="jquery.js"></script>

<style>
td { border-style:dotted; }
th { border-style:dotted; }

.inp01 {
  text-align: left; 
  color: #F78623; 
  border: 1px solid #DDD; 
  font-size:16px;
  padding:5px;
  background-color: rgb(250,250,250);
  margin-top:2px
}

body {
  background-color:#ecf2f3;
  font-family:helvetica;
  color:#888;
  padding:20px;
}

#titleblue {
  color:#00afd8;
  font-size: 20px;
  margin-bottom:5px;
}

#top {
width:100%;
height:200px;
background-color:#fff;

}

#kwhd {
  color:#00afd8;
  font-size: 180px;
}

</style>

</head>

<body>
<div style="float:left;">
<div id="titleblue" >Home dimensions</div>
<table>
<tr><td>Width</td><td><input class="inp01" type="text" id="width" style="width:110px"/></td><td>m</td></tr>
<tr><td>Length</td><td><input class="inp01" type="text" id="length" style="width:110px"/></td><td>m</td></tr>
<tr><td>Floors</td><td><input class="inp01" type="text" id="floors" style="width:110px"/></td><td></td></tr>
</table>
</div>

<div style="float:left; margin-left:40px;">
<div id="titleblue" >Insulation</div>
<table>
<tr><td>Loft insulation</td><td><input class="inp01" type="text" id="loftins" style="width:110px"/> mm</td></tr>
<tr><td>Wall insulation</td><td><input class="inp01" type="text" id="wallins" style="width:110px"/> mm</td></tr>
<tr><td>Floor insulation</td><td><input class="inp01" type="text" id="floorins" style="width:110px"/> mm</td></tr>
</table>
</div>

<div style="float:left; margin-left:40px;">
<br/><br/>
<table>
<tr><td>
<span id="titleblue">Draughts</span></td><td>
<input class="inp01" type="text" id="draughts" style="width:110px"/> air change/hour (1-4)<br/>
</td></tr>
<tr><td><br/></td></tr>
<tr><td>
<span id="titleblue">Temperature</span></td><td>
<input class="inp01" type="text" id="indoortemp" style="width:110px"/> C<br/>
</td></tr>
</table>
</div>

<div style="clear:both"></div><br/><br/>

<div style="float:left;">
<table border="1" cellpadding="10" style="border-style:dotted" width="350px">
<tr><th width="120px"></th><th>U values</th><th>Loss</th></tr>
<tr><td>Roof</td><td><span id="roofUV"></span></td><td><span id="roofWK"></span> W/K</td></tr>
<tr><td>Walls</td><td><span id="wallsUV"></span></td><td><span id="wallsWK"></span> W/K</td></tr>
<tr><td>Floor</td><td><span id="floorUV"></span></td><td><span id="floorWK"></span> W/K</td></tr>
<tr><td>Infiltration</td><td><span id="ac"></span> AC/h</td><td><span id="infWK"></span> W/K</td></tr>
<tr><td></td><td><b>TOTAL</b></td><td><b><span id="totalWK"></span> W/K</b></td></tr>
</table>
</div>
<div style="float:left; margin-left:40px;">
<div id="titleblue">Daily heating demand:</div><span id="kwhd"></span> <span style="font-size:32px">kWh/d</span>
</div>

<div style="float:right; margin-right:40px;">
<b>Heating costs</b><br/>
£<span id="yearcost"></span>/year<br/><br/>
<b>Over 20 years:</b><br/> £<span id="20yearcost"></span><br/><br/>
@ 4.3p/kWh

<br/><br/><b>Installation costs</b><br/>
Loft ins: £100-£350<br/>
Wall ins: £5500-£8500<br/>
Floor ins: £100-£770<br/>
Drafts proof: £120-£240<br/>

</div>

<script type="application/javascript">

// insulated cavity

 var house_width = 7;
 var house_length = 7;
 var house_floors = 2;

 var roof_ins = 100; 		// insulation mm
 var floor_ins = 0; 		// insulation mm - 6inch concrete
 var wall_ins = 50; 		// insulation mm

 var draughts = 2; 		// out of 5

 var inside_temp = 18;

 $("#width").val(house_width);
 $("#length").val(house_length);
 $("#floors").val(house_floors);
 $("#draughts").val(draughts);

 $("#loftins").val(roof_ins);
 $("#wallins").val(wall_ins);
 $("#floorins").val(floor_ins);

 $("#indoortemp").val(inside_temp);

 calcs();

 $("#width").keyup(function(){
   house_width = $("#width").val(); calcs();
 });

 $("#length").keyup(function(){
   house_length = $("#length").val(); calcs();
 });

 $("#floors").keyup(function(){
   house_floors = $("#floors").val(); calcs();
 });

 $("#floorins").keyup(function(){
   floor_ins = $("#floorins").val(); calcs();
 });

 $("#wallins").keyup(function(){
   wall_ins = $("#wallins").val(); calcs();
 });

 $("#loftins").keyup(function(){
   roof_ins = $("#loftins").val(); calcs();
 });

 $("#draughts").keyup(function(){
   draughts = $("#draughts").val(); calcs();
 });

 $("#indoortemp").keyup(function(){
   inside_temp = $("#indoortemp").val(); calcs();
 });

 function calcs()
 {
 //-----------------------------------------------------------------------------------
 // CALCULATIONS
 //-----------------------------------------------------------------------------------
 // Lengths
 var house_height = house_floors * 2.45;

 // Areas
 var wall_area = (2 * house_width * house_height) + (2 * house_length * house_height);
 var floor_area = house_length * house_width;
 var roof_area = house_length * house_width;

 // Volume
 var house_volume = house_width * house_length * house_height;
 
 // Calculations are based on approximate method as outlined on page 180
 // of The Whole House Book by Cindy Harris and Pat Borer.
 var wall_UV =  1/((1/2.2)  + (1/(0.035/(wall_ins/1000)))  );
 var floor_UV = 1/((1/0.75) + (1/(0.035/(floor_ins/1000))) );
 var roof_UV =  1/((1/2.0)  + (1/(0.035/(roof_ins/1000)))  );

 var wall_WK = wall_UV * wall_area;
 var floor_WK = floor_UV * floor_area;
 var roof_WK = roof_UV * roof_area;

 var infiltration = draughts * house_volume;
 var infiltration_loss = (1.2*infiltration*1010)/3600;

 var heat_loss = wall_WK + floor_WK + roof_WK + infiltration_loss;

 var delta = inside_temp - 10;
 var total_heat_loss = delta * heat_loss;
 var kwhd = total_heat_loss * 24 * 0.001;
 //-----------------------------------------------------------------------------------

 $("#roofUV").html(roof_UV.toFixed(2));
 $("#wallsUV").html(wall_UV.toFixed(2));
 $("#floorUV").html(floor_UV.toFixed(2));

 $("#roofWK").html(roof_WK.toFixed(0));
 $("#wallsWK").html(wall_WK.toFixed(0));
 $("#floorWK").html(floor_WK.toFixed(0));

 $("#infWK").html(infiltration_loss.toFixed(0));
 $("#ac").html(draughts);

 $("#totalWK").html(heat_loss.toFixed(0));

 $("#kwhd").html((kwhd).toFixed(0));
 $("#yearcost").html(((kwhd/0.9)*0.043*365).toFixed(0));
 $("#20yearcost").html(((kwhd/0.9)*0.043*365*20).toFixed(0));

 console.log("Roof "+roof_area.toFixed(1)+"m2 | "+roof_UV.toFixed(2)+" "+roof_WK.toFixed(0)+"W/K");
 console.log("Walls "+wall_area.toFixed(1)+"m2 | "+wall_UV.toFixed(2)+" | "+wall_WK.toFixed(0)+"W/K");
 console.log("Floor "+floor_area.toFixed(1)+"m2 | "+floor_UV.toFixed(2)+" | "+floor_WK.toFixed(0)+"W/K");
 console.log("House volume "+house_volume.toFixed(0)+" | "+draughts+"ac/h | "+infiltration_loss.toFixed(0)+"W/K");
 console.log("Total heat loss: "+heat_loss.toFixed(0)+"W/K");
 console.log("Daily Space heating requirements: "+kwhd.toFixed(0)+"kWh/d");
 }

</script>

</body>
</html>
