// Demo of a sketch which sends and receives packets
// 2010-05-17 <jcw@equi4.com> http://opensource.org/licenses/mit-license.php
// $Id: pingPong.pde 5655 2010-05-17 16:13:35Z jcw $

// with thanks to Peter G for creating a test sketch and pointing out the issue
// see http://news.jeelabs.org/2010/05/20/a-subtle-rf12-detail/

#include <RF12.h>
#include <Ports.h>

byte needToSend = 1;
char instr[34]; int pos;

int stage = 1;

void setup () {
    Serial.begin(57600);
    rf12_initialize(1, RF12_868MHZ, 33);
}

void loop () {
  
  if (rf_rx()) 
  {
    
    if (stage == 2) stage=3;
    if (stage == 4) stage=5;
    
    Serial.println(instr);
  }
    
    if (stage==1 && rf12_canSend()) {
        stage = 2;
        rf12_sendStart(0, "@,11", 4);
        delay(100);
    }
    
    if (stage==3 && rf12_canSend()) {
        stage = 4;
        rf12_sendStart(0, "r,20", 4);
        delay(100);
    }
}
