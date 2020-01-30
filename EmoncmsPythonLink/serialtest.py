import serial, sys
ser = serial.Serial('/dev/ttyUSB3', 9600)
while 1 :
        sys.stdout.write(ser.readline())

