<?php
include "/var/epg/inc/includas.inc";

$conn = mysql_connect($sqlhost, $mysqluser, $mysqlpass);
mysql_set_charset('cp1257',$conn);
mysql_select_db ($dbname,$conn) or die("Nera duombazes ".$dbname);
	
//-------------------  Ishrenka ish eiles visus service ID  ----------------
$sql1 = 'SELECT * FROM `epg_sid`';
$result1 =mysql_query($sql1,$conn);
	
while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
//$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
//	echo"------- TS Stream  ID -------------\n";
echo"------- ".$row1[eit_file]." -------------\n";

		
$fp1 = fopen($row1[eit_file], 'w');		
//	echo $row1[esid],'\n';
	$vers = $row1[version];
	$verss = $vers+1;
	if ($verss == 32) $verss = 0;
	$sqlu = 'UPDATE epg_sid SET epg_sid.version = '.$verss.' WHERE epg_sid.esid = ('.$row1[esid].')';
	mysql_query($sqlu,$conn);
	$vers<<=1;
	$versijj = $vers + 193;
//	$pasksecnr = passeknr($row1[esid],$conn);
	$sek_nr_all = 0;
	
	
//-------------------  Ishrenka esamo sido  ish eiles visus pid'us  -------------
$sql2 = 'SELECT epg_kan.pid AS pidas, epg_kan.esid AS sidas, epg_kan.kalba FROM `epg_kan` WHERE esid = '.$row1[esid].' ORDER BY pidas;';
//$sql2 = 'SELECT epg_kan.pid AS pidas, epg_kan.esid AS sidas, epg_kan.kalba FROM `epg_kan` WHERE esid = '.$row1[esid].' ORDER BY pidas;';

$result2 = mysql_query($sql2, $conn);

	
	while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
//		$sek_nr = 2;
		$sek_nr = 0;
		$sek_nr_cont = 0;
	//---------165 val. =  7  dienos i prieki----
		unset($eit);
	$ivy_nr = 0;
	
$sek_nr_arr = passeknr($row2[pidas], $row1[esid],$conn,$sek_nr_cont);	
	
	for ($i=0; $i<168; $i = $i + 3){
	$pip = $i + 3;
	
	$ltabid = tableid($sek_nr_cont);
	
//	$sek_nr_arr = passeknr($row2[pidas], $row1[esid],$conn,$sek_nr_cont);
	$pasksecnr = $sek_nr_arr[0];
	$lastabid = $sek_nr_arr[1];
	
	if ($sek_nr == 256) $sek_nr = 0;

//	echo"------------------------------------  Segmentas ---------------------\n";	    
//----------------------  Ishrenka kiekvieno pid informacija 3 val. laiko tarpe ( segmentas ) -------------------------	
		$sql3 = 'SELECT epg_sched.pid AS pidas, epg_kan.esid, DATE_FORMAT(epg_sched.estart, \'%Y%m%d%H%i%S\') as startas, epg_sched.estop AS stopas , trim( epg_sched.title ) as title , trim( epg_sched.edesc ) as edesc, epg_kan.kalba FROM epg_sched, epg_kan WHERE epg_kan.pid = epg_sched.pid AND epg_sched.estart >= (SELECT TIMESTAMPADD(HOUR,"'.$i.'",CURDATE())) AND epg_sched.estart < (SELECT TIMESTAMPADD(HOUR,"'.$pip.'",CURDATE())) AND epg_sched.pid = '.$row2[pidas].' ORDER BY startas;
';   
		$result3 =mysql_query($sql3,$conn);

			unset($eventall);
			unset($descall);
		while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {
//--------------------  Nuo chia eina ivykio aprashymas  ----------------------------------------------
//-------- pvz -----
		unset($desc);// $a +=5;
		unset($event);// $a <<=5;
		unset($desc_len);// $a .="Add";
		
//		echo"-------------  Ivykis + description ".$ivy_nr." --Pidas ".$row2[pidas]." ----\n";
		$event = tohex($ivy_nr, 4);//Event nr.
		$event.= mjd($row3['startas']);
		$event.= pack("H*", $row3['stopas']);
		$row3['title'] = trim($row3['title']);
		$row3['edesc'] = trim($row3['edesc']);
		$desc_len = strlen($row3['title'].$row3['edesc']);
		$desc_len += 5;
		$desc_len_all = $desc_len + 2;
		$event.= tohex($desc_len_all, 4);// Visu descripshinu ilgis
//		$eventall.= $event;
//--------------------  Short event descriptor --------------------------------------------------------

		$desc= pack("H*", "4D");// short event descriptoriaus zyme
		$desc.= @tohex($desc_len, 2);
		$desc.= $row3['kalba'];
		$desc.= tohex(strlen($row3['title']), 2);
		$desc.= $row3['title'];
		$desc.= tohex(strlen($row3['edesc']), 2);
		$desc.= $row3['edesc'];
//		$desc.= pack("H*", "FFFFFF");
		$descall.= $event;
		$descall.= $desc;
		//echo $desc;

//			echo"-----------------  Ivykis ".$ivy_nr." ------\n";
//			print_r($row3);
				$ivy_nr++;
//				echo $ivy_nr."ivykis \n";
			}
			if(mysql_num_rows($result3)){
			
//		$eit= 	
		$eit= tohex($row2[pidas], 4);//Programos ID	
		$eit.= tohex($versijj, 2);//versija, curent next	
		$eit.= tohex($sek_nr, 2);//Sekcijos Nr.
		$eit.= tohex($pasksecnr, 2);//Paskutines sekcijos Nr.
		$eit.= tohex($row1[esid], 4);//Transport stream ID
		$eit.= pack("H*", "0457");//Originalus tinklo ID
		$eit.= tohex($sek_nr, 2);//Segmeno paskutines sekcijos numeris !!!!!!!!!!kolkas nera  ideta sekcijos numeris
		$eit.= pack("H*", $lastabid);//Paskutines lentos ID 
		$sekilg = strlen($eit.$eventall.$descall);//Sekcijos ilgis
//		echo $sekilg."\n";
		$sekilg = $sekilg + 61444;
		$sekilg = tohex($sekilg, 4);
		$tabid = pack("H*", $ltabid);// Lentos ID
		$creit = $tabid.$sekilg.$eit.$eventall.$descall;
		$creit = bin2hex($creit);
		$crc = pack("H*",crc32mpeg($creit));//CRC mpeg2
		$eit = $tabid.$sekilg.$eit;
		fwrite($fp1, pack("H*", "00"));// prieki sekcijos du 00
		fwrite($fp1, $eit);
		fwrite($fp1, $eventall);
		fwrite($fp1, $descall);
		fwrite($fp1, $crc);
//		echo "----- Sekcija Nr. ".$sek_nr." ------ \n";
		$sek_nr += 8;
		$sek_nr_cont += 8;
		}
			
		}
		
	}

fclose($fp1);
//echo $row1[eit_file]."\n ***";
}
mysql_free_result($result1);
mysql_free_result($result2);
mysql_free_result($result3);
mysql_close($conn);

?>