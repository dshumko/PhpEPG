<?php

include "/var/epg/inc/includas.inc";

$socket = stream_socket_server("udp://192.168.2.110:65221", $errno, $errstr, STREAM_SERVER_BIND);
if (!$socket) {
    echo "shudas!!!";
    die("$errstr ($errno)");
}
  $per = 0;
  echo "grudas!!!"; 
  

$failas_sched = fopen(__DIR__."/eit_01.ts", "rb");
$failas_now = fopen(__DIR__."/now_01.ts", "rb");

rewind($failas_sched);

$cnt = 0;
$per = 1;
$nuliai = pack("H*","00");
$kem = pack("H*","40");

do {
	$per = 0;
	
  //*************  Grudam now informacija **********************		
  do {


		$paketas = fread($failas_now, 188);//
		if ($paketas == FALSE)break;
		$paketas = substr_replace($paketas, pack("H*","1".dechex($cnt)), 3, 1);
		usleep(30000);//30000
		stream_socket_sendto($socket, $paketas, 0, "239.1.3.1:1234");
		if (feof($failas_now) == true){
		rewind($failas_now);
	}
	if ($cnt == 16) {
		$cnt = 0;
		} else {
		$cnt++; }
			fseek($failas_now, 1, SEEK_CUR);
			$antras = fgets($failas_now, 2);
			fseek($failas_now, -2, SEEK_CUR);
		}while ($kem <> $antras);

//*************  Grudam schedule informacija  **********************		
	for ($i = 1; $i <= 4; $i++) {

		do {
		$paketas = fread($failas_sched, 188);//
		$paketas = substr_replace($paketas, pack("H*","1".dechex($cnt)), 3, 1);
		usleep(30000);//30000
		stream_socket_sendto($socket, $paketas, 0, "239.1.3.1:1234");
		if (feof($failas_sched) == true){
		rewind($failas_sched);
	}
	if ($cnt == 16) {
		$cnt = 0;
		} else {
		$cnt++; }
			fseek($failas_sched, 1, SEEK_CUR);
			$antras = fgets($failas_sched, 2);
			fseek($failas_sched, -2, SEEK_CUR);
		}while ($nuliai <> $antras);
		}
} while ($per < 100);

fclose($failas_sched);
fclose($failas_now);

