/****************************************************************/
// Network Library
// Licence: GNU GPL
// For documentation see: openenergymonitor.org and sustburbia.blogspot.com/
/****************************************************************/

#include "WProgram.h"
#include "Network.h"

/***********************************************************/
// Shared Master/Slave functions 
/***********************************************************/

//Command string decoding functions
//------------------------------------------------------------

  //Commands come in comma seperated variable format
  //startArg takes us to the start of the cmd string
  //readArg- from the last comma upto the next comma
  //This makes it possible to decode very long comma
  //seperated variable strings.

  //arguments are up to 25 characters long - set in header.

//Goto start of cmd string + a few other things
//---------------------------------------------------------------------------------------------------
void Network::startArg(char *strIN)             
{
  argpos = 0;                                   //cmd string start
  str = strIN;                                  //fetch cmd string
  slen = strlen(str);                           //get cmd string length
}

//Read argument between last comma and next comma:  ...,10.0,...
//---------------------------------------------------------------------------------------------------
char * Network::readArg()                       
{
  int p=0;
  while((str[argpos]!=',') && (argpos<slen))    //Read characters until next comma or end of string
  {
    
    argStr[p]=str[argpos];                      //copy characters from cmd string to argument string
    p++;                                        //increment argument string position
    argpos++;                                   //increment cmd string position
  }
  argStr[p]='\0';                               //end of string identifier
  argpos++;     
 
  return argStr;                                //return the argument string 
}

//used for fetching cmd identifier character
//---------------------------------------------------------------------------------------------------
char Network::readArgC()
{
  char * tmp = readArg();                       //Read the first argument
  return tmp[0];                                //Send back only the first character
}
//Convert argument string into integer
//---------------------------------------------------------------------------------------------------
int Network::readArgI()
{
  return atoi(readArg());                       //Send back integer
}
//Convert argument string into double
//---------------------------------------------------------------------------------------------------
double Network::readArgD()
{
  return strtod(readArg(),NULL);                //Send back double, uses quite a bit of memory!
}

//Create command string from serial character steam
//------------------------------------------------------------
int Network::available()
{
  
  if (strpos==0) {for (int a=0; a<35; a++){ inString[a]=0;}}  //Empty inString on new line start
                                                              //note that inString is set to 34 characters in the header
  
  if  (Serial.available() > 0)                                //When characters are being recieved.
  {        
    char inByte = Serial.read();    
    
    if (inByte == 13 || inByte == '>')                        //Check for end of line character or for use in arduino terminal > 
    {
      inString[strpos]='\0';                                  //end of string character
      strpos=0; return 1;
    }
    else
    {
      //For some reason a newline character 
      //was often found at the start of the string
      //this makes sure it doesnt get in
      if (inByte!='\n'){
        inString[strpos] = inByte;
        strpos++;
      }
      if (strpos>34) strpos = 34;                             //Stop string from overflowing
    }  
  }
  return 0;
}

//Used for fetching string in main program
//---------------------------------------------------------------------------------------------------
char * Network::readCmd()
{
  return inString;
}

/***********************************************************/
// Slave only functions 
/***********************************************************/

//Setup tristate pins
//---------------------------------------------------------------------------------------------------
void Network::setTriStatePins(int rx,int tx)
{
  tristateTX = tx;
  tristateRX = rx;

  pinMode(tristateRX, OUTPUT);   
  pinMode(tristateTX, OUTPUT);   

  digitalWrite(tristateTX,HIGH);                 //Set tristate pin to HIGH to start with
}

//Setup reset pin
//---------------------------------------------------------------------------------------------------
void Network::setResetPin(int ars)
{
  autoReset = ars;
  pinMode(autoReset, OUTPUT);   
}

//Start master slave session 
//set tristate to LOW allows
//comunication on SLAVE RX -> MASTER TX bus
//---------------------------------------------------------------------------------------------------
void Network::start()
{
  started = 1;
  digitalWrite(tristateTX,LOW);
  delay(100);
  Serial.println('?');                           //Acknowledgment
}

//Stop the connection
//---------------------------------------------------------------------------------------------------
void Network::stop()
{
  Serial.println('!');
  delay(10);                                     //Allow enough time for ! to send
  started = 0;
                            
  digitalWrite(tristateTX,HIGH);                 //Disconnect SLAVE RX -> MASTER TX bus
  delay(100);
}

/***********************************************************/
// Master only functions 
/***********************************************************/

//request a connection with a remote slave
//---------------------------------------------------------------------------------------------------
int Network::open(int slave)
{
  Serial.print("@,"); 
  Serial.println(slave); 
  return waitForAck();
}

//Make it as easy as possible to read a remote analog pin.
//---------------------------------------------------------------------------------------------------
char * Network::readRegister(int index)
{
  Serial.print("r,");                        //read command identifier
  Serial.println(index);                     //read register index

  waitForData();                             //wait for and then fetch data reply
  
  startArg(inString);                        //start cmd decode
  char arg0 = readArgC();                    //read first character should be a d for data
  char * read_arg = readArg();               //read arg1 - the data
  
  return  read_arg;
}

double Network::readRegisterD(int index)
{
  return  strtod(readRegister(index),NULL);   
}

int Network::readRegisterI(int index)
{
  return  atoi(readRegister(index));   
}

//Print a string
//---------------------------------------------------------------------------------------------------
void Network::write(int index, char * pstr)
{
  Serial.print("w,");                        //write command identifier
  Serial.print(index);                       //write register id
  Serial.print(','); 
  Serial.println(pstr); 
  waitForAck();
}

//Print an integer
//---------------------------------------------------------------------------------------------------
void Network::write(int index, int pstr)
{
  Serial.print("w,");                        //write command identifier
  Serial.print(index);                       //write register id
  Serial.print(','); 
  Serial.println(pstr); 
  waitForAck();
}

//Captures acknowledgment reply from slave arduino
//---------------------------------------------------------------------------------------------------
int Network::waitForAck()
{
  unsigned long stime = millis();
  unsigned long timeout = 5000;
  int done = 0;
  while (done == 0)
  {
    if (available()) 
    {
      startArg(inString);
      char arg0 = readArgC();
      if ((done == 0) && (arg0 =='?')) {done = 1; return 1;}  //? is ack cmd identifier

    }

      if ((millis()-stime)>timeout) {done = 1; return 0;}   //timeout
  } 
  return 0;
}

//Captures data reply from slave arduino
//---------------------------------------------------------------------------------------------------
int Network::waitForData()
{

    unsigned long stime = millis();
    unsigned long timeout = 5000;
  int done = 0;
  while (done == 0)
  {
    if (available()) 
    {
      startArg(inString);
      char arg0 = readArgC();
      if ((done == 0) && (arg0 =='d')) {done = 1; return 1;}  //d is data cmd identifier

    }

    if ((millis()-stime)>timeout) {done = 1; return 0;}   //timeout
  }
  return 0;
}

