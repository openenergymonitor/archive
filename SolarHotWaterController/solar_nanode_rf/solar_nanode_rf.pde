/*                          _                                                      _      
                           | |                                                    | |     
  ___ _ __ ___   ___  _ __ | |__   __ _ ___  ___       _ __   __ _ _ __   ___   __| | ___ 
 / _ \ '_ ` _ \ / _ \| '_ \| '_ \ / _` / __|/ _ \     | '_ \ / _` | '_ \ / _ \ / _` |/ _ \
|  __/ | | | | | (_) | | | | |_) | (_| \__ \  __/  _  | | | | (_| | | | | (_) | (_| |  __/
 \___|_| |_| |_|\___/|_| |_|_.__/ \__,_|___/\___| (_) |_| |_|\__,_|_| |_|\___/ \__,_|\___|
                                                                                          
*/
//--------------------------------------------------------------------------------------
// Solar hot water controller basestation sketch

// Uses JeeLabs RF12 library http://jeelabs.org/2009/02/10/rfm12b-library-for-arduino/
// Uses Andrew Lindsay's EtherShield library - using DHCP

// By Trystan Lea
// openenergymonitor.org
// GNU GPL V3

// Last update: 4th of October
//--------------------------------------------------------------------------------------

//#define DEBUG

//---------------------------------------------------------------------
// RF12 link - JeeLabs
//---------------------------------------------------------------------
#include <Ports.h>
#include <RF12.h>

#define MYNODE 35            // node ID 30 reserved for base station
#define freq RF12_433MHZ     // frequency
#define group 61            // network group 

// The RF12 data payload - a neat way of packaging data when sending via RF - JeeLabs
typedef struct {
  	  int COL;		
	  int CYLT;		
	  int CYLB;
          int error;		
} Payload;
Payload emontx;             

//---------------------------------------------------------------------
// The PacketBuffer class is used to generate the json string that is send via ethernet - JeeLabs
//---------------------------------------------------------------------
class PacketBuffer : public Print {
public:
    PacketBuffer () : fill (0) {}
    const char* buffer() { return buf; }
    byte length() { return fill; }
    void reset()
    { 
      memset(buf,NULL,sizeof(buf));
      fill = 0; 
    }
    virtual void write(uint8_t ch)
        { if (fill < sizeof buf) buf[fill++] = ch; }
    byte fill;
    char buf[150];
    private:
};
PacketBuffer str;

//---------------------------------------------------------------------
// Ethernet - Andrew Lindsay
//---------------------------------------------------------------------
#include <EtherShield.h>
byte mac[6] =     { 0x06,0x13,0x31,0x12,0x07,0x23};           // Unique mac address - must be unique on your local network
#define HOST ""                                                   // Blank "" if on your local network: www.yourdomain.org if not
#define API "/emoncms2/api/post?apikey=57ec0f95a0a4cdba96b6aa36ee9fd7bb&json="  // Your api url including APIKEY
byte server[4] = {192,168,2,100};                                   // Server IP
//---------------------------------------------------------------------

// Flow control varaiables
int dataReady=0;                                                  // is set to 1 when there is data ready to be sent
unsigned long lastRF;                                             // used to check for RF recieve failures
int post_count;                                                   // used to count number of ethernet posts that dont recieve a reply
   
   
int dhcp_count =0;
//---------------------------------------------------------------------
// Setup
//---------------------------------------------------------------------
void setup()
{
  Serial.begin(9600);
 
    #ifdef DEBUG 
    Serial.println("Emonbase:NanodeRF ctonly");
    Serial.print("Node: "); Serial.print(MYNODE); 
    Serial.print(" Freq: "); Serial.print("433Mhz"); 
    Serial.print(" Network group: "); Serial.println(group);
    Serial.print("Posting to "); printIP(server); Serial.print(" "); Serial.println(HOST);
    #endif
  
  ethernet_setup_dhcp(mac,server,80,8); // Last two: PORT and SPI PIN: 8 for Nanode, 10 for nuelectronics
  
  rf12_initialize(MYNODE, freq,group);
  lastRF = millis()-40000;                                        // setting lastRF back 40s is useful as it forces the ethernet code to run straight away
                                                                  // which means we dont have to wait to see if its working
  pinMode(6, OUTPUT); digitalWrite(6,LOW);                       // Nanode indicator LED setup, HIGH means off! if LED lights up indicates that Etherent and RFM12 has been initialize
}

//-----------------------------------------------------------------------
// Loop
//-----------------------------------------------------------------------
void loop()
{
digitalWrite(6,HIGH);    //turn inidicator LED off! yes off! input gets inverted by buffer
  //---------------------------------------------------------------------
  // On data receieved from rf12
  //---------------------------------------------------------------------
  if (rf12_recvDone() && rf12_crc == 0 && (rf12_hdr & RF12_HDR_CTL) == 0) 
  {
    digitalWrite(6,LOW);                                         // Flash LED on recieve ON
    emontx=*(Payload*) rf12_data;                                 // Get the payload
    // emontx_nodeID=rf12_hdr & 0x1F;   //extract node ID from received packet 
    
    // JSON creation: JSON sent are of the format: {key1:value1,key2:value2} and so on
    str.reset();                                                  // Reset json string      
    str.print("{ogo_RF:0,");                                    // RF recieved so no failure
    str.print("ogo_COL:");    str.print(emontx.COL/100.0);          // Add CT 1 reading 
    str.print(",ogo_CYLB:");    str.print(emontx.CYLB/100.0);      // Add Emontx battery voltage reading
    str.print(",ogo_CYLT:");    str.print(emontx.CYLT/100.0);      // Add Emontx battery voltage reading
    str.print(",ogo_error:");    str.print(emontx.error);      // Add Emontx battery voltage reading

    dataReady = 1;                                                // Ok, data is ready
    lastRF = millis();                                            // reset lastRF timer
    digitalWrite(6,HIGH);                                          // Flash LED on recieve OFF
    #ifdef DEBUG 
      Serial.println("RF recieved");
    #endif
    
  }
  
  // If no data is recieved from rf12 module the server is updated every 30s with RFfail = 1 indicator for debugging
  if ((millis()-lastRF)>30000)
  {
    lastRF = millis();                                            // reset lastRF timer
    str.reset();                                                  // reset json string
    str.print("{ogo_RF:1");                                       // No RF received in 30 seconds so send failure 
    dataReady = 1;                                                // Ok, data is ready
  }
  
  //----------------------------------------
  // 2) Send the data
  //----------------------------------------
  if (ethernet_ready_dhcp() && dataReady==1)                      // If ethernet and data is ready: send data
  {
    if (reply_recieved()==0) post_count++; else post_count = 0;   // Counts number of times a reply was not recieved
    str.print(",ogo_PST:"); str.print(post_count); // Posts number of times a reply was not recieved 
    str.print(",ogo_dhcp:"); str.print(dhcp_count); str.print("}\0");
    
    #ifdef DEBUG 
      Serial.print(str.buf);  
    #endif    // Print final json string to terminal
    
    ethernet_send_url(PSTR(HOST),PSTR(API),str.buf);              // Send the data via ethernet
    #ifdef DEBUG 
      Serial.println("sent");
    #endif
    dataReady = 0;                        // reset dataReady
  }
  
}


