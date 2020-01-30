# Archived emoncms raspberrypi sd card build

Replaced buy new low write SD Card optimised version see: [https://github.com/emoncms/emoncms/tree/bufferedwrite](https://github.com/emoncms/emoncms/tree/bufferedwrite)

<div class="alert alert-info"><b>Ready to go image: </b>
There is a pre-compiled image of the following available here
<a href="http://emoncms.org/site/docs/raspberrypiimage">Ready-to-go SD Card Image</a>
</div>

Download Raspbian 'Wheezy' SD card image [here](http://www.raspberrypi.org/downloads) (This guide was made using 9th February 2013 release). Extract the image.

## 1) Write image to an SD card (Linux)

Start by inserting your SD card, your distribution should mount it automatically so the first step is to unmount the SD card and make a note of the SD card device name, to view mounted disks and partitions run:

    $ df -h

You should see something like this:

    Filesystem            Size  Used Avail Use% Mounted on
    /dev/sda6             120G   90G   24G  79% /
    none                  490M  700K  490M   1% /dev
    none                  497M  1.7M  495M   1% /dev/shm
    none                  497M  260K  497M   1% /var/run
    none                  497M     0  497M   0% /var/lock
    /dev/sdb1             3.7G  4.0K  3.7G   1% /media/sandisk

Unmount the SD card, change sdb to match your SD card drive:

    $ umount /dev/sdb1 

If the card has more than one partition unmount that also: 

    $ umount /dev/sdb2

Locate the directory of your downloaded emoncms image in terminal and write it to an SD card using linux tool *dd*:

<div class='alert alert-error'><i class='icon-fire'></i> <b>Warning:</b> take care with running the following command that your pointing at the right drive! If you point at your computer drive you will lose a lot of data!</div>

    $ sudo dd bs=4M if=2012-02-09-wheezy-raspbian.img of=/dev/sdb

Once complete insert the SD card into the PI and connect ethernet and power. All the lights in the corner should light up and flicker. 

The next step is to connect to your PI via SSH from your computer, to do this find the ip address of the PI on your local network, then using linux terminal run:

    $ ssh pi@xxx.xxx.xxx.xxx

On windows you can do the above step with a nice bit of software called putty. Once you make the connection request the PI will ask for a password. The default password is 'raspberry'.

Now that you have your RaspberryPi up and working, we are now going to install the Emonncms software. To do this we need to install the webserver software (Apache), the database (MySQL) as well as the Emoncms scripts (written in a programming language called php).

    $ sudo apt-get update

## 2) Follow Install on Linux guide

Follow steps 1 to 6 of the installing emoncms on linux installation guide

The raspberrypi user is called pi (needed for step 1 and 4)

### [Install on Linux](http://emoncms.org/site/docs/installlinux)

## 3) Install the raspberrypi module

Navigate to the emoncms modules folder

    $ cd /var/www/emoncms/Modules 
    
Download the Raspberry Pi emoncms module into the Modules folder using git:

    $ git clone https://github.com/emoncms/raspberrypi.git
    
If you have already loaded emoncms in your browser prior to installing the raspberrypi module you will need to run the database updater via the Admin tab once you log in to emoncms so that emoncms creates the raspberrypi module table. You can wait to do this at the end if you wish.

## 4) RFM12Pi Setup

Make sure your Raspberry Pi’s UART is disconnected from the console and available for programs to use.

#### a) Backup cmdline.txt: 

    $ sudo cp /boot/cmdline.txt /boot/cmdline_backup.txt

#### b) Edit cmdline.txt to remove references to Pi’s UART (ttyAMA0)

    $ sudo nano /boot/cmdline.txt

edit it from 

    dwc_otg.lpm_enable=0 console=ttyAMA0,115200 kgdboc=ttyAMA0,115200 console=tty1 root=/dev/mmcblk0p2 rootfstype=ext4 elevator=deadline rootwait 

to 

    dwc_otg.lpm_enable=0 console=tty1 root=/dev/mmcblk0p2 rootfstype=ext4 elevator=deadline rootwait 

[Ctrl+X] then [y] then [Enter] to save and exit

#### c) Edit inittab

    $ sudo nano /etc/inittab 

At the bottom of the file comment out the line (by adding a '#' at begining)

    # T0:23:respawn:/sbin/getty -L ttyAMA0 115200 vt100

[Ctrl+X] then [y] then [Enter] to save and exit

## 5) Install rfm12pi gateway service

Install one of the two available gateway scripts and let them run on startup.

#### PHP Gateway

Install serial PHP libraries

    $ sudo apt-get install php-pear php5-dev
    $ sudo pecl install channel://pecl.php.net/dio-0.0.6

Create new file with extension needed by rfm12pi service

    $ sudo nano /etc/php5/cli/conf.d/rfm12pi.ini

and put there two lines line 

    ;Dynamic Extension needed by Rfm12pi
    extension=dio.so

[Ctrl+X] then [y] then [Enter] to save and exit

Install rfm12piphp gateway service:

    sudo cp /var/www/emoncms/Modules/raspberrypi/rfm12piphp /etc/init.d/
    sudo chmod 755 /etc/init.d/rfm12piphp
    sudo update-rc.d rfm12piphp defaults

#### Python Gateway

  Install python serial port and mySQL modules

    $ sudo aptitude install python-serial python-mysqldb
  
  Ensure the script is executable
  
    $ chmod 755 /var/www/emoncms/Modules/raspberrypi/rfm2pigateway.py
  
  Create group emoncms and make user pi part of it

    $ sudo groupadd emoncms
    $ sudo usermod -a -G emoncms pi

  Create a directory for the logfiles and give ownership to user pi, group emoncms

    $ sudo mkdir /var/log/rfm2pigateway
    $ sudo chown pi:emoncms /var/log/rfm2pigateway
    $ sudo chmod 750 /var/log/rfm2pigateway

  Make script run as daemon on startup

    $ sudo cp /var/www/emoncms/Modules/raspberrypi/rfm2pigateway.init.dist /etc/init.d/rfm2pigateway
    $ sudo chmod 755 /etc/init.d/rfm2pigateway
    $ sudo update-rc.d rfm2pigateway defaults 99
    
## 6) Turn off apache logs

To prolong the life of the raspberrypi SD card its a good idea to turn off all apache logging, logging can be turned on when needed for debugging.

There are 3 different files that need to be edited to turn off all apache logging:

1) In apache2.conf:

    sudo nano /etc/apache2/apache2.conf

Replace the ErrorLog line:

    ErrorLog ${APACHE_LOG_DIR}/error.log

with:

    ErrorLog /dev/null

Comment out the line:

    # LogLevel warn

2) In /etc/apache2/conf.d/other-vhosts-access-log

    sudo nano /etc/apache2/conf.d/other-vhosts-access-log

Comment out:

    # CustomLog ${APACHE_LOG_DIR}/other_vhosts_access.log vhost_combined

3) In /etc/apache2/sites-enabled/000-default

    $ sudo nano /etc/apache2/sites-enabled/000-default

Comment out:

    # ErrorLog ${APACHE_LOG_DIR}/error.log
    # LogLevel warn
    # CustomLog ${APACHE_LOG_DIR}/access.log combined

The latest version of the rfm12piphp bash script detailed above also has logging turned off as default.

## 7) Reboot

To complete all of the above reboot the pi

    $ sudo reboot

## 8) In an internet browser on the local network, load emoncms by browsing to the Pi's IP address:

<div class='alert alert-info'>

<h3>Note: Browser Compatibility</h3>

<p><b>Chrome Ubuntu 23.0.1271.97</b> - developed with, works great.</p>

<p><b>Chrome Windows 25.0.1364.172</b> - quick check revealed no browser specific bugs.</p>

<p><b>Firefox Ubuntu 15.0.1</b> - no critical browser specific bugs, but movement in the dashboard editor is much less smooth than chrome.</p>

<p><b>Internet explorer 9</b> - works well with compatibility mode turned off. F12 Development tools -> browser mode: IE9. Some widgets such as the hot water cylinder do load later than the dial.</p>

<p><b>IE 8, 7</b> - not recommended, widgets and dashboard editor <b>do not work</b> due to no html5 canvas fix implemented but visualisations do work as these have a fix applied.</p>

</div>

[http://localhost/emoncms](http://localhost/emoncms)

The first time you run emoncms it will automatically setup the database and you will be taken straight to the login screen. 

Create a new user via the register tab.


## Optional

If your SD Card is larger than 4GB you may want to expand the partition to fill the disk, this can be done easily with the raspi-config utility:

    $ sudo raspi-config

Select Expand root partition to fill SD card. Before rebooting you may also like to change the following:

- If you plan to run the Pi as a headless dataloggin emoncms server as we do then select memory-split and choose the first setting a 240/16 split, the gives the CPU more memory at the expense of graphics which we're not using.

- It is also recommended to disabling booting straight into a desktop since this increases boot time and wastes system resources. If required a desktop can be loaded with $ startx.

- Set locale and timezone as required.

- Change password for user Pi to something of your choice, make it secure it will be storing your home energy data!

Finish and reboot, remember to use your new password when SSH'ing back in!

#### Host name

Set a host name for the Pi to enable host name to be used instead of IP address when connecting to the Pi
Change the default raspberrypi host name to that of your choice eg.emoncms in the file 

    $ sudo nano /etc/hostname 

[Ctrl+X] then [Y] then [Enter] to save and exit

and in the file 

    $ sudo nano /etc/hosts 

[Ctrl+X] then [Y] then [Enter] to save and exit

Reboot the Pi (sudo reboot) and you should be able to SSH back in with $ ssh pi@emoncms if 'emoncms' was your chosen host name. 

<div class='alert'><b>Note:</b> this won't work with all routers, you might need to set the Pi as a 'Fixed Host' in the router config</div>

#### Install minicom

Minicom is a useful tool for reading from the serial port that the rfm12pi board is connected to:

    $ sudo apt-get install minicom

#### Add redirect index.php in /var/www

    <?php header('Location: ../emoncms'); ?>

    <html><body><h1>Welcome</h1>
    <p><a href="emoncms" >Goto Emoncms</a></p>
    </body></html>

