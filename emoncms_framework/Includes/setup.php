   
<?php
/*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
*/

 db_query(
        "CREATE TABLE users
        (
        id int NOT NULL AUTO_INCREMENT, 
        PRIMARY KEY(id),
        username varchar(30),
        password varchar(64),
        salt varchar(3),
        apikey varchar(64)
      )");


?>
