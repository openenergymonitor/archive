// 
// Basic example of low power node transmitter using the LowPowerLabs RFM69 Library
//

#include <RFM69.h>
#include <SPI.h>
#include "LowPower.h"

#define DEBUG      0 
#define SLEEP_TIME 10

#define NODEID     10
#define NETWORKID  50
#define GATEWAYID  5

#define FREQUENCY   RF69_433MHZ
// has to be same 16 characters/bytes on all nodes
#define KEY         "sampleEncryptKey"
#define LED         9
#define SERIAL_BAUD 115200
#define ACK_TIME    50  // # of ms to wait for an ack  

RFM69 radio;

#include "EmonLib.h"             // Include Emon Library
EnergyMonitor ct1,ct2,ct3,ct4;   // Create an instance

typedef struct { int power1, power2, power3, power4, vrms; } PayloadTX;
PayloadTX emontx;

void setup() {
  Serial.begin(SERIAL_BAUD);
  Serial.println("emonTxV3_LPL");
  delay(10);
  
  radio.initialize(FREQUENCY,NODEID,NETWORKID);
  radio.encrypt(KEY);
  radio.promiscuous(false);
  
  ct1.voltage(0, 268.97, 1.7);
  ct2.voltage(0, 268.97, 1.7);
  ct3.voltage(0, 268.97, 1.7);
  ct4.voltage(0, 268.97, 1.7);
  
  ct1.current(1, 90.9);
  ct2.current(2, 90.9);
  ct3.current(3, 90.9);
  ct4.current(4, 16.67);
}

void loop() {
  
  ct1.calcVI(30,2000);
  ct2.calcVI(30,2000);
  ct3.calcVI(30,2000);
  ct4.calcVI(30,2000);

  emontx.power1 = ct1.realPower;
  emontx.power2 = ct2.realPower;
  emontx.power3 = ct3.realPower;
  emontx.power4 = ct4.realPower;
  
  emontx.vrms = ct1.Vrms;
  
  Serial.print(emontx.power1);
  Serial.print(" ");
  Serial.print(emontx.power2);
  Serial.print(" ");
  Serial.print(emontx.power3);
  Serial.print(" ");
  Serial.print(emontx.power4);
  Serial.print(" ");
  Serial.println(emontx.vrms);
  
  // Sending the radio packet takes about 150ms
  if (radio.sendWithRetry(GATEWAYID, (const void*)(&emontx), sizeof(emontx))) {
    if (DEBUG) Serial.print(" ok!");
    digitalWrite(LED, HIGH); delay(20); digitalWrite(LED, LOW);
  } else { 
    if (DEBUG) Serial.print(" nothing...");
  }
  if (DEBUG) Serial.println();
  
  delay(10);
  
  radio.sleep();
  for (int i=0; i<SLEEP_TIME-3; i++) {
      LowPower.powerDown(SLEEP_1S, ADC_OFF, BOD_OFF);
  }
  LowPower.powerDown(SLEEP_500MS, ADC_OFF, BOD_OFF);
  LowPower.powerDown(SLEEP_120MS, ADC_OFF, BOD_OFF);
}
