<?php
include "/var/tv24_epg/inc/includas.inc";

	$connf = mysql_connect($sqlhost, $mysqluser, $mysqlpass);
	mysql_set_charset('cp1257',$connf);
	mysql_select_db ($dbname,$connf) or die("Nera duombazes ".$dbname);


	
//$sql1 = 'SELECT * FROM `epg_sid`';
//$result1 =mysql_query($sql1,$conn);
	
//while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {

//echo"------- ".$row1[eit_file]." -------------\n";	
	
	
	
	$sqlin = 'UPDATE configas SET cnt = 0';
	mysql_query($sqlin,$connf);

$feit = fopen('/var/tv24_epg/eit_04.eit', "r");
$fts = fopen('/var/tv24_epg/eit_04.ts', 'w');	
$cnt = 0;
$connf = array($connf, $fts);
//fseek($feit, 2, SEEK_CUR);
//$data = fread($feit, 1);
while (ftell($feit) < filesize('/var/tv24_epg/eit_04.eit')) {

$pirmdu = fread($feit, 2);
$sekilgis = fread($feit, 2);
$ilgis = hexdec(bin2hex($sekilgis)) - 61440;


$hexilgis = unpack("H*", $sekilgis);
//echo "Desimtainis ilgis - ".$ilgis." HEX ilgis - ".$hexilgis[1]."\n";

$kartoti = floor($ilgis / 184);
$likutis = $ilgis - (floor($ilgis / 184) * 184);
$ffff = 184 - ($ilgis - (floor($ilgis / 184) * 184));
fseek($feit, -4, SEEK_CUR);
//for ($i=0; $i<$kartoti; $i++){

	$sekcija = fread($feit, $ilgis + 4);
		if (!$sekcija) {
		die('');
	
}

	//echo bin2hex($pirmdu.$sekilgis.$sekcija)."\n----------------------------------------------\n";
	
	$sekarr = str_split($sekcija, 184);
	//$sekarr = str_split($pirmdu.$sekilgis.$sekcija, 368);
//	print_r($sekarr);


	array_walk($sekarr, 'ffffunk', $connf);

}


function ffffunk(&$item1, $key, $connf)
{
	$sqlcnt = 'SELECT * FROM `configas`';
	$result1 =mysql_query($sqlcnt,$connf[0]);
	$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
	
	$cnt = $row1['cnt'];
	$eilil = strlen($item1);
	if($eilil < 184){
		$skirt = 184 - $eilil;
		for ($i=0; $i<$skirt; $i++){
		 $item1.=pack("H*","FF");}
		 //$item1.="F";}
	}
		if($key == 0){
		$head=pack("H*","4740121".dechex($cnt));
		//$head="4740121".dechex($cnt);
		}
		if($key > 0){
		$head=pack("H*","4700121".dechex($cnt));
		//$head="4700121".dechex($cnt);
		}
		if ($cnt == 15) {
		$cnt = 0;
		} else {
		$cnt++;
		}
		$sqlu = 'UPDATE configas SET cnt = '.$cnt.'';
		mysql_query($sqlu,$connf[0]);
		//   ----  Rashom i faila  ----
		
		fwrite ($connf[1], $head.$item1);
//    echo "\n".bin2hex($head.$item1);
}


fclose($feit);
fclose($fts);
//}
@mysql_close($connf);
//exec ("php /var/epg/pack03.php");
?>