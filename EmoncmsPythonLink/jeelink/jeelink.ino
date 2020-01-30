
#include <JeeLib.h>	     //https://github.com/jcw/jeelib

#define MYNODE 15            
#define freq RF12_868MHZ     // frequency
#define group 64            // network group 

//---------------------------------------------------
// Data structures for transfering data between units
//---------------------------------------------------

typedef struct { int power1, power2, power3, voltage; } PayloadTX;
PayloadTX emontx;

typedef struct { int temperature; } PayloadGLCD;
PayloadGLCD emonglcd;

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
    virtual size_t write (uint8_t ch)
        { if (fill < sizeof buf) buf[fill++] = ch; }
    byte fill;
    char buf[150];
    private:
};
PacketBuffer str;

char line_buf[50];                        // Used to store line of http reply header

unsigned long last_rf = 0;
int data_ready = 0;
int emonglcd_rx = 0;

//**********************************************************************************************************************
// SETUP
//**********************************************************************************************************************
void setup () {
    
  Serial.begin(9600);
  pinMode(9,OUTPUT);
  rf12_initialize(MYNODE, freq,group);
  last_rf = millis()-40000;                                       // setting lastRF back 40s is useful as it forces the ethernet code to run straight away
   
}

//**********************************************************************************************************************
// LOOP
//**********************************************************************************************************************
void loop () {
  
  //-----------------------------------------------------------------------------------------------------------------
  // 1) On RF recieve
  //-----------------------------------------------------------------------------------------------------------------
  if (rf12_recvDone()){      
      if (rf12_crc == 0 && (rf12_hdr & RF12_HDR_CTL) == 0)
      {
        int node_id = (rf12_hdr & 0x1F);
        byte n = rf12_len;
         
        str.reset();
        str.print("&node=");  str.print(node_id);
        str.print("&csv=");
        for (byte i=0; i<n; i+=2)
        {
          int num = ((unsigned char)rf12_data[i+1] << 8 | (unsigned char)rf12_data[i]);
          if (i) str.print(",");
          str.print(num);
        }

        str.print("\0");  //  End of json string
        data_ready = 1; 
        last_rf = millis(); 
        
  
  digitalWrite(9,HIGH);  
  }

  //-----------------------------------------------------------------------------------------------------------------
  // 2) If no data is recieved from rf12 module the server is updated every 30s with RFfail = 1 indicator for debugging
  //-----------------------------------------------------------------------------------------------------------------
  if ((millis()-last_rf)>30000)
  {
    last_rf = millis();                                                 // reset lastRF timer
    str.reset();                                                        // reset json string
    str.print("{rf_fail:1");                                            // No RF received in 30 seconds so send failure 
    data_ready = 1;                                                     // Ok, data is ready
  }
  
  if (data_ready) {
   
    Serial.println(str.buf);
    
    data_ready = 0;
  }
  
}

  }
