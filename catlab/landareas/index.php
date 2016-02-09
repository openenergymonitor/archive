
  <!---
   All code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    Author: Trystan Lea: trystan.lea@googlemail.com
  --->

<html>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="style2.css" />
    <script language="javascript" type="text/javascript" src="jquery.js"></script>
    <link rel="stylesheet" type="text/css" href="style.css" />
  <script type="text/javascript" src="abox.js"></script>
  </head>
  <body>

  <div id="bound">
    <div id="header">
      <div style="float:left; margin-top:30px; margin-left:20px;">
        <img src="zcb.png" height="30px">
      </div>    
      <div style="float:right; margin-top:30px; margin-right:20px;">
        <img src="powerup.png" height="50px">
      </div>
    </div>

    <div id="maintext"  style="height:1200px;"> 
    <h3>Explore the land area required for producing energy from renewable generation vs the land area used for other activities.</h3>
   
  <div style="float:left; font-size:16px; width:340px; color:#fff;">
  <p style="font-size:16px; color:#666;">Experiment with matching the ZCB 2030 Demand by entering in TWh values for the different energy sources below.</p>
  <p style="font-size:16px; color:#666;">Try moving the land area boxes around on the map</p>
    <canvas id="canstack" width="300" height="400"></canvas>

  <div style="font-size:16px; color:#666;">

  </div>
  <div style="padding:10px; background-color:#645724; margin-bottom:10px;">
  <span>Biomass</span><span style="float:right;"><input id="biomassT" type="text" style="width:100px" /> TWh</span>
  </div>

  <div style="padding:10px; background-color:#69bd45; margin-bottom:10px; ">
  <span style="">Offshore wind</span><span style="float:right;"><input id="offshoreT" type="text" style="width:100px" /> TWh</span>
  </div>

  <div style="padding:10px; background-color:#0c8140;  margin-bottom:10px; ">
  <span style="">Onshore wind</span><span style="float:right;"><input id="onshoreT" type="text" style="width:100px" /> TWh</span>
  </div>

  <div style="padding:10px; background-color:#f3ec19;  margin-bottom:10px; ">
  <span style="">Solar PV</span><span style="float:right;"><input id="solarT" type="text" style="width:100px" /> TWh</span>
  </div>
  <br/>
  <div style="padding:10px; background-color:#d2609e;  margin-bottom:10px; ">
  <span style=""><b>ZCB 2030 Land use areas</b></span>
  <p>Urban<span style="float:right;"><input id="urbanT" type="text" style="width:100px" value="22800"/> km2</span></p>
  <p>Woodland<span style="float:right;"><input id="woodlandT" type="text" style="width:100px" value="43650" /> km2</span></p>
  <p>Coppice and miscanthus<span style="float:right;"><input id="coppiceT" type="text" style="width:100px" value="54500" /> km2</span></p>
  <p>Livestock feed<span style="float:right;"><input id="feedT" type="text" style="width:100px" value="7250" /> km2</span></p>
  <p>Livestock<span style="float:right;"><input id="livestockT" type="text" style="width:100px" value="19470" /> km2</span></p>
  <p>Direct crops<span style="float:right;"><input id="directT" type="text" style="width:100px" value="37450" /> km2</span></p>
  <p>Total<span id="totalT" style="float:right;"></span></p>
  </div>

  <p style="color:#666;">Fixed tidal, Wave & Tidal stream and Hydro are not included for now as they are more difficult to represent with an area box</p>

  </div>

  <canvas id="canvas" width="550" height="800" style="float:right;"></canvas>

 

  <div id="out"></div>

  <script id="source" language="javascript" type="text/javascript">
  $(function () 
  {

    var canvas2 = document.getElementById("canstack");
    var ctx2 = canvas2.getContext("2d");
    setctx(canvas2,ctx2);

    var canvas = document.getElementById("canvas");
    var ctx = canvas.getContext("2d");

    canvas.addEventListener('mousemove', mouse_move, false);
    canvas.addEventListener('mousedown', mouse_down, false);
    canvas.addEventListener('mouseup', mouse_up, false);

    var x=0,y=0,lx=0,ly=0,mx=0,my=0,mdown;
    var scale = 1.5;

    var mbox = [];
    //-------------------------
    var newbox = [];
    newbox[0] = 100;	// x
    newbox[1] = 100;	// y
    newbox[2] = 0;	// size
    newbox[3] = "Offshore Wind";
    newbox[4] = 0;	// size
    newbox[5] = "#69bd45";	// color
    newbox[6] = 1;	// units
    mbox[0] = newbox;

    newbox = [];
    newbox[0] = 200;	// x
    newbox[1] = 1050;	// y
    newbox[2] = 0;	// size
    newbox[3] = "Solar";
    newbox[4] = 0;	// size
    newbox[5] = "#f3ec19";	// color
    newbox[6] = 1;	// units
    mbox[1] = newbox;

    newbox = [];
    newbox[0] = 100;	// x
    newbox[1] = 300;	// y
    newbox[2] = 0;	// size
    newbox[3] = "Biomass";
    newbox[4] = 0;	// size
    newbox[5] = "#645724";	// color
    newbox[6] = 1;	// units
    mbox[2] = newbox;

    newbox = [];
    newbox[0] = 300;	// x
    newbox[1] = 100;	// y
    newbox[2] = 0;	// size
    newbox[3] = "Onshore Wind";
    newbox[4] = 0;	// size
    newbox[5] = "#0c8140";	// color
    newbox[6] = 1;	// units
    mbox[3] = newbox;

    newbox = [];
    newbox[0] = 550;	// x
    newbox[1] = 420;	// y
    newbox[3] = "coppice + miscanthus";	
    newbox[4] = 0;	// size
    newbox[5] = "#d2609e";	// color
    newbox[6] = 2;	// units
    mbox[4] = newbox;

    newbox = [];
    newbox[0] = 550;	// x
    newbox[1] = 720;	// y
    newbox[3] = "woodland";	
    newbox[4] = 0;	// size
    newbox[5] = "#d2609e";	// color
    newbox[6] = 2;	// units
    mbox[5] = newbox;

    newbox = [];
    newbox[0] = 350;	// x
    newbox[1] = 500;	// y
    newbox[3] = "livestock";	
    newbox[4] = 0;	// size
    newbox[5] = "#d2609e";	// color
    newbox[6] = 2;	// units
    mbox[6] = newbox;

    newbox = [];
    newbox[0] = 350;	// x
    newbox[1] = 350;	// y
    newbox[3] = "livestock feed";	
    newbox[4] = 0;	// size
    newbox[5] = "#d2609e";	// color
    newbox[6] = 2;	// units
    mbox[7] = newbox;

    newbox = [];
    newbox[0] = 300;	// x
    newbox[1] = 800;	// y
    newbox[3] = "direct crops";	
    newbox[4] = 0;	// size
    newbox[5] = "#d2609e";	// color
    newbox[6] = 2;	// units
    mbox[8] = newbox;

    newbox = [];
    newbox[0] = 600;	// x
    newbox[1] = 1000;	// y
    newbox[3] = "urban";	
    newbox[4] = 0;	// size
    newbox[5] = "#d2609e";	// color
    newbox[6] = 2;	// units
    mbox[9] = newbox;

    var selected_box = -1;

    //-------------------------
 
    ctx.fillStyle = "rgb(255, 250, 250)";
    ctx.fillRect(0,0,550,800);

    var img = new Image();
    img.src = 'map.jpg';


    get_twh_values();
    get_land_values();
    drawStack();
    setInterval ( theloop, 1000 );

    function theloop()
    {
      ctx.fillStyle = "rgb(230, 230, 230)";
      ctx.fillRect(0,0,550,800);
      ctx.drawImage(img,mx,my,760/scale,1200/scale);

      for (i in mbox)
      {
        drawblock(mbox[i][0],mbox[i][1],mbox[i][2],mbox[i][3],mbox[i][4],mbox[i][5],mbox[i][6]);
      }
    }

    $("#offshoreT").change(get_twh_values);
    $("#solarT").change(get_twh_values);
    $("#biomassT").change(get_twh_values);
    $("#onshoreT").change(get_twh_values);

    $("#offshoreT").click(get_twh_values);
    $("#solarT").click(get_twh_values);
    $("#biomassT").click(get_twh_values);
    $("#onshoreT").click(get_twh_values);



    $("#urbanT").change(get_land_values);
    $("#woodlandT").change(get_land_values);
    $("#coppiceT").change(get_land_values);
    $("#feedT").change(get_land_values);
    $("#livestockT").change(get_land_values);
    $("#directT").change(get_land_values);

    $("#urbanT").click(get_land_values);
    $("#woodlandT").click(get_land_values);
    $("#coppiceT").click(get_land_values);
    $("#feedT").click(get_land_values);
    $("#livestockT").click(get_land_values);
    $("#directT").click(get_land_values);

  function get_twh_values()
  {

    var twh = $("#offshoreT").attr("value");
    mbox[0][2] = twh_to_km(twh,3);
    mbox[0][4] = twh;

    var twh = $("#solarT").attr("value");
    mbox[1][2] = twh_to_km(twh,10);
    mbox[1][4] = twh;

    var twh = $("#biomassT").attr("value");
    mbox[2][2] = twh_to_km(twh,0.5);
    mbox[2][4] = twh;

    var twh = $("#onshoreT").attr("value");
    mbox[3][2] = twh_to_km(twh,3);
    mbox[3][4] = twh;

    drawStack();
    theloop();
  }


  function get_land_values()
  {
    var km2 = $("#urbanT").attr("value");
    mbox[9][2] = get_km_land_km2(km2);
    mbox[9][4] = parseInt(km2,10);

    km2 = $("#woodlandT").attr("value");
    mbox[5][2] = get_km_land_km2(km2);
    mbox[5][4] = parseInt(km2,10);

    km2 = $("#coppiceT").attr("value");
    mbox[4][2] = get_km_land_km2(km2);
    mbox[4][4] = parseInt(km2,10);

    km2 = $("#feedT").attr("value");
    mbox[7][2] = get_km_land_km2(km2);
    mbox[7][4] = parseInt(km2,10);

    km2 = $("#livestockT").attr("value");
    mbox[6][2] = get_km_land_km2(km2);
    mbox[6][4] = parseInt(km2,10);

    km2 = $("#directT").attr("value");
    mbox[8][2] = get_km_land_km2(km2);
    mbox[8][4] = parseInt(km2,10);

    var total = parseInt(mbox[9][4],10) + parseInt(mbox[5][4],10) + parseInt(mbox[4][4],10) + parseInt(mbox[7][4],10) + parseInt(mbox[6][4],10) + parseInt(mbox[8][4],10);
    $("#totalT").html("<b>"+total+" / 241590 km2</b>")
    //$("#totalT").html("TEST")

    drawStack();
    theloop();
  }

  function drawStack()
  {
    
    reset();
    anchorStack(20,380,0.3);
    add(4,"Demand",902,0);

    anchorStack(140,380,0.3);
    add(0,"Biomass",mbox[2][4],0);
    add(1,"Offshore Wind",mbox[0][4],0);
    add(2,"Onshore Wind",mbox[3][4],0);
    add(3,"Solar",mbox[1][4],0);
    abox_draw();
    
  }

  function drawblock(in_x,in_y,in_size,line1,line2,color,units)
  {
    if (!in_size) in_size=0;
    if (in_size!=0)
    {
      var cx = (in_x/scale)+mx;
      var cy = (in_y/scale)+my;
      var csize = in_size / scale;
 
      ctx.fillStyle = color;
      ctx.fillRect(cx,cy,csize,csize);
      ctx.fillStyle = "rgba(255,255,255,0.5)";
      ctx.fillRect(((in_x+5)/scale)+mx,((in_y+5)/scale)+my,(in_size-10)/scale,(in_size-10)/scale);

      
      ctx.fillStyle    = "rgba(0, 0, 0, 0.5)";
      ctx.textAlign    = "center";
      ctx.font         = "bold "+14/scale+"px arial";
      ctx.fillText(line1, cx+(csize/2),cy+(csize/2)-(10/scale));  
      if (units==1) {if (line2) ctx.fillText(line2+" TWh", cx+(csize/2),cy+(csize/2)+(10/scale));  }
      if (units==2) {if (line2) ctx.fillText(line2+" km2", cx+(csize/2),cy+(csize/2)+(10/scale));  }
    }
  }

  function kwhdp_to_km(kWhd_pp,e_density)
  {
    var kWhd_uk = kWhd_pp * 62000000;
    var W_uk = 1000* ( kWhd_uk / 24.0 )
    var m2 = W_uk / e_density;
    var km2 = m2 / 1000000;
    var km = Math.sqrt(km2);
    var px = km * 1.15;
    return px;
  }

  function twh_to_km(twh,e_density)
  {
    var kWhd_uk = ((twh * 1000000000)/365.0);
    var W_uk = 1000* ( kWhd_uk / 24.0 )
    var m2 = W_uk / e_density;
    var km2 = m2 / 1000000;
    var km = Math.sqrt(km2);
    var px = km * 1.15;
    return px;
  }

  function get_km_land(m2_pp)
  {
    var m2 = m2_pp*62000000;
    var km2 = m2 / 1000000;
    var km = Math.sqrt(km2);
    var px = km * 1.15;
    return px;
  }

  function get_km_land_km2(km2)
  {
    var km = Math.sqrt(km2);
    var px = km * 1.15;
    return px;
  }



function mouse_move (ev) {
 
  lx = x; ly = y;
  x = ev.layerX - canvas.offsetLeft;
  y = ev.layerY - canvas.offsetTop;
  var dx = x-lx;
  var dy = y-ly;

  if (mdown==1)
  {
    //-------------------------------------------------------------------------------
    // Handle selection and move of boxes
    //-------------------------------------------------------------------------------
    var onbox = 0; 	// variable to register if a any box was selected
    for (i in mbox)	// For all boxes
    {
      if (selected_box==-1 && inbox(x,y,mbox[i][0],mbox[i][1],mbox[i][2]) == 1)	// if selected
      {
        selected_box = i;
        onbox = 1;						// a box was selected
      }
    }
    //-------------------------------------------------------------------------------

   // if (onbox == 0) { mx += dx;  my += dy; }			// if no box was selected: move the map

    if (selected_box!=-1)
    {
        mbox[selected_box][0] += (dx*scale);				// Move mbox x
        mbox[selected_box][1] += (dy*scale);				// Move box y	

    }

    theloop();							// redraw as either the boxes or the map has moved
  }

}

function mouse_down (ev) {
  mdown = 1;
}

function mouse_up (ev) {
  mdown = 0;
  selected_box = -1;
}

        function inbox(x,y,left,top,size)
        {
          left = (left/scale)+mx;
          width = size/scale;

          top = (top/scale)+my;
          height = size/scale;

          if ( ( (x>=left) && (x<=(left+width)) ) && ( (y>=top) && (y<=(top+height)) ) ) return 1; else return 0;
        }

  });
  </script>

  
    </div>
    <div id="footer"></div>
  </div>
   
  </body>
</html>
