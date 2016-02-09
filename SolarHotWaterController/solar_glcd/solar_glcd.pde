//--------------------------------------------------------------------------------------
// GLCD Energy Monitor Display example
//
// All hard gaphics library building work done by Jean-Claude Wippler: Jee Labs
// 2010-05-28 <jcw@equi4.com> http://opensource.org/licenses/mit-license.php
//
// Solar hot water specific example by Trystan Lea and Glyn Hudson
// OpenEnergyMonitor.org
//--------------------------------------------------------------------------------------
#include <OneWire.h>
#include <DallasTemperature.h>
#include <GLCD_ST7565.h>
#include <Ports.h>
#include <RF12.h> // needed to avoid a linker error :(
#include <avr/pgmspace.h>
#include "utility/font_clR6x8.h"
#include "utility/font_clR4x6.h"
#include "utility/font_clR6x6.h"
#include "utility/font_courB18.h"
#include "utility/font_ncenBI14.h"
#include "utility/font_ncenR08.h"


GLCD_ST7565 glcd;

// fixed RF12 settings
#define MYNODE 28            //node ID 30 reserved for base station
#define freq RF12_433MHZ     //frequency
#define group 61             //network group 

#define ONE_WIRE_BUS 5      //temperature sensor connection 

//########################################################################################################################
//Data Structure to be received 
//########################################################################################################################
typedef struct {
  	  int COL;		// current transformer 1
	  int CYLT;		// emontx voltage
	  int CYLB;		// emontx voltage
          int error;		// emontx voltage
} Payload;
Payload emontx;

int emontx_nodeID;    //node ID of emon tx, extracted from RF datapacket. Not transmitted as part of structure
//###############################################################

unsigned long last;
unsigned long lastTemp;

OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);
double temp=0;

double COL,CYLB,CYLT;

void setup () {
    rf12_initialize(MYNODE, freq,group);
    
    glcd.begin();
    glcd.backLight(255);
    last = millis();
    
    sensors.begin(); //start up temp sensor
    
    Serial.begin(9600);
    Serial.println("emonGLCD example 02");
  Serial.println("openenergymonitor.org");
  
  Serial.print("Node: "); 
  Serial.print(MYNODE); 
   Serial.print(" Freq: "); 
 if (freq == RF12_433MHZ) Serial.print("433Mhz");
 if (freq == RF12_868MHZ) Serial.print("868Mhz");
 if (freq == RF12_915MHZ) Serial.print("915Mhz"); 
  Serial.print(" Network: "); 
  Serial.println(group);
  
   pinMode(8, OUTPUT); //green 
 pinMode(9, OUTPUT);   //red
 
 //get inital temperature reading
 sensors.requestTemperatures();
 temp=(sensors.getTempCByIndex(0));
}

void loop () {

   //--------------------------------------------------------------------
    // 1) Receive data from RFM12
    //--------------------------------------------------------------------
    if (rf12_recvDone() && rf12_crc == 0 && (rf12_hdr & RF12_HDR_CTL) == 0) {
        emontx=*(Payload*) rf12_data;   
       // emontx_nodeID=rf12_hdr & 0x1F;   //extract node ID from received packet 
        last = millis();
        
        COL = emontx.COL/100.0;
        CYLB = emontx.CYLB/100.0;
        CYLT = emontx.CYLT/100.0;
    }
    
    //get data from temp sensor every 10s
    if (millis()>lastTemp+10000){
   sensors.requestTemperatures();
   temp=(sensors.getTempCByIndex(0));
   lastTemp=millis();
   }
    
   glcd.setFont(font_clR6x8);
   //glcd.drawString(0,0,"emonGLCD");
   glcd.drawString(0,0,"Solar Hot Water");
   glcd.drawString(0,18,"CYLT:");
   
   char str[50];

   //cval=cval+1;
   dtostrf(CYLT,0,1,str); 
   strcat(str,"C");
   //glcd.setFont(font_courB18); //non-italic font
   glcd.setFont(font_ncenBI14); //italic font
   glcd.drawString(40,10,str);
   
   //glcd.setFont(font_clR6x8); bigger font
   glcd.setFont(font_clR6x6); //use smaller font
   
   
   glcd.drawString(0,40, "CYLB: ");
   dtostrf(CYLB,0,1,str); 
   strcat(str,"C");
   glcd.drawString(35,40,str);
   
   
   glcd.drawString(0,48, "COL: ");
   dtostrf(COL,0,1,str); 
   strcat(str,"C");
   glcd.drawString(35,48,str);
   
   glcd.drawString(85,40, "Room");
   dtostrf(temp,0,1,str); 
   strcat(str,"C");
   glcd.drawString(85,48,str);
   
   glcd.setFont(font_clR4x6); //select even smaller font
    //last updated
   glcd.drawString(0,57, "Last update: ");
   int seconds = (int)((millis()-last)/1000.0);
   itoa(seconds,str,10);
   strcat(str,"s ago");
   glcd.drawString(50,57,str);
   
   //draw power bar
   glcd.drawRect(0, 29, 127, 7, WHITE);
   glcd.fillRect(0, 29, (CYLT/50.0 * 125), 7, WHITE); //bar fully black aat 3Kw
   
   //updat
   glcd.refresh();
   glcd.clear();
   
   
   //turn LED from green > red when power goes over 1Kw
   if (CYLT>50){
     digitalWrite(8, LOW);    // set the red LED off
     digitalWrite(9, HIGH);    // set the green LED on
   }
   else 
   {
     digitalWrite(9, LOW);    // set the green LED off
     digitalWrite(8, HIGH);    // set the red LED on
   }
     
}
