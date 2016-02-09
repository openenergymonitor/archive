/****************************************************************/
// Network Library
// Licence: GNU GPL
// For documentation see: openenergymonitor.org and sustburbia.blogspot.com/
/****************************************************************/

#ifndef Network_h
#define Network_h

#include "WProgram.h"

class Network
{
  public:

  //master and slave
  //---------------------------------------------------------------------------------------------------
  char * readCmd();                        //Read line equivalent of serial.read
  int available();                         //Read line equivalent of serial.available

  //cmd string decode functions
  void startArg(char*);                    //Tell cmd decoder the cmd to be decoded and to start at the start.
  char * readArg();                        //Read char* arg
  char readArgC();                         //Read first char only - useful for retrieving cmd letter
  int readArgI();                          //Read arg converted to integer
  double readArgD();                       //Read arg converted to double

  //Slave specific
  //---------------------------------------------------------------------------------------------------
  void setTriStatePins(int,int);           //Tristate pins setup
  void setResetPin(int);                   //Reset pin setup
  void start();                            //Sets tristate pin low to allow communication
  void stop();                             //Sets tristate pin high to allow other slaves to comunicate
  int started;                             //started status variable to control program flow

  //master specific
  //----------------------------------------------------------------------------------------------------
  int open(int slaveID);                  //Open a connection with a slave of ID given.
  char * readRegister(int registerID);     //Send a read register command to slave and wait for responce
  double readRegisterD(int registerID);     //Double version of the above
  int readRegisterI(int registerID);     //Integer version of the above

  void write(int registerID, int value);   //Write an integer to slave register ID
  void write(int registerID, char * wstr); //Write a string to slave register ID

  int waitForAck();                       //Wait for acknowledgment reply
  int waitForData();                      //Wait for data reply
  //----------------------------------------------------------------------------------------------------
  private:

  //master and slave
  //----------------------------------------------------------------------------------------------------
  //available
  int strpos;                              //Used in method available to contruct incoming line.
  char inString[34];                       //Incoming line char array. Increase size if you are sending larger strings.

  //readArg
  int argpos;                              //Argpos is used as a character position index when decoding the command string
  int slen;                                //Used to store length of command string
  char argStr[25];                         //Used to store argument string
  char *str;                               //Used to store command string being decoded.

  //Slave specific
  //----------------------------------------------------------------------------------------------------
  int tristateTX;                          //Pin number
  int autoReset;                           //Pin number
  int tristateRX;                          //Pin number


};

#endif
