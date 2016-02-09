 /*
   All Energy stack generator code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Energy stack generator
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */ 

 function drawCat(ctx,x,mov,kwh,text,c)
  {

    if (kwh!=0)
    {
    seg = kwh*8.0;
    mov -=seg;

    if (c==0)
    {
    ctx.fillStyle = "#ffe6e6";
    ctx.strokeStyle = "#ff7373";
    }
    if (c==1)
    {
    ctx.fillStyle = "#f0fff0";
    ctx.strokeStyle = "#78bf78";
    }
    if (c==2)
    {
    ctx.fillStyle = "#fff7f7";
    ctx.strokeStyle = "#ffd3d3";
    }

    if (c==3)
    {
    ctx.fillStyle = "#fdfffd";
    ctx.strokeStyle = "#c3e8c3";
    }

    if (c==4)
    {
    ctx.fillStyle = "#fcffcd";
    ctx.strokeStyle = "#fffc1f";
    }

    if (c==5)
    {
    ctx.fillStyle = "#d2f6d2";
    ctx.strokeStyle = "#78bf78";
    }

    if (c==6)	// Oil
    {
    ctx.fillStyle = "#fff";
    ctx.strokeStyle = "#aaa";
    }

    ctx.fillRect (x, mov, 120, seg-4);
    ctx.strokeRect(x, mov, 120, seg-4);

    if (kwh>3.0)
    {
    ctx.fillStyle    = "rgba(0, 0, 0, 0.5)";
    ctx.textAlign    = "center";
    ctx.font         = "bold 14px arial";
    ctx.fillText(text, x+60,mov+(seg/2)-10+2);  
    ctx.font         = "normal 14px arial"; 
    ctx.fillText((kwh).toFixed(0)+" kWh/d", x+60,mov+(seg/2)+10+2);   
    } else {
    ctx.fillStyle    = "rgba(0, 0, 0, 0.5)";
    ctx.textAlign    = "center";
    ctx.font         = "bold 14px arial";
    ctx.fillText(text+" "+(kwh).toFixed(0), x+60,mov+(seg/2)+2);  
    }
    }
    return mov;
  }

