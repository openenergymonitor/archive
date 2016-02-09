int argpos;
int slen;
char *str; 
char argStr[25];
  int started;

//Start master slave session 
//set tristate to LOW allows
//comunication on SLAVE RX -> MASTER TX bus
//---------------------------------------------------------------------------------------------------
void netstart()
{
  started = 1;
  strcat(outstr,"?"); needToSend = 1;                       //Acknowledgment
}

//Stop the connection
//---------------------------------------------------------------------------------------------------
void netstop()
{
  strcat(outstr,"!"); needToSend = 1;
  started = 0;
}

//Goto start of cmd string + a few other things
//---------------------------------------------------------------------------------------------------
void startArg(char *strIN)             
{
  argpos = 0;                                   //cmd string start
  str = strIN;                                  //fetch cmd string
  slen = strlen(str);                           //get cmd string length
}

//Read argument between last comma and next comma:  ...,10.0,...
//---------------------------------------------------------------------------------------------------
char * readArg()                       
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
char readArgC()
{
  char * tmp = readArg();                       //Read the first argument
  return tmp[0];                                //Send back only the first character
}
//Convert argument string into integer
//---------------------------------------------------------------------------------------------------
int readArgI()
{
  return atoi(readArg());                       //Send back integer
}
//Convert argument string into double
//---------------------------------------------------------------------------------------------------
double readArgD()
{
  return strtod(readArg(),NULL);                //Send back double, uses quite a bit of memory!
}



//=====================================================================================



/***************************************************************************************/
//  The Read lookup table, contains index register of variables to read
//  Place variables that you would like to read here, for example:
//  if(index == 26) Serial.print(currentA);
/***************************************************************************************/
void readLookup(int index)
{
  //Standard read functions
  //----------------------------------------------------------------
  //Read from digital channels 0-13, but also analog channels set to digital 14-19
  //Allocating 8 to 13 as readable to avoid conflict with hardware connected to other pins.
  if (index>1 && index<14) {pinMode(index, INPUT); Serial.print(digitalRead(index)); }
        
  //Read from analog channels - they are mapped to 20 through to 25.
  if(index>19 && index<26) {char buffer[10]; itoa (analogRead(index - 20),buffer,10); strcat(outstr,buffer); needToSend = 1; }
  
  //Application dependent variables
  //-----------------------------------------------------------------
  //Example variable set to register 26
  //if(index == 26) Serial.print(exampleVariable);
  //-----------------------------------------------------------------
}

/***************************************************************************************/
//  The Write lookup table, contains index register of variables or outputs to write to
//  Place variables or outputs that you would like to write to here for example:
//  if(index == 15) currentVperA = strtod(val,p); //strtod converts a string to double
/***************************************************************************************/
void writeLookup(int index, char* val)
{
  //Standard write functions
  //----------------------------------------------------------------
  if (index>1 && index<14) //Allocating here digital pins 8 to 13 as writeable
                           //to avoid conflict with hardware connected to other pins.
  {
    pinMode(index, OUTPUT);  
    int dstate = atoi(val);
    if (dstate<0) dstate=0;
    if (dstate>1) dstate=1;
    Serial.println(index);
    digitalWrite(index,dstate);
  }
  //----------------------------------------------------------------
  
  //Application dependent variables
  //-----------------------------------------------------------------  
  //char**p;
  //if(index == 15) exampleVariable = strtod(val,p);  //strtod converts a string to double

  //-----------------------------------------------------------------  
}


void SlaveCommands()
{
  //Commands come in comma seperated variable format
  //startArg takes us to the start of the cmd string
  //readArg- from the last comma upto the next comma
  //This makes it possible to decode very long comma
  //seperated variable strings.
  startArg(instr);
  char arg0 = readArgC();
   int arg1 = readArgI();

  //If cmd starts with @ and has arg1 = this slave ID 
  //then start session.
  if (arg0 == '@' && arg1 == slaveID) { netstart();}
  if (arg0 == '@' && arg1 != slaveID) { netstop();}
  //Close connection
  if (arg0 == 'q')  netstop();
  
  //If the session has started read incoming cmd's
  if (started) 
  {
      //---------------------------------------------------------
      //If we recieve a WRITE command
      if (arg0 == 'w')
      { 
        startArg(instr); 
        readArgC(); 

        writeLookup(readArgI(),readArg());
        //Send an acknowledgment to the master
          strcat(outstr,"?"); needToSend = 1;
      }
      //----------------------------------------------------------
      
      //---------------------------------------------------------
      //If we recieve a READ command
      if (arg0 == 'r')
      { 
        startArg(instr);
        readArgC(); 
        //Return the data
          strcat(outstr,"d"); 
        
        //Looks up and prints data for all arguments of the read string.
        //INDEX 0 is used as a null since it is unlikely that we would want
        //to read the serial rx channel... 
         int arg;
         while(arg!=0)
         {
           arg = readArgI();  //Read next arg
           if (arg!=0)
           {
               strcat(outstr,","); 
             readLookup(arg);     //Lookup variable
           }
         }
        
         needToSend = 1;
        //Send an acknowledgment to the master
        //Serial.println('?');
      }
      //----------------------------------------------------------


  }
}


