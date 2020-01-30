# ARCHIVED

---

Replaced with [https://github.com/emoncms/emoncms/tree/bufferedwrite](https://github.com/emoncms/emoncms/tree/bufferedwrite)

### Emoncms + RaspberryPI + Hard drive build notes

**Ready-to-image:** A ready to go image is available here [http://emoncms.org/site/docs/raspberrypihdd](http://emoncms.org/site/docs/raspberrypihdd)

Download the raspbian image and write to the SD card and Harddrive.

[http://www.raspberrypi.org/downloads](http://www.raspberrypi.org/downloads)

### Change /boot/cmdline.txt

Paul from the OpenEnergyMonitor forums has written an alternative guide for setting up the Pi with file system on a hard drive which should be easier for those running Windows, see forum post: 

[http://openenergymonitor.org/emon/node/5092] (http://openenergymonitor.org/emon/node/5092) 


Mount the SD Card on your computer and open the boot) partition. Open to edit the file:

    /boot/cmdline.txt
    
Over write with:

    dwc_otg.lpm_enable=0 console=tty1 root=/dev/sda2 rootfstype=ext4 elevator=deadline rootwait
    
This both tells the PI that the root filesystem is on /dev/sda2 and to not use the serial port for raspberrypi debug purposes as we need it for emoncms.

### Change /etc/fstab on HDD

Mount the Harddrive on your computer and open the main partition. Open to edit the file:

     /etc/fstab
    
Change the root device from: /dev/mmcblk0p2 / to be /dev/sda2

### Install dependencies

With that set the raspberrypi should start-up using the hard drive. Once loged in update the rasbian repositories with:

    sudo apt-get update

Install all dependencies:

    sudo apt-get install apache2 mysql-server mysql-client php5 libapache2-mod-php5 php5-mysql php5-curl php-pear php5-dev php5-mcrypt git-core redis-server build-essential ufw ntp

Install pecl dependencies (serial, redis and swift mailer)

    sudo pear channel-discover pear.swiftmailer.org
    sudo pecl install channel://pecl.php.net/dio-0.0.6 redis swift/swift
    
Add pecl modules to php5 config
    
    sudo sh -c 'echo "extension=dio.so" > /etc/php5/apache2/conf.d/20-dio.ini'
    sudo sh -c 'echo "extension=dio.so" > /etc/php5/cli/conf.d/20-dio.ini'
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

### Install the emoncms application via git

Git is a source code management and revision control system but at this stage we use it to just download and update the emoncms application.

First cd into the var directory:

    $ cd /var/

Set the permissions of the www directory to be owned by your username:

    $ sudo chown $USER www

Cd into www directory

    $ cd www

Download emoncms using git:

    $ git clone https://github.com/emoncms/emoncms.git
    
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

### Install add-on emoncms modules

    cd /var/www/emoncms/Modules
    
    git clone https://github.com/emoncms/raspberrypi.git
    git clone https://github.com/emoncms/event.git
    git clone https://github.com/emoncms/openbem.git
    git clone https://github.com/emoncms/energy.git
    git clone https://github.com/emoncms/notify.git
    git clone https://github.com/emoncms/report.git
    git clone https://github.com/emoncms/packetgen.git
    git clone https://github.com/elyobelyob/mqtt.git
    
See individual module readme's for further information on individual module installation.
            
Install rfm12piphp gateway service:

    sudo cp /var/www/emoncms/Modules/raspberrypi/rfm12piphp /etc/init.d/
    sudo chmod 755 /etc/init.d/rfm12piphp
    sudo update-rc.d rfm12piphp defaults

Edit inittab to allow raspberrypi module access to the serial port.

    $ sudo nano /etc/inittab 

At the bottom of the file comment out the line (by adding a '#' at begining)

    # T0:23:respawn:/sbin/getty -L ttyAMA0 115200 vt100

[Ctrl+X] then [y] then [Enter] to save and exit
