# ARCHIVED

---

## Heatpump Monitor (local HDD)

**(emonTx v3, Raspberry Pi, Emoncms V8, Local harddrive installation)**

This guide details how to build a heatpump monitor with local data logging and visualisation accessible in the same way as you would access your home router on your local LAN. It uses the OpenEnergyMonitor emonTx V3, a Raspberry Pi with an RFM12Pi expansion board and a connected harddrive.

## System overview

![System overview](files/system.jpg)

**Measurement**

Temperature measurements are performed with one-wire DS18B20 digital temperature sensors, which are small thermometers with built in 12bit ADC's and microcontroller. The sensor communicates over a one-wire bus via the one-wire serial protocol and requires little in the way of additional components. The sensors have an accuracy of +/-0.5 deg C in the range -10 deg C to +85 deg C. [Building blocks: DS18B20 Temperature sensing](http://openenergymonitor.org/emon/buildingblocks/DS18B20-temperature-sensing)

Power measurement is performed with a clip-on current transformer (CT) which measures AC current and a plug in AC to AC power adapter which measures AC voltage. AC power is then calculated onboard the emontx. See the AC power theory section of the building blocks documentation for more detail [building blocks](http://openenergymonitor.org/emon/buildingblocks)

The rate at which power and temperature measurements are taken can be set in the emontx firmware, but is usually around 10s per set of measurements.

## Parts list

Here are the parts you will need, most of them are available from the OpenEnergyMonitor shop.

    1x emonTx V3 pre-assembled
    1x 100A max clip-on current sensor CT
    1x AC-AC Power Supply Adapter - AC voltage sensor (Both UK and Euro plugs are available)
    4x Encapsulated DS18B20 Temperature sensors

    1x Raspberry Pi (Model B) - Web-connected Base Station
    1x RFM12Pi - Raspberry Pi Base Station Receiver Board
    1x Blank SD Card
    1x (optional) RaspberryPi case, this one is nice: Pimoroni Berryblack Case
    1x Harddrive with SATA to USB converter
    1x Powered USB hub (for powering the harddrive)

You might also need:

    1x 5V DC USB Power Adapter (UK Plug)
    1x Micro-USB cable
    1x Ethernet cable
    1x USB to serial programmer
    
Note: it's important that the frequency (868Mhz / 433Mhz) of the chosen modules match each other and is a legal ISM band in your country.

## System setup

The OpenEnergyMonitor hardware listed above all come pre-assembled, no soldering is required. However heatpump monitoring we need to change the default firmware on the emonTx in order to monitor 4x temperature sensors.

Changing the EmonTx firmware

1. Start by following the: setting up the arduino anvironment guide

2. Click on File > sketchbook > OpenEnergyMonitor > emonTxFirmware > emonTxV3 > 
RFM12B > Examples > EmonTxV3HeatpumpMonitor.

3. Set the frequency of your emontx at the top of the sketch/firmware and nodeid 
if you wish to change it. Plug up your emonTx v3 with a usb to serial programmer 
and click on Upload.

**Continuing with the hardware installation:**

_Example to go here on installing the temperature sensors and power monitoring on the heatpump_

## Setting up the RaspberryPI with a harddrive for local logging and visualisation

**[How to: setup a raspberrypi with a harddrive for local logging and visualisation](../../Modules/RaspberryPI/FullStackHDD/FullStackHDD.md)**
_Uses emonhub to link rfm12pi to local emoncms as well as having the ability to forward the data to multiple remote services_

## Setting up emoncms

## Using the heatpump monitor

The best place to start with making use of your heatpump monitor is to understand exactly how a heatpump works, when it is most efficient, so that you can compare a theoretical understanding with the data being generated from your heatpump monitor.

John Cantor's book on heatpumps has several useful chapters on a heatpump's operation that I (Trystan) would highly recommend, here's his website: [http://heatpumps.co.uk/](http://heatpumps.co.uk/)

We're currently working on tools that model a heatpumps operation theoretically, making it possible to explore how different heating profiles and external and internal conditions affect its operation:

Theory: [Heatpump model](http://openenergymonitor.org/emon/node/3021)

Web App: [http://www.emoncms.org/openbem/heatpumpexplorer](http://www.emoncms.org/openbem/heatpumpexplorer)

If you build a heatpump monitor and can add your experience to this documentation please get in contact on the forums.

