//ArduinoComm software part of OpenEnergyMonitor.org project

/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Author: Trystan Lea

//Main program class
public class Program
{

//Declares the Arduino object field
public static ArduinoComm myArduino;

   //Main method
   public static void main(String args[]) 
   {
      myArduino = new ArduinoComm();
      myArduino.apiurl = "http://localhost/emoncms3/api/post.json?apikey=YOURAPIKEY&json=";
      //Start the arduino connection - usb port - baud rate
      myArduino.start("/dev/ttyUSB3",9600);
   }

}
