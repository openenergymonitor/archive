//GLCD fonts - part of GLCD lib
#include "utility/font_helvB24.h" 	//big numberical digits 
#include "utility/font_helvB12.h" 	//big numberical digits 
#include "utility/font_clR6x8.h" 	//title
#include "utility/font_clR4x6.h" 	//kwh

double cval, cval2, cval3, cval4;   //values to calulate smoothing

void draw_main_screen()
{
  glcd.clear();
  glcd.fillRect(0,0,128,64,0);
  
  glcd.drawLine(0, 47, 128, 47, WHITE);     //middle horizontal line 
                
  char str[50];    			 //variable to store conversion 
  glcd.setFont(font_clR6x8);             
  glcd.drawString_P(0,0,PSTR("POWER NOW:"));
  glcd.drawString_P(0,38,PSTR("ENERGY TODAY:"));
   
  glcd.setFont(font_helvB24);  		//big bold font
                 
  cval = cval + (consuming - cval)*0.50;
  itoa((int)cval,str,10);
  strcat(str,"w");   
  glcd.drawString(3,9,str);     		//ammount of power currently being used 
               
  glcd.setFont(font_clR6x8);
  if (wh_consuming<10000) dtostrf((wh_consuming/1000),0,1,str); else itoa((int)(wh_consuming/1000),str,10);
  strcat(str,"kWh");   
  glcd.drawString(87,38,str);    		//pv
  
  glcd.setFont(font_helvB12);  		//big bold font             
  dtostrf(temp,0,1,str); 
  strcat(str,"C");
  glcd.drawString(0,50,str);  
               
  glcd.setFont(font_clR4x6);   		//small font - Kwh
                             
  itoa((int)mintemp,str,10);
  strcat(str,"C");
  glcd.drawString_P(46,51,PSTR("MIN"));
  glcd.drawString(62,51,str);
               
  itoa((int)maxtemp,str,10); 
  strcat(str,"C");
  glcd.drawString_P(46,59,PSTR("MAX"));
  glcd.drawString(62,59,str);
  
  DateTime now = RTC.now();
  char str2[5];
  itoa((int)now.hour(),str,10);
  if  (now.minute()<10) strcat(str,":0"); else strcat(str,":");
  itoa((int)now.minute(),str2,10);
  strcat(str,str2); 
               
  glcd.setFont(font_helvB12);  		//big bold font   
  glcd.drawString(88,50,str);  

  //if ((millis()-last_emontx)>10000) glcd.drawString_P(32,58,PSTR("RF fail"));

  glcd.refresh();
                    
}

void draw_page_two()
{
  glcd.clear;
  glcd.fillRect(0,0,128,64,0);
  
  glcd.setFont(font_clR6x8);
  glcd.drawString_P(2,0,PSTR("Current Time:"));
               
  DateTime now = RTC.now();
  char str[20];
  char str2[5];
  itoa((int)now.hour(),str,10);
  strcat(str,":");   
  itoa((int)now.minute(),str2,10);
  strcat(str,str2); 
               
  glcd.setFont(font_helvB12);  		//big bold font   
  glcd.drawString(2,10,str);  

  glcd.refresh();
  
}

void backlight_control()
{
  //--------------------------------------------------------------------
  // Turn off backlight and indicator LED's between 12pm and 6am
  //-------------------------------------------------------------------- 
  DateTime now = RTC.now();
  int hour = now.hour();                  //get hour digit in 24hr from software RTC
   
  if ((hour > 1) &&  (hour < 6)) {
    night=1; 
    glcd.backLight(0);
  } else {
    night=0; 
    glcd.backLight(200); 
  }
}

//--------------------------------------------------------------------
//Change color of LED on top of emonGLCD, red if consumption exceeds gen or green if gen is greater than consumption 
//-------------------------------------------------------------------- 
void led_control()
{
  if ((gen>0) && (night==0)) {
    if (gen > consuming) {  //show green LED when gen>consumption   
      digitalWrite(greenLED, HIGH);    
      digitalWrite(redLED, LOW); 
    } else { //red if consumption>gen
      digitalWrite(redLED, HIGH);   
      digitalWrite(greenLED, LOW);    
    }
  } else{ //Led's off at night and when solar PV is not generating
    digitalWrite(redLED, LOW);
    digitalWrite(greenLED, LOW);
  }
}
