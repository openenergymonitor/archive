# ARCHIVED

---

## Building the raspberrypi gateway image (draft)

You may also like to refer to Martin's original blog post here:

[http://harizanov.com/2013/08/rock-solid-rfm2pi-gateway-solution/](http://harizanov.com/2013/08/rock-solid-rfm2pi-gateway-solution/)

Download IPE: [http://nutcom.hu/?page_id=108](http://nutcom.hu/?page_id=108)

Write the IPE image to an SD Card.

SSH is disabled by default so we need to connect Pi to monitor and keyboard. 

Login with user *root* and password *root*

To enable mounting FS as R/W to change stuff

    $ ipe-rw 

To lock file system down as R/O

    $ ire-re 

Run the following to enable ssh, generate keys and disable telnet

    $ ipe-rw
    $ dpkg-reconfigure openssh-server
    $ update-rc.d ssh enable
    $ update-rc.d openbsd-inetd remove
    $ /etc/init.d/openbsd-inetd stop
    $ mv /root/.bashrc.fb /root/.bashrc
    $ ipe-ro

Or run firstboot if you want to do all the above but also expand to fill the SD card.

Make sure that Raspberry Pi’s UART is disconnected from the console and available for programs to use. The problem here is that */boot/cmdline.txt* is mounded on a R/O partition, easiest way is to insert the SD card in a computer and edit that file there. Remove the text that make reference to the UART i.e.

update: there is a nice script which will automate the process of removing the UART from the console: https://github.com/lurch/rpi-serial-console

    console=ttyAMA0,115200 kgdboc=ttyAMA0,115200

run

    $ sudo nano /etc/inittab

At the bottom of the file comment out the line (by adding a ‘#’ at beginning)

    # T0:23:respawn:/sbin/getty -L ttyAMA0 115200 vt100
    
[Ctrl+X] then [y] then [Enter] to save and exit

Now that the UART is free to use the next step is to setup the RFM2Pi software.

    sudo apt-get update

Install git

    sudo apt-get install git-core

Install python serial libraries
    
    sudo aptitude install python-serial python-configobj
    
Download oem_gateway with git
    
    git clone http://github.com/Jerome-github/oem_gateway.git

Cd into the oem_gateway folder and make a copy of the configuration file, placing the configuration file on the boot partition for easy access as its a fat partition and can be opened on any computer when you plug in the sd card.
    
    cd oem_gateway
    cp oemgateway.conf.dist /boot/oemgateway.conf

Open the configuration file to edit
    
    nano /boot/oemgateway.conf

Defaults:

    # OemGateway Configuration file example
    # Copy this as oemgateway.conf (or any custom name) and edit settings

    # Each listener and each buffer has 
    # - a [[name]]: a unique string
    # - a type: the name of the class it instantiates
    # - a set of init_settings (depends on the type)
    # - a set of runtime_settings (depends on the type)
    # Both init_settings and runtime_settings sections must be defined, 
    # even if empty. Init settings are used at initialization, and runtime
    # settings are refreshed on a regular basis.

    # All lines beginning with a '#' are comments and can be safely removed.

    ####################
    # Gateway settings #
    ####################
    [gateway]
    # loglevel must be one of DEBUG, INFO, WARNING, ERROR, and CRITICAL
    # see here : http://docs.python.org/2/library/logging.html
    loglevel = DEBUG

    #############
    # Listeners #
    #############
    [listeners]

    # This listener manages the RFM2Pi module
    [[RFM2Pi]]
      type = OemGatewayRFM2PiListener
    [[[init_settings]]]
      com_port = /dev/ttyAMA0
    [[[runtime_settings]]]
      sgroup = 210
      frequency = 8
      baseid = 15
      #time period to update time clock on emonGLCD
      sendtimeinterval = 300

    # This listener gets data from a socket
    [[Socket]]
      type = OemGatewaySocketListener
    [[[init_settings]]]
      port_nb = 50011
    [[[runtime_settings]]]

    ###########
    # Buffers #
    ###########
    [buffers]

    # The two following buffers instantiate the same class, 
    # that formats the data for an emoncms instance.
    # One sends the data to a local instance, the other one
    # to a distant one.
    # If active is set to False, the buffer neither records nor sends any data,
    # but it holds unsent data until active becomes True.
    [[emoncms_local]]
      type = OemGatewayEmoncmsBuffer
    [[[init_settings]]]
    [[[runtime_settings]]]
      domain = localhost 
      apikey = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
      protocol = http://
      active = False
      path = /emoncms

    [[emoncms_remote]]
      type = OemGatewayEmoncmsBuffer
    [[[init_settings]]]
    [[[runtime_settings]]]
      domain = emoncms.org
      apikey = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
      protocol = http://
      active = True
      path =

Set oem gateway script to run at boot

    $ nano /etc/rc.local

    (sleep 10; python /root/oem_gateway/oemgateway.py --config-file /boot/oemgateway.conf)&

edit host name with

    $ sudo nano /etc/hosts
    $ sudo nano /etc/hostname

to be 'oemgateway'

User: things to do after booting up for first time

    $ ssh root@oemgateway

password: root

Change password with 

    $ passwd

Set timezone to ensure time sent to emonGLCD is correct - default in Europe/London 

    $ dpkg-reconfigure tzdata
