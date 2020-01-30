// 
// Basic example of emonTH with DHT22 using the LowPowerLabs RFM69 Library
//

#include <RFM69.h>
#include <SPI.h>
#include "LowPower.h"
#include "DHT.h"

#define DEBUG      1
#define SLEEP_TIME 60
#define BATT_ADC   1 

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

typedef struct {                                                      // RFM12B RF payload datastructure
  	  int temp;
          int temp_external;
          int humidity;    
          int battery;          	                                      
} Payload;
Payload emonth;

#define DHT22_PWR  6
#define DHTPIN     18 
#define DHTTYPE    DHT22

DHT dht(DHTPIN, DHTTYPE);
boolean DHT22_status; 

void setup() {
  Serial.begin(SERIAL_BAUD);
  delay(10);
  
  radio.initialize(FREQUENCY,NODEID,NETWORKID);
  radio.encrypt(KEY);
  radio.promiscuous(false);
  
  pinMode(DHT22_PWR,OUTPUT);
  pinMode(BATT_ADC, INPUT);
  digitalWrite(DHT22_PWR,LOW);
}

void loop() {

  digitalWrite(DHT22_PWR,HIGH);
  LowPower.powerDown(SLEEP_2S, ADC_OFF, BOD_OFF);
  dht.begin();
  float h = dht.readHumidity();                                         // Read Humidity
  float t = dht.readTemperature();                                      // Read Temperature
  digitalWrite(DHT22_PWR,LOW);                                          // Power down
  
  emonth.temp = t*10;
  emonth.humidity = h*10;
  emonth.battery=int(analogRead(BATT_ADC)*0.03225806);
  Serial.print (emonth.battery);
  
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
  for (int i=0; i<SLEEP_TIME-3; i++) {
      LowPower.powerDown(SLEEP_1S, ADC_OFF, BOD_OFF);
  }
  LowPower.powerDown(SLEEP_500MS, ADC_OFF, BOD_OFF);
  LowPower.powerDown(SLEEP_250MS, ADC_OFF, BOD_OFF);
  LowPower.powerDown(SLEEP_120MS, ADC_OFF, BOD_OFF);
}
