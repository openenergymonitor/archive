void print_glcd_setup()
{
  Serial.println("emonGLCD solar PV monitor - gen and use");
  Serial.println("openenergymonitor.org");
  Serial.print("Node: "); 
  Serial.print(MYNODE); 
  Serial.print(" Freq: "); 
  if (freq == RF12_433MHZ) Serial.print("433Mhz");
  if (freq == RF12_868MHZ) Serial.print("868Mhz");
  if (freq == RF12_915MHZ) Serial.print("915Mhz"); 
  Serial.print(" Network: "); 
  Serial.println(group);
}

void print_emonbase_payload()
{
  Serial.print("2 emonbase: ");
  Serial.print(emonbase.hour);
  Serial.print(':');
  Serial.print(emonbase.mins);
  Serial.print(':');
  Serial.println(emonbase.power);
}
