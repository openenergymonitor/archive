#include <RFM69.h>
#include <SPI.h>

#define NODEID      5
#define NETWORKID   50
#define FREQUENCY   RF69_433MHZ
#define KEY         "sampleEncryptKey" // 16 characters
#define LED         9
#define SERIAL_BAUD 38400

RFM69 radio;

unsigned long lastsent = 0;

void setup() {
  Serial.begin(SERIAL_BAUD);
  delay(10);
  
  radio.initialize(FREQUENCY,NODEID,NETWORKID);
  radio.encrypt(KEY);
  radio.promiscuous(true);
}

unsigned long lasttime = 0;
unsigned long now = 0;
byte ackCount=0;
void loop() {

  if (radio.receiveDone())
  { 
    Serial.print("OK ");
    Serial.print(radio.SENDERID, DEC);
    Serial.print(" ");
    for (byte i = 0; i<radio.DATALEN; i++)
    {
        Serial.print((word)radio.DATA[i]);
        Serial.print(" ");
    }
    Serial.print("(");
    Serial.print(radio.readRSSI());
    Serial.print(")");
    Serial.println();
    
    if (radio.ACKRequested()) radio.sendACK();
  }
}
