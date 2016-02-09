// Demo of a sketch which sends and receives packets
// 2010-05-17 <jcw@equi4.com> http://opensource.org/licenses/mit-license.php
// $Id: pingPong.pde 5655 2010-05-17 16:13:35Z jcw $

// with thanks to Peter G for creating a test sketch and pointing out the issue
// see http://news.jeelabs.org/2010/05/20/a-subtle-rf12-detail/

#include <RF12.h>
#include <Ports.h>

byte needToSend = 0;
char instr[34]; int pos;
char outstr[34];


int slaveID = 11;

void setup () {
    Serial.begin(57600);
    Serial.print("node b ");
    rf12_initialize(2, RF12_868MHZ, 33);
}

void loop () {
  
  if (rf_rx()) 
  {
    
    SlaveCommands(); 

  }
  
  
  
    if (needToSend && rf12_canSend()) {
        Serial.println(instr);
        needToSend = 0;
        rf12_sendStart(0, outstr, strlen(outstr));
        strcpy(outstr,"");
        delay(100);
    }
}




 
