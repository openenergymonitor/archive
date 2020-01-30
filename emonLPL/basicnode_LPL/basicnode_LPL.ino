// 
// Basic example of low power node transmitter using the LowPowerLabs RFM69 Library
//

#include <RFM69.h>
#include <SPI.h>
#include "LowPower.h"

#define DEBUG      0 
#define SLEEP_TIME 5

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

typedef struct { int temperature, humidity; } PayloadTH;
PayloadTH emonth;

void setup() {
  Serial.begin(SERIAL_BAUD);
  delay(10);
  
  radio.initialize(FREQUENCY,NODEID,NETWORKID);
  radio.encrypt(KEY);
  radio.promiscuous(false);
}

void loop() {

  emonth.temperature = 1920;
  emonth.humidity = 5430;
  
  // Sending the radio packet takes about 150ms
  if (radio.sendWithRetry(GATEWAYID, (const void*)(&emonth), sizeof(emonth))) {
    if (DEBUG) Serial.print(" ok!");
    digitalWrite(LED, HIGH); delay(20); digitalWrite(LED, LOW);
  } else { 
    if (DEBUG) Serial.print(" nothing...");
  }
  if (DEBUG) Serial.println();
  
  delay(10);
  
  radio.sleep();
  for (int i=0; i<SLEEP_TIME-1; i++) {
      LowPower.powerDown(SLEEP_1S, ADC_OFF, BOD_OFF);
  }
  LowPower.powerDown(SLEEP_500MS, ADC_OFF, BOD_OFF);
  LowPower.powerDown(SLEEP_250MS, ADC_OFF, BOD_OFF);
  LowPower.powerDown(SLEEP_120MS, ADC_OFF, BOD_OFF);
}
