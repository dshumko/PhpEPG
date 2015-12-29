<?php
include "/var/tv24_epg/inc/includas.inc";

$conn = mysql_connect($sqlhost, $mysqluser, $mysqlpass);

//mysql_set_charset('cp1257',$conn);//rusu cp1251, cp866, kio8r
//mysql_set_charset('cp1251',$conn);
mysql_set_charset('utf8',$conn);

mysql_select_db ($dbname,$conn) or die("Nera duombazes ".$dbname);
	
//-------------------  Ishrenka ish eiles visus service ID  ----------------
$sql1 = 'SELECT * FROM `epg_sid`';
$result1 =mysql_query($sql1,$conn);
	
while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
//$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
//	echo"------- TS Stream  ID -------------\n";
echo"------- ".$row1[eit_file]." -------------\n";

		
$fp1 = fopen($row1[eit_file], 'w');	//Atidarom eit faila, pavadinimas ish duombazes "epg_sid" lentos	
//	echo $row1[esid],'\n';
	$vers = $row1[version]; // versija ish duombazes
	$verss = $vers+1;
	if ($verss == 32) $verss = 0;
	$sqlu = 'UPDATE epg_sid SET epg_sid.version = '.$verss.' WHERE epg_sid.esid = ('.$row1[esid].')';// Atnaujinam versija duombazej
	mysql_query($sqlu,$conn);
	$vers<<=1;
	$versijj = $vers + 193;//buvo 193
//	$pasksecnr = passeknr($row1[esid],$conn);
	$sek_nr_all = 0;
	
	
//-------------------  Ishrenka esamo sido  ish eiles visus pid'us  -------------
$sql2 = 'SELECT epg_kan.tv24_pid AS pidas, epg_kan.esid AS sidas, epg_kan.pid FROM `epg_kan` WHERE esid = '.$row1[esid].' ORDER BY pidas;';

//$sql2 = 'SELECT epg_kan.pid AS pidas, epg_kan.esid AS sidas, epg_kan.kalba FROM `epg_kan` WHERE esid = '.$row1[esid].' ORDER BY pidas;';

$result2 = mysql_query($sql2, $conn);

	
	while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
//		$sek_nr = 2;
		$sek_nr = 0;
		$sek_nr_cont = 0;
	//---------165 val. =  7  dienos i prieki----
		unset($eit);
	$ivy_nr = 0;
	
//$sek_nr_arr = passeknr($row2[pidas], $row1[esid],$conn,$sek_nr_cont);// paskaichiuoja paskutines sekcijos numeriuka
	
	
	
	
	for ($i=0; $i<168; $i = $i + 3){
	//***** Sekcijom po 3 valandas
	$pip = $i + 3;
	
	$ltabid = tableid($sek_nr_cont);// suranda lentu eilishkuma
	
//$time_start = microtime(true);
	
	$sek_nr_arr = passeknr($row2[pidas], $row1[esid],$conn,$sek_nr_cont);// paskaichiuoja paskutines sekcijos numeriuka
	
//$time_end = microtime(true);
//$time = $time_end - $time_start;
//$timemin = $time /60;
//echo "\n * Nr. $i Did script in $time seconds or in $timemin minutes\n";	
	
	
	
	$pasksecnr = $sek_nr_arr[0];
	$lastabid = $sek_nr_arr[1];
	
	if ($sek_nr == 256) $sek_nr = 0;

//----------------------------------------  Segmentas ----------------------------  
//---------------  Ishrenka kiekvieno pid informacija 3 val. laiko tarpe ( segmentas ) -------------------------	
	$sql3 = 'SELECT epg_sched.pid AS pidas, epg_kan.esid, DATE_FORMAT(epg_sched.estart, \'%Y%m%d%H%i%S\') as startas, TIME_FORMAT(epg_sched.estop, \'%H%i%s\') AS stopas ,
		trim( epg_sched.title ) AS title , ltrim( epg_sched.edesc ) AS edesc, epg_sched.edesc_lang, epg_sched.title_lang, trim( epg_sched.tit_1) AS tit_1, 
		trim( epg_sched.tit_2) AS tit_2, trim( epg_sched.tit_3) AS tit_3, trim( epg_sched.tit_4) AS tit_4, ltrim( epg_sched.edes_1 ) AS edes_1, 
		ltrim( epg_sched.edes_2 ) AS edes_2, ltrim( epg_sched.edes_3 ) AS edes_3, ltrim( epg_sched.edes_4 ) AS edes_4, epg_sched.edes_lan_1, epg_sched.tit_lan_1, 
		epg_sched.edes_lan_2, epg_sched.tit_lan_2, epg_sched.edes_lan_3, epg_sched.tit_lan_3, epg_sched.edes_lan_4, epg_sched.tit_lan_4
		FROM epg_sched, epg_kan WHERE epg_kan.tv24_pid = epg_sched.pid AND epg_sched.estart >= (SELECT TIMESTAMPADD(HOUR,"'.$i.'",CURDATE()))
		AND epg_sched.estart < (SELECT TIMESTAMPADD(HOUR,"'.$pip.'",CURDATE())) AND epg_sched.pid = '.$row2[pidas].' ORDER BY startas;';   
		$result3 =mysql_query($sql3,$conn);

			unset($eventall);
			unset($descall);
			unset($ben_sek_il);
//------------------------------  Sukam ivykiu rata (events)  ---------------------------------------		
		while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {
//--------------------  Nuo chia darom ivyki (event) ----------------------------------------------

		unset($shdesc); 
		unset($exdesc); 
		unset($muldesc);
		unset($event); 
		unset($desc_len);

$lang_id = 'ISO_8859-2';
$char_kod = '0A';
		
	if ($row3['title_lang'] == 'rus' OR $row3['title_lang'] == 'ukr' OR $row3['title_lang'] == 'xxx') {
		$lang_id = 'ISO_8859-5';
		$char_kod = '01';
		$row3['edesc'] = str_replace(chr(14844051), "-", $row3['edesc']);
			}
	if ($row3['title_lang'] == 'lit' OR $row3['title_lang'] == 'lav' OR $row3['title_lang'] == 'est' OR $row3['title_lang'] == 'pol') { 
		$lang_id = 'ISO_8859-13';//
		$char_kod = '09';
		$row3['edesc'] = str_replace(chr(14844051), "-", $row3['edesc']);
			}
	if ($row3['title_lang'] == 'spa' OR $row3['title_lang'] == 'ita' OR $row3['title_lang'] == 'ger') { 
		$lang_id = 'ISO_8859-1';
		$char_kod = '0A';
		$row3['edesc'] = str_replace(chr(14844051), "-", $row3['edesc']);
			}
			
//echo $lang_id."--";
		
$row3['title'] = mb_convert_encoding($row3['title'], $lang_id, "UTF-8");//
$row3['edesc'] = mb_convert_encoding($row3['edesc'], $lang_id, "UTF-8");//
//echo $row3['title'] ."**".bin2hex ($row3['title'])."----" ;
//		$row3['title'] = trim($row3['title']);// 
//		$row3['edesc'] = trim($row3['edesc']);// 

		$titdesc_len = strlen($row3['title'].$row3['edesc']);
//echo "Ash chia \n\n";
		
//-----------------------  Ziurim ar uzteks short desc ar dedam extended ---------------		
			if ($titdesc_len <= 248){
//--------------------  Short event descriptor --------------------------------------------------------

		$shdesc= pack("H*", "4D");// short event descriptoriaus zyme
		$shdesc.= @tohex($titdesc_len + 7, 2);// + 5 = 2 baitai char setai ir 6 baitai kalba
		$shdesc.= $row3['edesc_lang'];
		$shdesc.= tohex(strlen($row3['title']) + 1, 2);// 1 kad pailginti title ilgi
		$shdesc.= pack("H*", $char_kod);// Ikisham charset kodo simboli
		$shdesc.= $row3['title'];
		$shdesc.= tohex(strlen($row3['edesc']) + 1, 2);//  1 kad pailginti decription ilgi
		$shdesc.= pack("H*", $char_kod);// Ikisham charset kodo simboli
		$shdesc.= $row3['edesc'];
			}
//--------------------  Extented event descriptor --------------------------------------------------------
			else{	
//----------------------  Idedam short desc titla  --------------------------
		$shdesc= pack("H*", "4D");// short event descriptoriaus zyme
		$shdesc.= @tohex(strlen($row3['title']) + 6, 2);// + 5 = 2 baitai char setai ir 3 baitai kalba
		$shdesc.= $row3['edesc_lang'];
		$shdesc.= tohex(strlen($row3['title']) + 1, 2);// 1 kad pailginti title ilgi
		$shdesc.= pack("H*", $char_kod);// Ikisham charset kodo simboli
		$shdesc.= $row3['title'];
		$exdesc.= pack("H*", "00");// Tekstas eina extendet !!!
		
		$exdesc_nr = 0;
		$pask_exdesc_nr = paskexdescnr(strlen($row3['edesc']));
		$ex_tx = $row3['edesc'];
		$exdesc_len = strlen($ex_tx);
		
//------------------------ Extendet tekstas --------------------------------------				
				for ($ia = $exdesc_len; $ia > 0; $ia = $ia - 246){
				$ex_tx_su = substr($ex_tx, 0, 246);
				$ex_tx = substr_replace($ex_tx, '', 0, 246);
		
		$exdesc.= pack("H*", "4E");// extended event descriptoriaus zyme
		$ex_len = strlen($ex_tx_su);
		$exdesc.= @tohex(($ex_len + 7), 2);// deskripshino ilgis + kiek reikia 
		$exdesc.= @tohex(($exdesc_nr * 16) + $pask_exdesc_nr, 2);
		$exdesc.= $row3['edesc_lang'];
		$exdesc.= pack("H*", "00");//  item_description_length
		$exdesc.= @tohex($ex_len + 1, 2);// deskripshino teksto ilgis + 1 char set
		$exdesc.= pack("H*", $char_kod);// Ikisham charset kodo simboli
		$exdesc.= $ex_tx_su;// dedam teksta
		$exdesc_nr += 1;
		
				}// baigem FOR
			} //baigem ELSE

//--------------------- Jei yra kalbu kviechiam multi_lang funkcija ------------------------------
			
			if ($row3['tit_1']){
				$muldesc.= multlan($row3['tit_1'], $row3['tit_lan_1'], $row3['edes_1'], $row3['edes_lan_1']); }
			if ($row3['tit_2']){
				$muldesc.= multlan($row3['tit_2'], $row3['tit_lan_2'], $row3['edes_2'], $row3['edes_lan_2']); }
			if ($row3['tit_3']){
				$muldesc.= multlan($row3['tit_3'], $row3['tit_lan_3'], $row3['edes_3'], $row3['edes_lan_3']); }
			if ($row3['tit_4']){
				$muldesc.= multlan($row3['tit_4'], $row3['tit_lan_4'], $row3['edes_4'], $row3['edes_lan_4']); }
										
			
		
//		echo"-------------  Ivykis + description ".$ivy_nr." --Pidas ".$row2[pidas]." ----\n";
//-------------------------------------------------------------------------------------------------
//-----------------------------  Event ------------------------------------------------------------

		$desc_len = strlen($shdesc.$exdesc.$muldesc);
		$ben_sek_il = $ben_sek_il + $desc_len +  12;
		if($ben_sek_il + 14 > 4000)break;
		
		$event = tohex($ivy_nr, 4);//Event nr.
		$event.= mjd($row3['startas']);
		$event.= pack("H*", $row3['stopas']);

		$event.= tohex($desc_len, 4);// Visu descripshinu ilgis!!!!!!!!!!!!!!!!!!!!!!!!!!!!

		$eventall.= $event;
		$eventall.= $shdesc;
		$eventall.= $exdesc;
		$eventall.= $muldesc;

			$ivy_nr++;
			
//				echo $ivy_nr."ivykis \n";
			}
/////////////////////////// Baigiam ivykiu rata

			if(mysql_num_rows($result3)){
			
//------------------------------------  Nuo chia eina EIT sekcijos aprashas --------------------------------------------
		$eit= tohex($row2[pid], 4);//Programos ID kliento transliuojamos programos
		$eit.= tohex($versijj, 2);//versija, curent next	
		$eit.= tohex($sek_nr, 2);//Sekcijos Nr.
		$eit.= tohex($pasksecnr, 2);//Paskutines sekcijos Nr.
		$eit.= tohex($row1[esid], 4);//Transport stream ID
		$eit.= pack("H*", "0457");//Originalus tinklo ID
		$eit.= tohex($sek_nr, 2);//Segmeno paskutines sekcijos numeris !!!!!!!!!!kolkas nera,  ideta sekcijos numeris
		$eit.= pack("H*", $lastabid);//Paskutines lentos ID 
		$sekilg = strlen($eit.$eventall.$descall);//Sekcijos ilgis
		//$sekilg = strlen($eit.$eventall);//Sekcijos ilgis
		
//echo "Sek ilgis - ".$sekilg."\n";
		$sekilg = $sekilg + 61444;//  61444 reikalingas bitu prastumimui
		$sekilg = tohex($sekilg, 4);
		$tabid = pack("H*", $ltabid);// Lentos ID
		$creit = $tabid.$sekilg.$eit.$eventall.$descall;
		//$creit = $tabid.$sekilg.$eit.$eventall;
		$creit = bin2hex($creit);
		$crc = pack("H*",crc32mpeg($creit));//CRC mpeg2
		$eit = $tabid.$sekilg.$eit;
		fwrite($fp1, pack("H*", "00"));// prieki sekcijos du 00
		fwrite($fp1, $eit);
		fwrite($fp1, $eventall);
		fwrite($fp1, $descall);
		fwrite($fp1, $crc);
//echo "----- Sekcija Nr. ".$sek_nr." ------ \n";
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