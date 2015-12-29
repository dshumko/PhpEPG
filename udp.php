<?php

include "/var/epg/inc/includas.inc";
//echo "labas";
$socket = stream_socket_server("udp://192.168.2.110:65223", $errno, $errstr, STREAM_SERVER_BIND);
if (!$socket) {
    echo "shudas!!!";
    die("$errstr ($errno)");
}
  $per = 0;
  echo "grudas!!!"; 
  

$failas = fopen("/var/epg/eit_03.ts", "rb");
//$failas = fopen("/var/epg/LNK.ts", "rb");
//echo $failas;

rewind($failas);


  $per = 1;
do {
    
    $per = $per + 1;
    //echo date("D M j H:i:s Y\r\n");
	$paketas = fread($failas, 188);
	usleep(25000);
    stream_socket_sendto($socket, $paketas, 0, "239.1.3.3:1234");//239.1.1.9:1234
	if (feof($failas) == true){
	rewind($failas);
	}

} while ($per < 100000000000000);


fclose($failas);
?>
