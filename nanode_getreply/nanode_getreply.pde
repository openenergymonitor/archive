// openenergymonitor.org
// GNU GPL V3

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
byte mac[6] =     { 0x04,0x13,0x31,0x13,0x05,0x22};           // Unique mac address - must be unique on your local network
#define HOST "YOUR URL"                                                   // Blank "" if on your local network: www.yourdomain.org if not
#define API "/emoncms2/api/post?apikey=XXXXXXXXXXXXXXXXXXXX&json="  // Your api url including APIKEY                                  // Server IP
//---------------------------------------------------------------------

// Flow control varaiables
int dataReady=0;                                                  // is set to 1 when there is data ready to be sent
unsigned long lastRF;                                             // used to check for RF recieve failures
int post_count;                                                   // used to count number of ethernet posts that dont recieve a reply
    
//---------------------------------------------------------------------
// Setup
//---------------------------------------------------------------------
void setup()
{
  Serial.begin(9600);

  ethernet_setup_dhcp_dns(mac,HOST,80,8); // Last two: PORT and SPI PIN: 8 for Nanode, 10 for nuelectronics

  lastRF = millis()-40000;                                        // setting lastRF back 40s is useful as it forces the ethernet code to run straight away
}

//-----------------------------------------------------------------------
// Loop
//-----------------------------------------------------------------------
void loop()
{  
  // If no data is recieved from rf12 module the server is updated every 30s with RFfail = 1 indicator for debugging
  if ((millis()-lastRF)>10000)
  {
    lastRF = millis();                                            // reset lastRF timer
    str.reset();                                                  // reset json string
    str.print("{RFfail08:1");                                       // No RF received in 30 seconds so send failure 
    dataReady = 1;                                                // Ok, data is ready
  }
  
  //----------------------------------------
  // 2) Send the data
  //----------------------------------------
  if (ethernet_ready_dhcp_dns() && dataReady==1)                      // If ethernet and data is ready: send data
  {
    if (reply_recieved()==0) post_count++; else post_count = 0;   // Counts number of times a reply was not recieved
    str.print(",POSTfail08:"); str.print(post_count); str.print("}\0");// Posts number of times a reply was not recieved
    Serial.print(str.buf);                                        // Print final json string to terminal
    
    ethernet_send_url(PSTR(HOST),PSTR(API),str.buf);              // Send the data via ethernet
    Serial.println("sent"); dataReady = 0;                        // reset dataReady
  }
  
}


