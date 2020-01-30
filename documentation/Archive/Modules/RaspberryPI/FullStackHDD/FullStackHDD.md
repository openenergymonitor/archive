## Archived on 23rd July 2014

Replaced with latest emonhub and buffered write emoncms branch, see here: [https://github.com/emoncms/emoncms/tree/bufferedwrite](https://github.com/emoncms/emoncms/tree/bufferedwrite)

### How to setup a full stack emoncms raspberry PI + HDD basestation

This guide details how to setup a raspberry pi basestation with local data logging and visualisation accessible in the same way as you would access your home router on your local LAN.

**NOTE: THIS IS DOCUMENTATION IS FOR AN UNSTABLE IN DEVELOPMENT VERSION OF EMONCMS THAT EXPLORES A NEW MQTT ENABLED APPROACH AS IS BEING DISCUSSED HERE [https://github.com/emonhub/emonhub/issues/29](https://github.com/emonhub/emonhub/issues/29). It makes use of the MQTT development branch of emoncms and packetgen.** - 21 May 2014

The highlighted parts are the parts that have at least an initial development version working and is documented in the setup documentation below:

![systemdesign21May14.jpg](files/systemdesign21May14.jpg)

Start by downloading the official raspberrpi raspbian image

    [http://www.raspberrypi.org/downloads](http://www.raspberrypi.org/downloads)
    
Upload the image to both the harddrive and the SD Card. On Linux dd can be used for this but care needs to be taken to ensure you select the correct target device so as not to loose data.
    
    sudo dd bs=4M if=2014-01-07-wheezy-raspbian.img of=/dev/sdb
    
### Running from the harddrive:
 
### Change /boot/cmdline.txt on the SD card

Mount the SD Card on your computer and open the boot partition. Open to edit the file:

    /boot/cmdline.txt

Over write with:

    dwc_otg.lpm_enable=0 console=tty1 root=/dev/sda2 rootfstype=ext4 elevator=deadline rootwait

This both tells the PI that the root filesystem is on /dev/sda2 and to not use the serial port for raspberrypi debug purposes as we need it for emoncms.

### Change /etc/fstab on HDD

Mount the Harddrive on your computer and open the main partition. Open to edit the file:

    /etc/fstab

Change the root device from: /dev/mmcblk0p2 / to be /dev/sda2

That's all that is needed to get your raspberrypi running of a harddrive rather than the SD card. You can now insert the SD card in the raspberrypi and connect up the harddrive, power up and use the raspberrypi in the same way as you would use the pi if it was running off an SD card.

## Installing the software needed

Find the IP address of your raspberrypi on your network then connect and login to your pi with SSH, for windows users there's a nice tool called [putty](http://www.putty.org/) which you can use to do this. To connect via ssh on linux, type the following in terminal:

    ssh pi@YOUR_PI_IP_ADDRESS

It will then prompt you for a username and password which are: **username:**pi, **password:**raspberry.

### Install dependencies

Update the rasbian repositories with:

    sudo apt-get update

Install all dependencies:

    sudo apt-get install apache2 mysql-server mysql-client php5 libapache2-mod-php5 php5-mysql php5-curl php-pear php5-dev php5-mcrypt git-core redis-server build-essential ufw ntp python-serial python-configobj mosquitto mosquitto-clients python-pip python-dev

Install python pip dependencies

    sudo pip install tendo
    sudo pip install mosquitto

Install pecl dependencies (redis and swift mailer)

    sudo pear channel-discover pear.swiftmailer.org
    sudo pecl install redis swift/swift
    pecl install -B SAM
    
Add pecl modules to php5 config

    sudo sh -c 'echo "extension=redis.so" > /etc/php5/apache2/conf.d/20-redis.ini'
    sudo sh -c 'echo "extension=redis.so" > /etc/php5/cli/conf.d/20-redis.ini'

Emoncms uses a front controller to route requests, modrewrite needs to be configured:

    $ sudo a2enmod rewrite
    $ sudo nano /etc/apache2/sites-enabled/000-default

Change (line 7 and line 11), "AllowOverride None" to "AllowOverride All".
That is the sections <Directory /> and <Directory /var/www/>.
[Ctrl + X ] then [Y] then [Enter] to Save and exit.

Restart the lamp server:

    $ sudo /etc/init.d/apache2 restart
    
Edit the _inittab_ file to allow the python serial listener to use the serial port, type:

    sudo nano /etc/inittab

At the bottom of the file comment out the line, by adding a ‘#’ at beginning:

    # T0:23:respawn:/sbin/getty -L ttyAMA0 115200 vt100


### Security

[http://blog.al4.co.nz/2011/05/setting-up-a-secure-ubuntu-lamp-server/](http://blog.al4.co.nz/2011/05/setting-up-a-secure-ubuntu-lamp-server/)

#### Install ufw

ufw: uncomplicated firewall, is a great little firewall program that you can use to control your server access rules. The default set below are fairly standard for a web server but are quite permissive. You may want to only allow connection on a certain ip if you will always be accessing your pi from a fixed ip.

UFW Documentation
[https://help.ubuntu.com/community/UFW](https://help.ubuntu.com/community/UFW)

    apt-get install ufw
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw allow 22/tcp
    ufw enable

#### Change root password

Set root password

    passwd root

The default root password used in the ready to go image is **{@.o7SNf~Qg3-0}#SRCM**. 
Change this to a hard to guess password to make your root account secure.

#### Secure MySQL

Run mysql_secure_installation see [mysql docs](http://dev.mysql.com/doc/refman/5.0/en/mysql-secure-installation.html)

    mysql_secure_installation

#### Secure SSH

Disable root login:

    sudo nano /etc/ssh/sshd_config

Set **PermitRootLogin** to **no**
    
### Redis save's

Redis by default will save its in-memory database to the harddisk every x number of changed keys. As emoncms is write intensive we probably dont need it to update as often as the default setting. By changing it to the save every 900 key changes it will probably save around once every 10 mins or so depending on your post rate:

Redis settings: change save to: save 900 1 only

    sudo nano /etc/redis/redis.conf

### Apache access logs

Comment the access log to other-vhosts (add #)

    sudo nano /etc/apache2/conf.d/other-vhosts-access-log
    
### Reboot the pi

    sudo reboot

### Install the emoncms application via git

Git is a source code management and revision control system but at this stage we use it to just download and update the emoncms application.

First cd into the var directory:

    $ cd /var/

Set the permissions of the www directory to be owned by your username:

    $ sudo chown $USER www

Cd into www directory

    $ cd www

Download emoncms using git:

    $ git clone -b mqtt https://github.com/emoncms/emoncms.git
    
Once installed you can pull in updates with:

    git pull
    
### Create a MYSQL database

    $ mysql -u root -p

Enter the mysql password that you set above.
Then enter the sql to create a database:

    mysql> CREATE DATABASE emoncms;

Exit mysql by:

    mysql> exit
    
### Create data repositories for emoncms feed engine's

    sudo mkdir /var/lib/phpfiwa
    sudo mkdir /var/lib/phpfina
    sudo mkdir /var/lib/phptimeseries

    sudo chown www-data:root /var/lib/phpfiwa
    sudo chown www-data:root /var/lib/phpfina
    sudo chown www-data:root /var/lib/phptimeseries

### Set emoncms database settings.

cd into the emoncms directory where the settings file is located

    $ cd /var/www/emoncms/

Make a copy of default.settings.php and call it settings.php

    $ cp default.settings.php settings.php

Open settings.php in an editor:

    $ nano settings.php

Enter in your database settings.

    $username = "USERNAME";
    $password = "PASSWORD";
    $server   = "localhost";
    $database = "emoncms";

Save (Ctrl-X), type Y and exit

### Install packetgen

    cd /var/www/emoncms/Modules
    git clone -b mqtt https://github.com/emoncms/packetgen.git

### Create an account in emoncms and login
This will automatically create the database

### Test run the serial listener and node processor processes.

Login to your pi twice to create two terminal windows.

Locate the emoncms run folder:

    cd /var/www/emoncms/run

Configure your radio settings and target user in jeelistener.conf
The target user will probably be 1 if its the first account you created in emoncms.
To get the emoncms user id type http://localhost/emoncms/user/get.json
    
In the first terminal window run jeelistener.py

    python jeelistener.py
    
In the second terminal window run nodeprocessor.php

    sudo php nodeprocessor.php

With both of these scripts running data should now start to appear in the emoncms node interface and when you change the packet state in packetgen you should see rf packets being send immedietly from the rfm12pi.

