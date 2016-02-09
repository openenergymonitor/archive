//This class:
// - Starts up the communication with the Arduino.
// - Reads the data coming in from the Arduino and print's it out to terminal.
// - It reads and prints out LINES of data coming in

//Code builds upon this great example:
//http://www.csc.kth.se/utbildning/kth/kurser/DH2400/interak06/SerialWork.java

//Load Libraries
import java.io.*;
import java.util.TooManyListenersException;

//Load RXTX Library
import gnu.io.*;

import java.net.*;

class ArduinoComm implements SerialPortEventListener
{

   //Declare serial port variable
   SerialPort mySerialPort;

   //Declare input steam
   InputStream in;
   BufferedReader br;
 
   boolean stop=false;

   public String apiurl;


   //This open's the communcations port with the arduino
   public void start(String portName,int baudRate)
   {

      stop=false; 
      try 
      {
         //Finds and opens the port
         CommPortIdentifier portId = CommPortIdentifier.getPortIdentifier(portName);
         mySerialPort = (SerialPort)portId.open("my_java_serial" + portName, 2000);
         System.out.println("Serial port found and opened");

         //configure the port
         try 
         {
            mySerialPort.setSerialPortParams(baudRate,
            mySerialPort.DATABITS_8,
            mySerialPort.STOPBITS_1,
            mySerialPort.PARITY_NONE);
            System.out.println("Serial port params set: "+baudRate);
         } 
         catch (UnsupportedCommOperationException e)
         {
            System.out.println("Probably an unsupported Speed");
         }

         //establish stream for reading from the port
         try 
         {
            in = mySerialPort.getInputStream();
            //Buffered reader allows us to read a line.
            br = new BufferedReader(new InputStreamReader(in));
         } 
         catch (IOException e) 
         { 
            System.out.println("couldn't get streams");
         }

         // we could read from "in" in a separate thread, but the API gives us events
         try 
         {
            mySerialPort.addEventListener(this);
            mySerialPort.notifyOnDataAvailable(true);
            System.out.println("Event listener added");
         } 
         catch (TooManyListenersException e) 
         {
            System.out.println("couldn't add listener");
         }
      }
      catch (Exception e) 
      { 
         System.out.println("Port in Use: "+e);
      }
   }

   //Used to close the serial port
   public void closeSerialPort() 
   {
      try 
      {
         in.close();
         stop=true; 
         mySerialPort.close();
         System.out.println("Serial port closed");

      }
      catch (Exception e) 
      {
      System.out.println(e);
      }
   }

   public void serialEvent(SerialPortEvent event) 
   { 
      //Reads in data while data is available
      while (event.getEventType()== SerialPortEvent.DATA_AVAILABLE && stop==false) 
      {
         try 
         {
            //------------------------------------------------------------------- 
            //Reads in the line and then prints it to terminal
            System.out.println(br.readLine());

        URL oracle = new URL(apiurl+br.readLine());
        oracle.openStream();

            //-------------------------------------------------------------------
         } 
         catch (IOException e) 
         {
         }
      }
   }

}
