# ARCHIVED

---

# Home Energy Monitor

**(emonTx v3, Raspberry Pi, Emoncms V8)**

This guide details how to build a web-connected home electricity monitor using the OpenEnergyMonitor emonTx V3, a Raspberry Pi (with RFM12Pi expansion board) and Emoncms web-app for logging and viewing the data. At the end of this guide you should have your system up and running with your energy data accessible on a mobile friendly web page like this:

![System overview](files/cover.jpg)

## System overview

![System overview](files/system.jpg)

## Parts list

Here are the parts you will need, most of them are available from the OpenEnergyMonitor shop.

    1x emonTx V3 pre-assembled
    1x 100A max clip-on current sensor CT
    1x AC-AC Power Supply Adapter - AC voltage sensor (Both UK and Euro plugs are available)

    1x Raspberry Pi (Model B) - Web-connected Base Station
    1x RFM12Pi - Raspberry Pi Base Station Receiver Board
    1x Blank SD Card
    1x (optional) RaspberryPi case, this one is nice: Pimoroni Berryblack Case

Note: it's important that the frequency (868Mhz / 433Mhz) of the chosen modules match each other and is a legal ISM band in your country.

You might also need:

    1x 5V DC USB Power Adapter (UK Plug)
    1x Micro-USB cable
    1x Ethernet cable
    1x USB to serial programmer

## System setup

The OpenEnergyMonitor hardware listed above all come pre-assembled, no soldering is required. However for this particular configuration we need to change the default firmware on the emonTx in order to gain the benefit of the new addition of accumulated watt-hours calculation on the emonx which increases the accuracy and reliability of the energy measurement.

Changing the EmonTx firmware

1. Start by following the: [setting up the arduino environment guide](http://openenergymonitor.org/emon/buildingblocks/setting-up-the-arduino-environment)

2. Click on File > sketchbook > OpenEnergyMonitor > emonTxFirmware > emonTxV3 > 
RFM12B > Examples > emonTxV3_continuous_kwhtotals_noeeprom. This will load the 
firmware example that both uses the higher accuracy continuous sampling code by 
Robin Emley and the watt hour calculation code mentioned above.

3. Set the frequency of your emontx at the top of the sketch/firmware and node ID 
if you wish to change it. Plug up your emonTx v3 with a usb to serial programmer 
and click on Upload.

![rfsettings.png](files/rfsettings.png)

**Continuing with the hardware installation:**

Place or wall-mount your emontx near your meter cabinet

Plug in the CT sensor into the emonTx first before clipping it around the mains wire and before powering-up the emonTx. Clip the sensor around either the live or neutral wire of the circuit you wish to measure. (Don't clip around both as the magnetic fields will cancel).

Plug in the AC-AC Voltage adapter this both power's the emonTx and is used for sampling the AC mains voltage. A red light should now appear near the AC-AC adapter input and start flashing once every 10 seconds. Thats the emonTx set up! The emonTx can also be powered by batteries or from DC power source. See the emonTx [technical wiki](http://wiki.openenergymonitor.org/index.php?title=EmonTx_V3) for info on using alternate power sources and interporating the red LED behaviour

Plug the RFM12Pi hardware expansion module onto the Pi's GPIO pins taking care to align up pin 1, the RFM12Pi should be connected to the GPIO pins connector closest to the edge of the pi.

## Setting up the RaspberryPI

The easiest way to setup a RaspberryPI for use an OpenEnergyMonitor base-station is to use the ready-to-go image. The image has EmonHub and emoncms pre-installed and can be used to post data to a remote emoncms server such as emoncms.org or it can be used to record data locally on the SD card, or a connected hard-drive.

Download the ready-to-go image here: [emonSD-13-08-14.img.zip](http://files.openenergymonitor.org/emonSD-13-08-14.img.zip)

*The full setup for the raspberrypi emonhub and emoncms software stack is described here, including a step by step build guide if you would prefer to build the image yourself: [https://github.com/emoncms/emoncms/tree/bufferedwrite](https://github.com/emoncms/emoncms/tree/bufferedwrite)*

Write the image to the SD Card using an image writting tool (The minimum SD card size is 4GB). Insert the SD card in the raspberry pi, connect up the rfm12pi adapter board, power and ethernet.

It usually takes a minute to boot up the ACT light on the PI should be actively flickering for the first minute.

Find the raspberrypi's IP address on your network, this can usually be found in your router status page. Alternatively there's a useful app called fing that can be used to scan for devices on a network.

Login to the raspberrypi with SSH (Putty is a useful tool to do this on windows).

    user@user:~$ ssh pi@192.168.1.70
    pi@192.168.1.70's password:

The password is: raspberry

### Configuring EmonHub to post to emoncms.org

By default the main operating system partition on the raspberry pi is in read-only mode, this helps preserve SD card lifespan. To edit the emonhub configuration we need to first place the raspberrypi in write mode:

    rpi-rw
    
Then open emonhub.conf to edit with:

    sudo nano /boot/emonhub.conf
    
Under the title 'Reporters' you will see the reporter entry: 
    
    [[emonCMS]]
        type = EmonHubEmoncmsReporter
        [[[init_settings]]]
        [[[runtime_settings]]]
            url = http://localhost/emoncms
            apikey = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
            
Change the url to http://emoncms.org and set the apikey to your emoncms.org account apikey.

To send data to both a local and remote installation of emoncms:

    [reporters]

    [[emonCMS_local]]
        Type = EmonHubEmoncmsReporter
        [[[init_settings]]]
        [[[runtimesettings]]]
            url = http://localhost/emoncms
            apikey = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
            
    [[emonCMS_remote]]
        Type = EmonHubEmoncmsReporter
        [[[init_settings]]]
        [[[runtimesettings]]]
            url = http://emoncms.org
            apikey = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

Under the title 'Interfacers' set the radio frequency and group of your radio network. This should be the same frequency and group as set in the emontx above.

The emontx firmware that we installed above has a non standard packet structure, it uses the long datatype in addition to integers. We therefore need to tell emonhub to decode the data received from the emontx accordingly. This is done in the nodes section:

Under the title 'Nodes' replace the lines:
    
    [[99]]
            datacode = h
            datacodes = l, h, h, h,

with:

    [[10]]
            datacodes = L, h, h, h, h, l, l, l, l
        
Change [[10]] to the node id of your emontx if its different.

    L = unsigned long
    h = signed integer
    l = signed long

To finsish, save and exit and then place the raspberrypi back in read-only mode:

    rpi-ro

Navigate to the inputs page in your emoncms.org account, you should now see a list of variables. The next section details how to configure these variables.

## Setting up emoncms

**Input naming**

Navigate to the Input tab in emoncms.org, you should see a list of inputs, 9 in all. The inputs wont have any names to start with. Click on the pencil edit icon and enter the names in this screenshot so that you can identify the inputs:

![homeenergymonitor_inputlist.png](files/homeenergymonitor_inputlist.png)

If you know what CT1 Power is you may want give it a more indicative name such as 'house power'.

Once complete refresh the page, this will make the naming available in the next step.

**Log to feed**

Click on the wrench icon for CT1 Power: Select *Log to feed*. Enter a name for the feed such as "CT1 Power" or House Power or whatever, it should suggest the name you gave the input. Select the Fixed Interval With Averaging (PHPFiwa) feed engine and select the 10s feed interval (this is the same rate at which the EmonTx is posting). Click Add to create the feed.

![homeenergymonitor_inputconfig.png](files/homeenergymonitor_inputconfig.png)

**Wh accumulator**

Click on the wrench icon for CT1 Wh: Select *Wh accumulator*. Enter a name for the feed or use suggested, select the Fixed Interval No Averaging (PHPFina) feed engine and select the 10s feed interval.

*If your using the other CT's repeat for each CT.*

**View feeds**

Click on the feeds tab, you should now see two feeds: CT1 Power and CT1 Wh. You can view a graph of these feeds by clicking on the view icon.

![feeds.png](files/feeds.png)

**My Electric**

**Note** myelectric has now been integrated in an application specific dashboards module called app. On emoncms.org this is available under the Apps tab. The github repository for the app module can be found here https://github.com/emoncms/app and can be installed by downloading or git cloneing into the emoncms/Modules folder.

Click on the My Electric tab. Click on the wrench top-right on the my electric page to bring up the configuration interface.

Select under the power feed: CT1 Power, and under the bar graph feed: CT1 Wh, select *'watt hours elapsed'* for *'Bar graph feed type'*. Click save to complete.

Thats it you should now see your home electricity dashboard.

![System overview](files/emoncms_myelectric.png)

Open the page on your mobile phone and bookmark it. When you click on the bookmark it will automatically log you in and bring up your electricity dashboard.

## Using the monitor

The appliance list tool is a useful exercise for answering questions such as how much electricity can be save by using low energy lighting such as LED's? How much electricity does turning off lighting when not in use save?
Login to emoncms.org and then navigate to Extras > Report > Appliance list
[The Electricity audit and savings case study](http://openenergymonitor.org/emon/sustainable-energy/electricity-audit-and-savings-case-study) provides more background on the appliance list exercise
