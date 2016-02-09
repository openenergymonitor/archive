//----------------------------------------------------------------------------
// Network Slave Template
// Last revision 30 November 2009
// Licence: GNU GPL
// By Trystan Lea

// This example demonstrates basic functionality

// 1) The example variable can be read from the master using the command: r,26
// 2) The example variable can be set from the master using command w,15,value

// digital inputs can be read with command r,2 - 13
// analog inputs can be read with command r,20 - 25

// digital inputs can be set with command w,2 - 13, 0 or 1

//----------------------------------------------------------------------------
//----------------------------------------------------------------------------
// Load Network library and create new instance
//----------------------------------------------------------------------------
#include "Network.h";
Network net;

//char array to store commands
char *cmd;

//Slave ID needs to be unique
//for each slave on your network
int slaveID = 01;

double exampleVariable = 100;

//----------------------------------------------------------------------------
// Setup
//----------------------------------------------------------------------------
void setup()
{ 
  net.setTriStatePins(7,5);                      //Set tristate buffer pins

  Serial.begin(9600);
}

//----------------------------------------------------------------------------
// Main loop
//----------------------------------------------------------------------------
void loop()
{
  
  //Add unit specific functionality here
  
  //If serial line recieved
  if (net.available()) 
  {
    cmd = net.readCmd();
    //Check for standard slave commands
    SlaveCommands();           //Registers are set in here have a look in cmdList       
  } 
}




