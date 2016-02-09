//--------------------------------------------------------------------------------------
// MASTER ETHERNET RELAY UNIT Template

// 1) Requests and reads data from slave units 

// 2) Relay's this data on via ethernet to a webserver

// Author: Trystan Lea
// Licence: GNU GPL openenergymonitor.org
//--------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------
// ETHERNET
//--------------------------------------------------------------------------------------
#include "etherShield.h"
byte mac[] = {0x54,0x52,0x58,0x10,0x00,0x18};         //Ethernet shield mac address
byte ip[] = {192,168,1,8};                            //Ethernet shield ip address
byte gateway[] = {192,168,1,1};                       //Gateway ip
byte server[] = {85, 92, 86, 84};                     //Server ip
#define port 80
char str[250];
//--------------------------------------------------------------------------------------
// NETWORK 
//--------------------------------------------------------------------------------------
#include "Network.h";                                 //Load Network Library
Network net;                                          //Instance of Network
int pv = 43;                                          //PV unit ID
//--------------------------------------------------------------------------------------
// Laptop serial connection
//--------------------------------------------------------------------------------------
#include <NewSoftSerial.h>
NewSoftSerial laptop(3, 4);
//--------------------------------------------------------------------------------------
// Variables to be transfered
//--------------------------------------------------------------------------------------
double currentA=0;                                    //energy calc from realPower

unsigned long lastupdate;
//--------------------------------------------------------------------------------------
// SETUP
//--------------------------------------------------------------------------------------
void setup()
{
  Serial.begin(9600);
  laptop.begin(9600);
  
  client_setup(mac,ip,gateway,server);    //Setup ethernet client
  client_timeout(5,1000);                 //Set timeout variables
}

//--------------------------------------------------------------------------------------
// MAIN LOOP
//--------------------------------------------------------------------------------------
void loop()
{
  readFromPVunit();

  if ((millis()-lastupdate)>10000)  //Every 10 seconds
  { lastupdate = millis();
    
    createJSON();
    sendEthernet();
  }
}

//--------------------------------------------------------------------------------------
// Request and read data from PV unit (slave)

// If you want to request data from another unit copy the code below and address the second unit
//--------------------------------------------------------------------------------------
void readFromPVunit()
{
  if (net.open(pv))                                             //Select PV unit
  {
    laptop.print("net open : r,26 : ");                         //Verbose output to serial monitor
    //---------------------------------------------------------------------------------
    // Send READ command to PV unit : in this case read register 26 = currentA
    //---------------------------------------------------------------------------------
    Serial.println("r,26");                                     //To read more registers add ,27,28... and so on

    if (net.waitForData())                                      //wait until reply is recieved:
    {
      currentA = net.readArgD();                                //read returned argument register 26
      
      //Add in more net.readArgD() here if your reading ,27,28...
      
      Serial.println("q");                                      //Close connection with pv unit                              
    }
    
  }
  else { laptop.println("could not open net"); } 
}

//--------------------------------------------------------------------------------------
// Construct JSON string
//--------------------------------------------------------------------------------------
void createJSON()
{
  strcpy(str,"/post.php?json=");                //URL
                                                //If your using a shared server add in full URL here
  
  srtJSON(str);                                 //Start JSON string
  addJSON(str,"currentA",  currentA);           //Add a variable
  endJSON(str);                                 //End JSON string
}

//--------------------------------------------------------------------------------------
// Send the string to the server
//--------------------------------------------------------------------------------------
void sendEthernet()
{
  if (ethernet_send("",str))                     //Try to send the string NOTE: success on recieve of "ok" from server
  { laptop.println("Data sent");} 
  else
  { laptop.println("Failed to send"); }
}
