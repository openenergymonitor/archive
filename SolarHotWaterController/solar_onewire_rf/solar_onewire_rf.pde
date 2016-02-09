
// openenergymonitor.org
// GNU GPL V3

//JeeLabs libraries 
#include <Ports.h>
#include <RF12.h>
#include <avr/eeprom.h>
#include <util/crc16.h>  //cyclic redundancy check

//---------------------------------------------------------------------------------------------------
// Serial print settings - disable all serial prints if SERIAL 0 - increases long term stability 
//---------------------------------------------------------------------------------------------------
//#define DEBUG
//---------------------------------------------------------------------------------------------------

//---------------------------------------------------------------------------------------------------
// RF12 settings 
//---------------------------------------------------------------------------------------------------
// fixed RF12 settings

#define myNodeID 10         //in the range 1-30
#define network     61      //default network group (can be in the range 1-250). All nodes required to communicate together must be on the same network group
#define freq RF12_433MHZ     //Frequency of RF12B module can be RF12_433MHZ, RF12_868MHZ or RF12_915MHZ. You should use the one matching the module you have.
//--------------------------------------------------------------------------------------------------


#include <OneWire.h>
#include <DallasTemperature.h>

// Data wire is plugged into port 2 on the Arduino
#define ONE_WIRE_BUS 4

// Setup a oneWire instance to communicate with any OneWire devices (not just Maxim/Dallas temperature ICs)
OneWire oneWire(ONE_WIRE_BUS);

// Pass our oneWire reference to Dallas Temperature. 
DallasTemperature sensors(&oneWire);
int numberOfDevices; // Number of temperature devices found
// arrays to hold device address
DeviceAddress tempDeviceAddress;

uint8_t address[2];

double COL, CYLT, CYLB;
int ec=0;
//########################################################################################################################
//Data Structure to be sent
//######################################################################################################################## 
typedef struct {
  	  int COL;		// current transformer 1
	  int CYLT;		// emontx voltage
	  int CYLB;		// emontx voltage
          int error;
} Payload;
Payload emontx;
//########################################################################################################################

int reset_temp = 1;

void setup() {
  Serial.begin(9600);
  
  rf12_initialize(myNodeID,freq,network);   //Initialize RFM12 with settings defined above 
  
  pinMode(3,OUTPUT);
  pinMode(9,OUTPUT);
}

void loop()
{ 
  
  if (reset_temp == 1)
  {
    sensors.begin();
    numberOfDevices = sensors.getDeviceCount();
    for(int i=0;i<numberOfDevices; i++)
    {
      if (sensors.getAddress(tempDeviceAddress, i)) sensors.setResolution(tempDeviceAddress, 12);
    }
    reset_temp =0;
  }
  
  digitalWrite(9,HIGH);
  sensors.requestTemperatures(); // Send the command to get temperatures
  
  for(int i=0;i<numberOfDevices; i++)
  {
    if(sensors.getAddress(tempDeviceAddress, i))
    {
      for (uint8_t p = 1; p < 3; p++)  // Shorten the 64-bit unique ID to 16-bits
      {
         address[p-1] = tempDeviceAddress[p];
         //Serial.print(address[0][p-1],HEX);    
      } 
      
      // 1) If Cylinder top sensor then get its temperature
      int a = 0;
      if (address[0] == 0x95) { a = 1; } else { a = 0; }
      if (address[1] == 0x51) { a = 1; } else { a = 0; }
      
      if (a == 1) 
      {
        CYLT = sensors.getTempC(tempDeviceAddress);
        if (CYLT == DEVICE_DISCONNECTED) reset_temp = 1;
      }
      
      // 2) If Cylinder bottom sensor then get its temperature
      a = 0;
      if (address[0] == 0x85) { a = 1; } else { a = 0; }
      if (address[1] == 0x7A) { a = 1; } else { a = 0; }
      if (a == 1) 
      {
        CYLB = sensors.getTempC(tempDeviceAddress);
        if (CYLB == DEVICE_DISCONNECTED) reset_temp = 1;
      }
      
      // 3) If Collector sensor then get its temperature
      a = 0;
      if (address[0] == 0x22) { a = 1; } else { a = 0; }
      if (address[1] == 0x70) { a = 1; } else { a = 0; }
      if (a == 1) 
      {
        COL = sensors.getTempC(tempDeviceAddress);
        if (COL == DEVICE_DISCONNECTED) reset_temp = 1;
      }
      
    }
  }
 
  #ifdef DEBUG
  Serial.print(COL);
  Serial.print(" ");
  Serial.print(CYLB);
  Serial.print(" ");  
  Serial.println(CYLT);  
  delay(10);
  #endif 
 
  emontx.COL = COL*100;
  emontx.CYLB = CYLB*100;
  emontx.CYLT = CYLT*100;
  emontx.error = 0;
 
  rfwrite() ; 
  
  double diff = COL - CYLB;
  if (diff>15.0) digitalWrite(3,HIGH);
  if (diff<14.0) digitalWrite(3,LOW);
  digitalWrite(9,LOW);
  delay(5000);
}

//--------------------------------------------------------------------------------------------------
// Send payload data via RF - see http://jeelabs.net/projects/cafe/wiki/RF12 for RF12 library documentation 
//--------------------------------------------------------------------------------------------------
static void rfwrite(){
    ec--;
    while (!rf12_canSend())
    {
      rf12_recvDone();
      ec++;
      if (ec>1000) break;
    }
    emontx.error = ec;
    
    rf12_sendStart(0, &emontx, sizeof emontx); 
    //rf12_sendStart(rf12_hdr, &emontx, sizeof emontx, RADIO_SYNC_MODE); -- includes header data 
    rf12_sendWait(2);    //wait for RF to finish sending while in standby mode
}
