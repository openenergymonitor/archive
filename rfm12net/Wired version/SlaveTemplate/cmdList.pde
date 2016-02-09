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
  if(index>19 && index<26) Serial.print(analogRead(index - 20));
  
  //Application dependent variables
  //-----------------------------------------------------------------
  //Example variable set to register 26
  if(index == 26) Serial.print(exampleVariable);
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
  char**p;
  if(index == 15) exampleVariable = strtod(val,p);  //strtod converts a string to double

  //-----------------------------------------------------------------  
}


void SlaveCommands()
{
  //Commands come in comma seperated variable format
  //startArg takes us to the start of the cmd string
  //readArg- from the last comma upto the next comma
  //This makes it possible to decode very long comma
  //seperated variable strings.
  net.startArg(cmd);
  char arg0 = net.readArgC();
   int arg1 = net.readArgI();

  //If cmd starts with @ and has arg1 = this slave ID 
  //then start session.
  if (arg0 == '@' && arg1 == slaveID) { pinMode(1,OUTPUT); net.start();}
  if (arg0 == '@' && arg1 != slaveID) { pinMode(1,INPUT); net.stop();}
  //Close connection
  if (arg0 == 'q')  net.stop();
  
  //If the session has started read incoming cmd's
  if (net.started) 
  {
      //---------------------------------------------------------
      //If we recieve a WRITE command
      if (arg0 == 'w')
      { 
        net.startArg(cmd); 
        net.readArgC(); 

        writeLookup(net.readArgI(),net.readArg());
        //Send an acknowledgment to the master
        Serial.println('?');
      }
      //----------------------------------------------------------
      
      //---------------------------------------------------------
      //If we recieve a READ command
      if (arg0 == 'r')
      { 
        net.startArg(cmd);
        net.readArgC(); 
        //Return the data
        Serial.print("d");
        
        //Looks up and prints data for all arguments of the read string.
        //INDEX 0 is used as a null since it is unlikely that we would want
        //to read the serial rx channel... 
         int arg;
         while(arg!=0)
         {
           arg = net.readArgI();  //Read next arg
           if (arg!=0)
           {
             Serial.print(",");   //Comma seperated
             readLookup(arg);     //Lookup variable
           }
         }
        
        Serial.println();
        //Send an acknowledgment to the master
        //Serial.println('?');
      }
      //----------------------------------------------------------


  }
}


