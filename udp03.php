<?php

include "/var/epg/inc/includas.inc";

//	$connf = mysql_connect($sqlhost, $mysqluser, $mysqlpass);
//	mysql_set_charset('cp1257',$connf);
//	mysql_select_db ($dbname,$connf) or die("Nera duombazes ".$dbname);

$socket = stream_socket_server("udp://192.168.2.110:65223", $errno, $errstr, STREAM_SERVER_BIND);
if (!$socket) {
    echo "shudas!!!";
    die("$errstr ($errno)");
}
  $per = 0;
  echo "grudas!!!"; 
  

$failas_sched = fopen("/var/tv24_epg/eit_03.ts", "rb");
$failas_now = fopen("/var/tv24_epg/now_03.ts", "rb");

//$tsts = fopen('/var/tv24_epg/tststs.ts', 'w');

rewind($failas_sched);

	$cnt = 0;
	$per = 1;
	$nuliai = pack("H*","00");
	$kem = pack("H*","40");
//	$time_start = NULL;

do {
    
 //   $per = $per + 1;
	$per = 0;
	
//	$sqlfailas = 'SELECT * FROM `configas`';
//	$result1 =mysql_query($sqlfailas,$connf);
//	$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
	
//	$failas = $row1['failas'];
	
//******************  Kas minute paleidziam skripta  *********
//	$time_end = microtime(true);
//	$time = $time_end - $time_start;
	
//	if ($time >= 1.0000){
//	exec("/var/tv24_epg/epg_now");
//	$failas_now = fopen("/var/tv24_epg/now_01.ts", "rb");
//	$time_start = NULL;
//	}
//*************  Grudam now informacija **********************		
do {


//if ($failas == 0)break;
		$paketas = fread($failas_now, 188);//
		if ($paketas == FALSE)break;
		$paketas = substr_replace($paketas, pack("H*","1".dechex($cnt)), 3, 1);
		usleep(30000);//30000
		stream_socket_sendto($socket, $paketas, 0, "239.1.3.3:1234");//239.1.1.9:1234
//		fwrite ($tsts, $paketas);
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
//
//*************  Grudam schedule informacija  **********************		
	for ($i = 1; $i <= 4; $i++) {

		do {
		$paketas = fread($failas_sched, 188);//
		$paketas = substr_replace($paketas, pack("H*","1".dechex($cnt)), 3, 1);
		usleep(30000);//30000
		stream_socket_sendto($socket, $paketas, 0, "239.1.3.3:1234");//239.1.1.9:1234
//		fwrite ($tsts, $paketas);
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


//fclose ($tsts);
fclose($failas_sched);
fclose($failas_now);
//@mysql_close($connf);
?>
