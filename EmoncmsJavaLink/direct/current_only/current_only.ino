// EmonLibrary examples openenergymonitor.org, Licence GNU GPL V3

#include "EmonLib.h"             // Include Emon Library
EnergyMonitor emon1;             // Create an instance

void setup()
{  
  Serial.begin(9600);
  
  emon1.current(0, 138.8);       // Current: input pin, calibration.
}

void loop()
{
  emon1.calc_Irms(2960);         // Calculate Irms only
  
  Serial.print("{power:");	
  Serial.print(emon1.Irms*240.0);	// Apparent power
  Serial.println("}");
}
