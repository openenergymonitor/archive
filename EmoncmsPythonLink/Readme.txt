A very small linux python script for forwarding data from a serial port emoncms. Seperate each value by a space

We wrote it to run on the RaspberryPi to forward serial data from a JeeLink plugged into the USB port to emoncms running locally on the Pi

The Arduino Sketch to run on the JeeLink to receive RFM12B data from multiple nodes (emonTx's, emonGLCD etc.) and produce a CSV string ready to post to emoncms is included.


$ sudo apt-get install python-serial
$ sudo apt-get install python-dev
