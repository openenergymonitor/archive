sudo service emonhub stop
avrdude -v -c arduino -p ATMEGA328P -P /dev/ttyAMA0 -b 115200 -U flash:w:/home/pi/emonLPL/emonbase_LPL/emonbase_LPL_emonpi.hex
sudo service emonhub start

