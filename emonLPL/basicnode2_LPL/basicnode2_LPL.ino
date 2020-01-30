// 
// Basic example of low power node transmitter using the LowPowerLabs RFM69 Library
//

#include <RFM69.h>
#include <SPI.h>
#include "LowPower.h"

#define DEBUG      1

#define NODEID     19
#define NETWORKID  50
#define GATEWAYID  5

#define FREQUENCY   RF69_433MHZ
// has to be same 16 characters/bytes on all nodes
#define KEY         "sampleEncryptKey"
#define LED         9
#define SERIAL_BAUD 115200
#define ACK_TIME    50  // # of ms to wait for an ack  

RFM69 radio;

typedef struct {
  unsigned long msg;
  unsigned long retry;
  unsigned long fail;
} PayloadTX;
PayloadTX emontx;

unsigned long lastsent = 0;

void setup() {
  Serial.begin(SERIAL_BAUD);
  delay(10);
  
  radio.initialize(FREQUENCY,NODEID,NETWORKID);
  radio.encrypt(KEY);
  radio.promiscuous(false);
  
  emontx.msg = 0;
  emontx.retry = 0;
  emontx.fail = 0;
}

void loop() 
{
  if ((millis()-lastsent)>5000) 
  {
    lastsent = millis();
    
    emontx.msg++;
    
    Serial.print("msg: "); 
    Serial.print(emontx.msg);
    Serial.print(",");
    Serial.print(emontx.retry);
    Serial.print(",");
    Serial.print(emontx.fail);
    Serial.print(" ");
 
    if (radio.sendWithRetry(GATEWAYID, (const void*)(&emontx), sizeof(emontx))) {
      Serial.print("ok");
    } else { 
      emontx.fail++;
      Serial.print("fail");
    }
    Serial.print(" ");
    Serial.println(millis()-lastsent);
  }
}
