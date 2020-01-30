//------------------------------------------------------------------------------------------------------------------------------------------------
// emonGLCD Single CT example
// to be used with nanode auto update time base example

// emonGLCD documentation http://openEnergyMonitor.org/emon/emonglcd

// For use with emonTx setup with one CT
// RTC to reset Kwh counters at midnight is implemented is software. 
// Correct time is updated via NanodeRF which gets time from internet
// Temperature recorded on the emonglcd is also sent to the NanodeRF for online graphing

// GLCD library by Jean-Claude Wippler: JeeLabs.org
// 2010-05-28 <jcw@equi4.com> http://opensource.org/licenses/mit-license.php
//
// Authors: Glyn Hudson and Trystan Lea
// Part of the: openenergymonitor.org project
// Licenced under GNU GPL V3
// http://openenergymonitor.org/emon/license

//-------------------------------------------------------------------------------------------------------------------------------------------------
#define DEBUG

#include <OneWire.h>		    // http://www.pjrc.com/teensy/td_libs_OneWire.html
#include <DallasTemperature.h>      // http://download.milesburton.com/Arduino/MaximTemperature/ (3.7.2 Beta needed for Arduino 1.0)

//JeeLab libraires		       http://github.com/jcw
#include <JeeLib.h>		    // ports and RFM12 - used for RFM12B wireless
#include <RTClib.h>                 // Real time clock (RTC) - used for software RTC to reset kWh counters at midnight
#include <Wire.h>                   // Part of Arduino libraries - needed for RTClib

#include <GLCD_ST7565.h>            // Graphical LCD library 
#include <avr/pgmspace.h>           // Part of Arduino libraries - needed for GLCD lib

GLCD_ST7565 glcd;
 
#define ONE_WIRE_BUS 5              // temperature sensor connection - hard wired 
const int greenLED=8;               // Green tri-color LED
const int redLED=9;                 // Red tri-color LED
const int switchpin=15;		    // digital pin of onboard pushswitch 
const int LDRpin=4;    		    // analog pin of onboard lightsensor 

//--------------------------------------------------------------------------------------------
// RFM12B Setup
//--------------------------------------------------------------------------------------------
#define MYNODE 20            //Should be unique on network, node ID 30 reserved for base station
#define freq RF12_868MHZ     //frequency - match to same frequency as RFM12B module (change to 868Mhz or 915Mhz if appropriate)
#define group 210            //network group, must be same as emonTx and emonBase

//---------------------------------------------------
// Data structures for transfering data between units
//---------------------------------------------------
typedef struct { int hour, mins, power; } PayloadBase;
PayloadBase emonbase;
//---------------------------------------------------

//--------------------------------------------------------------------------------------------
// Power variables
//--------------------------------------------------------------------------------------------
int importing, night;                                  //flag to indicate import/export
double consuming, gen, grid, wh_gen, wh_consuming;     //integer variables to store ammout of power currenty being consumed grid (in/out) +gen
unsigned long whtime;                    	       //used to calculate energy used per day (kWh/d)

//--------------------------------------------------------------------------------------------
// DS18B20 temperature setup - onboard sensor 
//--------------------------------------------------------------------------------------------
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);
double temp,maxtemp,mintemp;

//--------------------------------------------------------------------------------------------
// Software RTC setup
//-------------------------------------------------------------------------------------------- 
RTC_Millis RTC;
int hour;
  
//-------------------------------------------------------------------------------------------- 
// Flow control
//-------------------------------------------------------------------------------------------- 
int view = 1;                                // Used to control which screen view is shown
unsigned long last_emontx;                   // Used to count time from last emontx update
unsigned long slow_update;                   // Used to count time for slow 10s events
unsigned long fast_update;                   // Used to count time for fast 100ms events
  
//--------------------------------------------------------------------------------------------
// Setup
//--------------------------------------------------------------------------------------------
void setup () {
    rf12_initialize(MYNODE, freq,group);
    
    glcd.begin(0x18);    //begin glcd library and set contrast 0x20 is max, 0x18 seems to look best on emonGLCD
    glcd.backLight(200); //max 255
   
    #ifdef DEBUG 
      Serial.begin(9600);
      print_glcd_setup();
    #endif
    
    pinMode(greenLED, OUTPUT); 
    pinMode(redLED, OUTPUT);  
  
    sensors.begin();                         // start up the DS18B20 temp sensor onboard  
    sensors.requestTemperatures();
    temp = (sensors.getTempCByIndex(0));     // get inital temperture reading
    mintemp = temp; maxtemp = temp;          // reset min and max
    
    RTC.begin(DateTime(__DATE__, __TIME__));	//load time and time from computer into sofware RTC
digitalWrite(greenLED, HIGH);     
}
//--------------------------------------------------------------------------------------------


//--------------------------------------------------------------------------------------------
// Loop
//--------------------------------------------------------------------------------------------
void loop () {
  
    //--------------------------------------------------------------------------------------------
    // 1. On RF recieve
    //--------------------------------------------------------------------------------------------  
    if (rf12_recvDone()){
      if (rf12_crc == 0 && (rf12_hdr & RF12_HDR_CTL) == 0)  // and no rf errors
      {
        int node_id = (rf12_hdr & 0x1F);
        
        if (node_id == 15)                        // ==== EMONBASE ====
        {
          emonbase = *(PayloadBase*) rf12_data;   // get emonbase payload data
          power_calculations();                   // do the power calculations

          consuming = emonbase.power;
          //emonglcd.power = emontx.power;
          #ifdef DEBUG 
            print_emonbase_payload();             // print data to serial
          #endif  
          RTC.adjust(DateTime(2012, 1, 1, emonbase.hour, emonbase.mins, 0));  // adjust emonglcd software real time clock
          
          delay(100);                             // delay to make sure printing and clock setting finished
        }
      }
    }
    
    //--------------------------------------------------------------------
    // Things to do every 10s
    //--------------------------------------------------------------------
    if ((millis()-slow_update)>10000)
    {
       slow_update = millis();
       
       // Control led's
       led_control();
       backlight_control();
       
       // Get temperatue from onboard sensor
       sensors.requestTemperatures();
       temp = (sensors.getTempCByIndex(0));
       if (temp > maxtemp) maxtemp = temp;
       if (temp < mintemp) mintemp = temp;
   }

    //--------------------------------------------------------------------
    // Control toggling of screen pages
    //--------------------------------------------------------------------    
    if (digitalRead(switchpin) == TRUE) view = 2; else view = 1;

    //--------------------------------------------------------------------
    // Update the display every 200ms
    //--------------------------------------------------------------------
    if ((millis()-fast_update)>200)
    {
      fast_update = millis();
      if (view == 1) draw_main_screen();
      if (view == 2) draw_page_two();
    }
    
} //end loop
//--------------------------------------------------------------------------------------------

//--------------------------------------------------------------------
// Calculate power and energy variables
//--------------------------------------------------------------------
void power_calculations()
{
  DateTime now = RTC.now();
  int last_hour = hour;
  hour = now.hour();
  if (last_hour == 23 && hour == 00) { wh_gen = 0; wh_consuming = 0; }
  
  gen = 0; // emontx.gen;  if (gen<100) gen=0;	// remove noise offset 
  //consuming = emontx.power; 		        // for type 1 solar PV monitoring
  grid = consuming - gen;		        // for type 1 solar PV monitoring
  // grid=emontx.grid; 		         	// for type 2 solar PV monitoring                     
  // consuming=gen + emontx.grid; 	        // for type 2 solar PV monitoring - grid should be positive when importing and negastive when exporting. Flip round CT cable clap orientation if not
         
  if (gen > consuming) {
    importing=0; 			        //set importing flag 
    grid= grid*-1;			        //set grid to be positive - the text 'importing' will change to 'exporting' instead. 
  } else importing=1;
            
  //--------------------------------------------------
  // kWh calculation
  //--------------------------------------------------
  unsigned long lwhtime = whtime;
  whtime = millis();
  double whInc = gen * ((whtime-lwhtime)/3600000.0);
  wh_gen=wh_gen+whInc;
  whInc = consuming *((whtime-lwhtime)/3600000.0);
  wh_consuming=wh_consuming+whInc;
  //---------------------------------------------------------------------- 
}


