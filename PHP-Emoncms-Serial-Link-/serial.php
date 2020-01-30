<?php

function microtime_float()
{
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
}

function post2emoncms($json)
{

        $url = "/emoncms3/api/post?apikey=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx&json=" . $json;
        echo $url;
        echo "\r\n";
        getcontent("127.0.0.1",80,$url);
}

function getcontent($server, $port, $file)
{
   $cont = "";
   $ip = gethostbyname($server);
   $fp = fsockopen($ip, $port);
   if (!$fp)
   {
       return "Unknown";
   }
   else
   {
       $com = "GET $file HTTP/1.1\r\nAccept: */*\r\nAccept-Language: de-ch\r\nAccept-Encoding: gzip, deflate\r\nUser-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)\r\nHost: $server:$port\r\nConnection: Keep-Alive\r\n\r\n";
       fputs($fp, $com);
       while (!feof($fp))
       {
           $cont .= fread($fp, 500);
       }
       fclose($fp);
       $cont = substr($cont, strpos($cont, "\r\n\r\n") + 4);
       return $cont;
   }
}



include "php_serial.class.php";

// Let's start the class
$serial = new phpSerial;
$serial->deviceSet("/dev/ttyUSB0");
$serial->confBaudRate(57600);
$serial->confParity("none");
$serial->confCharacterLength(8);
$serial->confStopBits(1);
$serial->confFlowControl("none");
// We may need to return if nothing happens for 10 seconds
//stream_set_timeout($serial->_dHandle, 10);

// Then we need to open it
//$serial->deviceOpen();
//$serial->sendMessage("210g");
//$serial->sendMessage("7i");
//$serial->sendMessage("8b");

echo ("Started..\r\n");

while(1) 
{
  $serial->deviceOpen();

  // Or to read from
  $read = '';
  $theResult = '';
  $start = microtime_float();

  //1 second limit to read
  while ( ($read == '') && (microtime_float() <= $start + 1)) 
  {
    $read = $serial->readPort();
    if ($read != '') 
    {
      $theResult .= $read;
      $read = '';
    }
  }

  $serial->deviceClose();

  $lines = preg_split( '/\r\n|\r|\n/', $theResult );
  foreach ($lines as $line)
  {
    $valores=explode(" ", $line);
    $count = count($valores);

    if ($count>2 && $valores[0]=='OK')
    {  
      echo $line."\n";
      $binarydata = "";
      for ( $i=2; $i<$count ; $i++) {
        $binarydata.=   str_pad(dechex( $valores[$i] ),2,'0',STR_PAD_LEFT) ;
      }
      $array = unpack("s*", pack("H*" , $binarydata));

      echo "Node id: ".$valores[1]." csv:";
      $csv = "";
      for ( $i=1; $i<=sizeof($array); $i++)
      {
        $csv.=intval($array[$i]);
        if ($i<sizeof($array)) $csv.=",";
      }
      echo $csv."\r\n";
      getcontent("127.0.0.1",80,"/emoncms3/api/post?apikey=xxxxxxxxxxxxxxxxxxxx&node=".$valores[1]."&csv=".$csv);
    }
  }
}

$serial->deviceClose();
?>
