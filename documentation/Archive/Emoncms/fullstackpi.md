### Archived

Replaced with [https://github.com/emoncms/emoncms/tree/bufferedwrite](https://github.com/emoncms/emoncms/tree/bufferedwrite)

### Emoncms on the RaspberryPI: Partitioned SD Card (Experimental)

<div class="alert alert-info">
<p>This guide details how to build the full software stack for running emoncms on the raspberry pi.</p>

<p><b>Features in this experimental build: </b><br>

<p><b>Two partitions (read-only OS & read-write data):</b><br>New in this version of the "full stack" build is that the operating system (debian linux) is placed on a read-only partition as is done with the rock solid gateway forwarder. A second partition is then used for storing monitored data. This should provide for a more robust setup in that at least in the event of a failure on the writeable data partition the SD card should boot and be functional as a simple gateway forwarder.</p>

<p><b>Redis: </b><br>The other notable feature of this build is that it is running the latest emoncms development branch which introduces the use of redis (an in memory database). Redis is used to store the feed and input last values which do not need to be persistent on disk. Storing these in memory therefore reduces disk writes potentially increasing the life span of the SD card.</p>

<p>With a simple system with a few energy monitoring and temperature nodes posting show a reducion in the write rate of about 45%</p>
</div>

Start by either installing the oem_gateway image as detailed [here](http://emoncms.org/site/docs/raspberrypigateway) or if you wish follow the gateway installation guide [here](http://emoncms.org/site/docs/raspberrypigatewaybuild).

The gateway image comes with a couple of things that make getting started easier, primarily that it already has ssh server installed and so you dont need a hdmi monitor or keyboard connected to your pi to follow the rest of this guide. You can just log into your pi over your network.

The oem_gateway image, comes with the following things pre-installed: ipe debian linux, ssh server, serial settings for rfm12pi, git and Jerome's oem gateway.

One you have written the image to your SD card, insert it in the pi, connect your pi up to ethernet and power and then wait for it to appear in your devices list on your internet router.

SSH into the pi with:

    ssh root@192.168.1.77
    
and password **root**

The default mode for the SD Card is read-only. Change the mount mode to read/write by typing:

    ipe-rw
    
### Step 1: Resize the root partition so that we have some room to install emoncms

    fdisk /dev/mmcblk0

    press d to delete
    press 2 for second partition
    press n to create new partition
    press p for primary
    press 2 for second partition
    press enter to start partition at default
    enter (1200MB*1024*1024) / 512 = 2457600 to create 1200MB partition
    press w to write
    
reboot the pi

    reboot
    
ssh back into the pi and put the pi in read/write mode with: *ipe-rw*

    resize2fs /dev/root
    
### Step 2: Create a new partition that will hold the data

    fdisk /dev/mmcblk0

    press n to create new partition
    press p for primary
    press 3 for third partition
    press enter to start partition at default
    press enter to end partition at default
    press w to write

reboot the pi

    reboot

ssh back into the pi and put the pi in read/write mode with: *ipe-rw*

Now that we have a partition for our data the next step is to create or set the filesystem. Create an ext2 file system which is non journaling with the following:

    mkfs.ext2 /dev/mmcblk0p3
    
Create a mount point for the data partition

    mkdir /data

Mount the filesystem at /data at startup:

    nano /etc/fstab
    
Add the line: 
tep
    /dev/mmcblk0p3  /daSta           ext2    errors=remount-ro 0     0

### Step 3: Install Apache, PHP, MySQL, Redis

You may need to start by updating the system repositories

    apt-get update
    
Install dependencies
    
    apt-get install apache2 mysql-server mysql-client php5 libapache2-mod-php5 php5-mysql php5-curl php-pear php5-dev php5-mcrypt git-core redis-server build-essential ufw ntp
    
Install pecl dependencies (serial, redis and swift mailer)

    pear channel-discover pear.swiftmailer.org
    pecl install channel://pecl.php.net/dio-0.0.6 redis swift/swift

Add pecl modules to php5 config

    sh -c 'echo "extension=dio.so" > /etc/php5/apache2/conf.d/20-dio.ini'
    sh -c 'echo "extension=dio.so" > /etc/php5/cli/conf.d/20-dio.ini'
    sh -c 'echo "extension=redis.so" > /etc/php5/apache2/conf.d/20-redis.ini'
    sh -c 'echo "extension=redis.so" > /etc/php5/cli/conf.d/20-redis.ini'

Emoncms uses a front controller to route requests, modrewrite needs to be configured:

    a2enmod rewrite
    nano /etc/apache2/sites-enabled/000-default
    
Change (line 7 and line 11), "AllowOverride None" to "AllowOverride All". 
Turn off the access.log by adding a # in front of the line that starts with CustomLog. 
[Ctrl + X ] then [Y] then [Enter] to Save and exit.

### Data directory setup on write data partition

Log directory:

    mkdir /data/log
    
Apache log directory:

    nano /etc/apache2/envvars
    
    export APACHE_LOG_DIR=/data/log/apache2$SUFFIX
    
    mkdir /data/log/apache2
    
Mysql directory:

    mkdir /data/mysql
    cp -rp /var/lib/mysql/. /data/mysql
    
    nano /etc/mysql/my.cnf
    change line datadir to /data/mysql
    
and redis:
    
    mkdir /data/redis
    chown redis:redis /data/redis
    mkdir /data/log/redis
    chown redis:redis /data/log/redis
    
    nano /etc/redis/redis.conf

set LogFile location to /data/log/redis/redis-server.log

    change save to: save 900 1 only

Turn off vhosts access log, place a # in front of CustomLog again, save and exit.

    nano /etc/apache2/conf.d/other-vhosts-access-log

### Create data repositories for emoncms feed engine's

    sudo mkdir /data/emoncmsdata/phpfiwa
    sudo mkdir /data/emoncmsdata/phpfina
    sudo mkdir /data/emoncmsdata/phptimeseries

    sudo chown www-data:root /data/emoncmsdata/phpfiwa
    sudo chown www-data:root /data/emoncmsdata/phpfina
    sudo chown www-data:root /data/emoncmsdata/phptimeseries

### Install emoncms

Download emoncms:

    cd /var/www
    git clone https://github.com/emoncms/emoncms.git
    
Create mysql database for emoncms:

    mysql -u root -p (image password is: raspberry) 
    CREATE DATABASE emoncms;

Enter mysql and timestore authentication settings in the emoncms settings file:

First create a copy of default.settings.php called settings.php:

    cp default.settings.php settings.php
    
Use the mysql user, password and database name as used when creating the emoncms database.

In the feedsettings section uncomment the datadir defenitions and set them to the location of each of the feed engine data folders on your system:
    
    'phpfiwa'=>array(
        'datadir'=>"/data/emoncmsdata/phpfiwa/"
    ),
    'phpfina'=>array(
        'datadir'=>"/data/emoncmsdata/phpfina/"
    ),
    'phptimeseries'=>array(
        'datadir'=>"/data/emoncmsdata/phptimeseries/"
    )

### Install raspberrypi module

    cd /var/www/emoncms/Modules
    git clone https://github.com/emoncms/raspberrypi.git
    sudo cp /var/www/emoncms/Modules/raspberrypi/rfm12piphp /etc/init.d/
    nano /etc/init.d/rfm12piphp
    
If you wish to use the php gateway rather than the python gateway that comes with the oem_gateway image you will need to disable the entry for the python gateway in /etc/rc.local.
    
    nano /etc/rc.local

Comment out the existing entry by placing a # before the following line, as so:

    # (sleep 10; python /root/oem_gateway/oemgateway.py --config-file /boot/oemgateway.conf

remove lock file 

## Security

[http://blog.al4.co.nz/2011/05/setting-up-a-secure-ubuntu-lamp-server/](http://blog.al4.co.nz/2011/05/setting-up-a-secure-ubuntu-lamp-server/)

### Install ufw

ufw: uncomplicated firewall, is a great little firewall program that you can use to control your server access rules. The default set below are fairly standard for a web server but are quite permissive. You may want to only allow connection on a certain ip if you will always be accessing your pi from a fixed ip.

UFW Documentation
[https://help.ubuntu.com/community/UFW](https://help.ubuntu.com/community/UFW)

    apt-get install ufw
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw allow 22/tcp
    ufw enable

### Change root password

Set root password

    passwd root

The default root password used in the ready to go image is **{@.o7SNf~Qg3-0}#SRCM**. 
Change this to a hard to guess password to make your root account secure.

### Create a new user

Its best practice not to use the root account for normal use. If the root accout is disabled on a publicly facing server or a raspberrypi made available to the web via port forwarding on your router this will immedietly stop a large percentage of automated dictionary attacks, where a script will try and guess your root password to login.

Create a user 

    useradd -m -G sudo,adm -s /bin/bash pi

    passwd pi

    default: (change this for secure installation)
    raspberry 
   
Disable root login via ssh
 
    nano /etc/ssh/sshd_config
   
set 

    PermitRootLogin to no

at the bottom add the lines:

    AllowUsers pi
    AllowGroups adm

### Secure MySQL

Follow guide here: 

[http://dev.mysql.com/doc/refman/5.0/en/mysql-secure-installation.html](http://dev.mysql.com/doc/refman/5.0/en/mysql-secure-installation.html)

### Getting sudo to work with read-only os partition

To get sudo to work I had to re-install sudo with the *--with-timedir* option set to */data/sudo*.

Sudo installation: [http://www.linuxfromscratch.org/blfs/view/cvs/postlfs/sudo.html](http://www.linuxfromscratch.org/blfs/view/cvs/postlfs/sudo.html)

    wget http://www.sudo.ws/sudo/dist/sudo-1.8.8.tar.gz
    tar -zxvf sudo-1.8.8.tar.gz

    ./configure --prefix=/usr                      \
                --libexecdir=/usr/lib/sudo         \
                --docdir=/usr/share/doc/sudo-1.8.8 \
                --with-timedir=/data/sudo       \
                --with-all-insults                 \
                --with-env-editor                  &&
    make

### Add a redirect to launch emoncms

Add redirect in /var/www

    <?php header('Location: ../emoncms'); ?>

    <html><body><h1>Welcome</h1>
    <p><a href="emoncms" >Goto Emoncms</a></p>
    </body></html>
    

Set emoncms to use timestore data directory

Install usefulscripts

Port data across from second raspberrypi or emoncms.org account

### Setting up your main computer to automatically download the latest data from the raspberrypi.


#### Script needs updating to work with v8

This section is to be written next. There are a couple of scripts available in the usefulscripts repository that can automatically download latest data from the raspberrypi to an installation of emoncms on your main computer.

git clone https://github.com/emoncms/usefulscripts/tree/master/replication

