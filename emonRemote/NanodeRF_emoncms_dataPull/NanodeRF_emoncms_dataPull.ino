/*                          _                                                      _      
                           | |                                                    | |     
  ___ _ __ ___   ___  _ __ | |__   __ _ ___  ___       _ __   __ _ _ __   ___   __| | ___ 
 / _ \ '_ ` _ \ / _ \| '_ \| '_ \ / _` / __|/ _ \     | '_ \ / _` | '_ \ / _ \ / _` |/ _ \
|  __/ | | | | | (_) | | | | |_) | (_| \__ \  __/  _  | | | | (_| | | | | (_) | (_| |  __/
 \___|_| |_| |_|\___/|_| |_|_.__/ \__,_|___/\___| (_) |_| |_|\__,_|_| |_|\___/ \__,_|\___|
                                                                                          
*/
//--------------------------------------------------------------------------------------
// Relay's data recieved by emontx up to emoncms
// Relay's data recieved by emonglcd up to emoncms
// Decodes reply from server to set software real time clock
// Relay's time data to emonglcd - and any other listening nodes.
// Looks for 'ok' reply from http request to verify data reached emoncms

// emonBase Documentation: http://openenergymonitor.org/emon/emonbase

// Authors: Trystan Lea and Glyn Hudson
// Part of the: openenergymonitor.org project
// Licenced under GNU GPL V3
//http://openenergymonitor.org/emon/license

// EtherCard Library by Jean-Claude Wippler and Andrew Lindsay
// JeeLib Library by Jean-Claude Wippler
//--------------------------------------------------------------------------------------

#define DEBUG     //comment out to disable serial printing to increase long term stability 
#define UNO       //anti crash wachdog reset only works with Uno (optiboot) bootloader, comment out the line if using delianuova

#include <Wire.h>
#include <RTClib.h>
RTC_Millis RTC;

#include <JeeLib.h>	     //https://github.com/jcw/jeelib
#include <avr/wdt.h>

#define MYNODE 15            
#define freq RF12_868MHZ     // frequency
#define group 210            // network group 

//---------------------------------------------------
// Data structures for transfering data between units
//---------------------------------------------------
typedef struct { int hour, mins, power; } PayloadBase;
PayloadBase emonbase;
//---------------------------------------------------



//--------------------------------------------------------------------------
// Ethernet
//--------------------------------------------------------------------------
#include <EtherCard.h>		//https://github.com/jcw/ethercard 

// ethernet interface mac address, must be unique on the LAN
static byte mymac[] = { 0x44,0x29,0x49,0x21,0x30,0x31 };

//IP address of remote sever, only needed when posting to a server that has not got a dns domain name (staticIP e.g local server) 
byte Ethernet::buffer[700];
static uint32_t timer;

//Domain name of remote webserver - leave blank if posting to IP address 
char website[] PROGMEM = "vis.openenergymonitor.org";
//static byte hisip[] = {31,222,163,58};    // un-comment for posting to static IP server (no domain name)            

const int redLED = 6;                     // NanodeRF RED indicator LED
const int greenLED = 5;                   // NanodeRF GREEN indicator LED

int ethernet_error = 0;                   // Etherent (controller/DHCP) error flag
int ethernet_requests = 0;                // count ethernet requests without reply                 

int emonglcd_rx = 0;                      // Used to indicate that emonglcd data is available
int data_ready=0;                         // Used to signal that emontx data is ready to be sent
unsigned long update;                    // Used to check for regular emontx data - otherwise error

char line_buf[50];                        // Used to store line of http reply header

//**********************************************************************************************************************
// SETUP
//**********************************************************************************************************************
void setup () {
  
  //Nanode RF LED indictor  setup - green flashing means good - red on for a long time means bad! 
  //High means off since NanodeRF tri-state buffer inverts signal 
  pinMode(redLED, OUTPUT); digitalWrite(redLED,LOW);            
  pinMode(greenLED, OUTPUT); digitalWrite(greenLED,LOW);       
  delay(100); digitalWrite(redLED,HIGH);                          // turn off redLED
  
  Serial.begin(9600);
  Serial.println("\n[webClient]");

  if (ether.begin(sizeof Ethernet::buffer, mymac) == 0) {
    Serial.println( "Failed to access Ethernet controller");
    ethernet_error = 1;  
  }
 
  rf12_initialize(MYNODE, freq,group);
   
  digitalWrite(greenLED,HIGH);                                    // Green LED off - indicate that setup has finished 
 
  #ifdef UNO
  wdt_enable(WDTO_8S); 
  #endif;
}

//**********************************************************************************************************************
// LOOP
//**********************************************************************************************************************
void loop () {
  
  #ifdef UNO
  wdt_reset();
  #endif

  dhcp_dns();   // handle dhcp and dns setup - see dhcp_dns tab
  
  // Display error states on status LED
  if (ethernet_error==1 || ethernet_requests > 0) digitalWrite(redLED,LOW);
    else digitalWrite(redLED,HIGH);

  

  //-----------------------------------------------------------------------------------------------------------------
  // 2) If no data is recieved from rf12 module the server is updated every 30s with RFfail = 1 indicator for debugging
  //-----------------------------------------------------------------------------------------------------------------
  if ((millis()-update)>5000)
  {
    update = millis();                                                 // reset lastRF timer
    data_ready = 1;                                                     // Ok, data is ready

  }


  //-----------------------------------------------------------------------------------------------------------------
  // 3) Send data via ethernet
  //-----------------------------------------------------------------------------------------------------------------
  ether.packetLoop(ether.packetReceive());
  
  if (data_ready) {
 

    // Example of posting to emoncms v3 demo account goto http://vis.openenergymonitor.org/emoncms3 
    // and login with sandbox:sandbox
    // To point to your account just enter your WRITE APIKEY 
    ethernet_requests ++;
    ether.browseUrl(PSTR("/emoncms3/feed/value.json?apikey=***************************&id=**"),NULL, website, my_callback);
                                                      //replace *************************** with emoncms read only API key and **with emoncms feed ID
    
    data_ready =0;
  }
  
  if (ethernet_requests > 10) delay(10000); // Reset the nanode if more than 10 request attempts have been tried without a reply

}
//**********************************************************************************************************************

//-----------------------------------------------------------------------------------
// Ethernet callback
// recieve reply and decode
//-----------------------------------------------------------------------------------
static void my_callback (byte status, word off, word len) {
  
  get_header_line(2,off);      // Get the date and time from the header
  Serial.print("3 ok | ");    // Print out the date and time
  Serial.println(line_buf);    // Print out the date and time
  
  // Decode date time string to get integers for hour, min, sec, day
  // We just search for the characters and hope they are in the right place
  char val[1];
  val[0] = line_buf[23]; val[1] = line_buf[24];
  int hour = atoi(val);
  val[0] = line_buf[26]; val[1] = line_buf[27];
  int mins = atoi(val);
  val[0] = line_buf[29]; val[1] = line_buf[30];
  int sec = atoi(val);
  val[0] = line_buf[11]; val[1] = line_buf[12];
  int day = atoi(val);

  RTC.adjust(DateTime(2012, 1, day, hour, mins, sec));
  
  DateTime now = RTC.now();
  emonbase.hour = now.hour();
  emonbase.mins = now.minute();
  
  //-----------------------------------------------------------------------------
  
  delay(100);
  
  // Send time data
  int i = 0; while (!rf12_canSend() && i<10) {rf12_recvDone(); i++;}    // if can send - exit if it gets stuck, as it seems too
  rf12_sendStart(0, &emonbase, sizeof emonbase);                        // send payload
  rf12_sendWait(0);
  
  Serial.println("4 emonbase sent");
  
  get_reply_data(off);
  Serial.println(line_buf);    // Print out the date and time
  emonbase.power=atoi(line_buf);
  //if (strcmp(line_buf,"ok")) {ethernet_requests = 0; ethernet_error = 0;}  // check for ok reply from emoncms to verify data post request
}
