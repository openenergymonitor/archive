
#include <JeeLib.h>	     //https://github.com/jcw/jeelib

#define MYNODE 15            
#define freq RF12_868MHZ     // frequency
#define group 210            // network group 

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
        
        if (node_id == 10)                                               // EMONTX
        {
          emontx = *(PayloadTX*) rf12_data;                              // get emontx data
          last_rf = millis();                                            // reset lastRF timer
          
          delay(50);                                                     // make sure serial printing finished
                               
          // JSON creation: JSON sent are of the format: {key1:value1,key2:value2} and so on
          
          str.reset();                                                   // RF recieved so no failure
          str.print("{power1:");        str.print(emontx.power1);          // Add power reading
          str.print(",power2:");        str.print(emontx.power2);          // Add power reading
          str.print(",power3:");        str.print(emontx.power3);          // Add power reading 
          str.print(",voltage:");      str.print(emontx.voltage);        // Add emontx battery voltage reading
    
          data_ready = 1;                                                // data is ready
        }
        
        if (node_id == 20)                                               // EMONGLCD 
        {
          emonglcd = *(PayloadGLCD*) rf12_data;                          // get emonglcd data
          emonglcd_rx = 1;        
        }
      }
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
    
    // include temperature data from emonglcd if it has been recieved
    if (emonglcd_rx) {
      str.print(",temperature:");  
      str.print(emonglcd.temperature/100.0);
      emonglcd_rx = 0;
    }
    
    str.print("}\0");  //  End of json string
   
    Serial.println(str.buf);
    
    data_ready = 0;
  }
  
}
