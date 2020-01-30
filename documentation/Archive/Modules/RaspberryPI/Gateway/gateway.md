## Archived on 23rd July 2014

Replaced with latest emonhub and buffered write emoncms branch, see here: [https://github.com/emoncms/emoncms/tree/bufferedwrite](https://github.com/emoncms/emoncms/tree/bufferedwrite)

### How to setup a raspberry PI based gateway (node interface)

This guide details how to setup a raspberry pi based gateway that receives data from wireless sensor nodes using the rfm12pi adapter board and then forwards that data on to a remote emoncms server for logging and visualisation. 

**Note:** The particular approach documented here is a restricted mode as it uses the remote server to decode the node data. This does not make full use of the raspberry pi and emonhub's capabilities such as forwarding data to multiple services but may be simpler in some circumstances. See main menu for all options.

This guide covers the software that is needed on the raspberrypi SD card to make this work.

Download the official raspberrpi raspbian image and write to the SD card.

    [http://www.raspberrypi.org/downloads](http://www.raspberrypi.org/downloads)
    
To upload the image using dd on linux 

Check the mount location of the SD card using:

    df -h
    
Unmount any mounted SD card partitions
    
    umount /dev/sdb1
    umount /dev/sdb2
    
Write the raspbian image to the SD card (Make sure of=/dev/sdb is the correct location)
    
    sudo dd bs=4M if=2014-01-07-wheezy-raspbian.img of=/dev/sdb

Once uploaded to the SD card, insert the SD card into the raspberrypi and power the pi up.

Find the IP address of your raspberrypi on your network then connect and login to your pi with SSH, for windows users there's a nice tool called [putty](http://www.putty.org/) which you can use to do this. To connect via ssh on linux, type the following in terminal:

    ssh pi@YOUR_PI_IP_ADDRESS

It will then prompt you for a username and password which are: **username:**pi, **password:**raspberry.

Now that your loged in to your pi, the first step is to edit the _inittab_ and _boot cmdline config_ file to allow the python gateway which we will install next to use the serial port, type:

    sudo nano /etc/inittab

At the bottom of the file comment out the line, by adding a ‘#’ at beginning:

    # T0:23:respawn:/sbin/getty -L ttyAMA0 115200 vt100

[Ctrl+X] then [y] then [Enter] to save and exit

Edit boot cmdline.txt

    sudo nano /boot/cmdline.txt

replace the line:

    dwc_otg.lpm_enable=0 console=ttyAMA0,115200 kgdboc=ttyAMA0,115200 console=tty1 
    root=/dev/mmcblk0p2 rootfstype=ext4 elevator=deadline rootwait

with:

    dwc_otg.lpm_enable=0 console=tty1 root=/dev/mmcblk0p2 rootfstype=ext4 elevator=deadline rootwait

Reboot the pi so that this will take effect:

    sudo reboot

Next we will install git and python gateway dependencies

    sudo apt-get update
    sudo apt-get install git-core python-serial python-configobj

Download emonhub with git:

    git clone -b threading https://github.com/TrystanLea/emonhub.git
    cd emonhub

Open the emonhub configuration file

    cp conf/emonhub-node-example.conf conf/emonhubNode.conf
    nano conf/emonhubNode.conf

Set your radio frequency and network group in the listener config (use short hand 4 for 433Mhz and 8 for 868Mhz). Set your emoncms.org apikey in the apikey section in the buffer.

Next we make emonhub run as a deamon:

Create a directory for the logfile and give ownership to user pi

    sudo mkdir /var/log/emonhub
    sudo chown pi /var/log/emonhub
    sudo chmod 750 /var/log/emonhub

Copy the oemgateway init script:

    sudo cp service/emonhub /etc/init.d/emonhub
    
Open the emonhub service script and change the user from emonhub to pi.
    
    sudo nano /etc/init.d/emonhub
    
Copy the emonhub service settings file to /etc/default:
    
    sudo cp conf/default/emonhub /etc/default
    
Open the emonhub service settings file:

    sudo nano /etc/default/emonhub

and change source and config path's to:
    
    # Specify the directory in which emonhub.py is found:
    EMONHUB_PATH=/home/pi/emonhub/src/

    # Specify the full config file path:
    EMONHUB_CONFIG=/home/pi/emonhub/conf/emonhubNode.conf

complete service script installation:
    
    sudo chmod 755 /etc/init.d/emonhub
    sudo update-rc.d emonhub defaults 99

The gateway can be started or stopped anytime with following commands:
    
    sudo service emonhub start
    sudo service emonhub stop
    sudo service emonhub restart

To stop running automatically on startup (sudo update-rc.d -f oemgateway remove)

That's it, at this point if you have already setup and powered on any sensor nodes, inputs should start appearing under the node tab in your emoncms account in a few seconds.

## Read only mode

Configure Raspbian to run in read-only mode for increased stability (optional but recommended)

The following is copied from: 
http://emonhub.org/documentation/install/raspberrypi/sd-card/

Then run these commands to make changes to filesystem

    sudo cp /etc/default/rcS /etc/default/rcS.orig
    sudo sh -c "echo 'RAMTMP=yes' >> /etc/default/rcS"
    sudo mv /etc/fstab /etc/fstab.orig
    sudo sh -c "echo 'tmpfs           /tmp            tmpfs   nodev,nosuid,size=30M,mode=1777       0    0' >> /etc/fstab"
    sudo sh -c "echo 'tmpfs           /var/log        tmpfs   nodev,nosuid,size=30M,mode=1777       0    0' >> /etc/fstab"
    sudo sh -c "echo 'proc            /proc           proc    defaults                              0    0' >> /etc/fstab"
    sudo sh -c "echo '/dev/mmcblk0p1  /boot           vfat    defaults                              0    2' >> /etc/fstab"
    sudo sh -c "echo '/dev/mmcblk0p2  /               ext4    defaults,ro,noatime,errors=remount-ro 0    1' >> /etc/fstab"
    sudo sh -c "echo ' ' >> /tmp/fstab"
    sudo mv /etc/mtab /etc/mtab.orig
    sudo ln -s /proc/self/mounts /etc/mtab
    
The Pi will now run in Read-Only mode from the next restart.

Before restarting create two shortcut commands to switch between read-only and write access modes.

Firstly “ rpi-rw “ will be the command to unlock the filesystem for editing, run

    sudo nano /usr/bin/rpi-rw

and add the following to the blank file that opens

    #!/bin/sh
    sudo mount -o remount,rw /dev/mmcblk0p2  /
    echo "Filesystem is unlocked - Write access"
    echo "type ' rpi-ro ' to lock"

save and exit using ctrl-x -> y -> enter and then to make this executable run

    sudo chmod +x  /usr/bin/rpi-rw

Next “ rpi-ro “ will be the command to lock the filesytem down again, run

    sudo nano /usr/bin/rpi-ro

and add the following to the blank file that opens

    #!/bin/sh
    sudo mount -o remount,ro /dev/mmcblk0p2  /
    echo "Filesystem is locked - Read Only access"
    echo "type ' rpi-rw ' to unlock"

save and exit using ctrl-x -> y -> enter and then to make this executable run

    sudo chmod +x  /usr/bin/rpi-ro

Lastly reboot for changes to take effect

    sudo shutdown -r now
