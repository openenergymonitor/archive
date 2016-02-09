int rf_rx()
 {
   if (rf12_recvDone() && rf12_crc == 0) {
        Serial.print("REC ");
        pos = 0;
        for (byte i = 0; i < rf12_len; ++i)
        {instr[pos] = rf12_data[i]; pos++;}
        delay(100);
        return 1;
    }
    return 0;
 }
