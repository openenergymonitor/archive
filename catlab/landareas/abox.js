/*
   All code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    Author: Trystan Lea: trystan.lea@googlemail.com
*/

      var box = [];

      var stack_scale;
      var stack_x;
      var stack_y;
      var stack_width;

      var focus = -1;
      var ctx, canvas;


      //---------------------------------------


      //-----------------------------------------

      function reset()
      {
        box = [];
      }

      function set_focus(f)
      {
        focus = f;
      }
      function setctx(icanvas, ictx)
      {
        canvas = icanvas;
        ctx = ictx;
      }

      function anchorStack(sx,sy,scale)
      {
        stack_scale = scale
        stack_x = sx;
        stack_y = sy;
        stack_width = 100;
      }

      function add(id,text,kwhd,color)
      {
        var seg_size = kwhd*stack_scale;
        stack_y = stack_y - seg_size;
        abox(id,text, kwhd, stack_x, stack_y, stack_width, seg_size-4,color); 
      }

      function abox(id, text, kwhd, top, left, width, height, color)
      {
        var newbox = [];
        newbox[0]=top;
        newbox[1]=left;
        newbox[2]=width;
        newbox[3]=height;
        newbox[4]=color;
        newbox[5]=text;
        newbox[6]=kwhd;
        box[id] = newbox;
      }

      function abox_color(id, color)
      {
        box[id][4] = color;
      }

      function abox_draw()
      {

        ctx.clearRect(0,0,250,390);
        for (i in box)
        {
          
          if (box[i][3] > 0)
          {
            if (box[i][4]==0) { ctx.fillStyle = "#ffe6e6"; ctx.strokeStyle = "#ff7373"; }

            if (box[i][4]==1) ctx.fillStyle = "#ffd6d6";
            ctx.strokeRect (box[i][0], box[i][1], box[i][2], box[i][3]);
            ctx.fillRect (box[i][0], box[i][1], box[i][2], box[i][3]);
 
            if (focus == -1 || focus == i)
            {
              ctx.textAlign    = "center";
              ctx.fillStyle = "#666";
              if (box[i][3]>10)
              {
                if (box[i][3]>20)
                {
                  ctx.font = "bold 14px arial";
                  ctx.fillText(box[i][5], box[i][0] + (box[i][2]/2) ,box[i][1]+(box[i][3]/2)+2-10); 
                  ctx.font = "normal 14px arial";
                  ctx.fillText((box[i][6])+" TWh", box[i][0] + (box[i][2]/2),box[i][1]+(box[i][3]/2)+2+10);
                } else {
                  ctx.font = "normal 14px arial";
                  ctx.fillText(box[i][5],box[i][0]+(box[i][2]/2),box[i][1]+(box[i][3]/2)+5);
                }
              }
            }
          }  
        }
      }

      function abox_hover(x,y)
      {

        var id = -1;
        for (i in box)
        { 
          box[i][4] = 0;
          if ( ( (x>=box[i][0]) && (x<=(box[i][0]+box[i][2])) ) && ( (y>=box[i][1]) && (y<=(box[i][1]+box[i][3])) ) ) {abox_color(i,1); id=i;}
        }
        return id;
      }

      //--------------------------------------------------
      // Handle mouse move and click
      //--------------------------------------------------


