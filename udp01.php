<?php

//include "/var/epg/inc/includas.inc";

$socket = stream_socket_server("udp://192.168.2.110:65221", $errno, $errstr, STREAM_SERVER_BIND);
if (!$socket) {
    echo "shudas!!!";
    die("$errstr ($errno)");
}
  $per = 0;
  echo "grudas!!!"; 
  

$failas = fopen("/var/epg/eit_01.ts", "rb");

rewind($failas);


  $per = 1;
do {
    
 //   $per = $per + 1;
	$per = 0;

	$paketas = fread($failas, 188);//940
//	if (feof($failas) == true){
//	rewind($failas);
//	}	
//	$paketas .= fread($failas, 188);//940
//	if (feof($failas) == true){
//	rewind($failas);
//	}
//	$paketas .= fread($failas, 188);//940
//	if (feof($failas) == true){
//	rewind($failas);
//	}
//	$paketas .= fread($failas, 188);//940
//	if (feof($failas) == true){
//	rewind($failas);
//	}
//	$paketas .= fread($failas, 188);//940
//	if (feof($failas) == true){
//	rewind($failas);
//	}	
	
	
	
	usleep(32000);//30000
    stream_socket_sendto($socket, $paketas, 0, "239.1.3.1:1234");//239.1.1.9:1234
	if (feof($failas) == true){
	rewind($failas);
	}

} while ($per < 100);


fclose($failas);
?>
