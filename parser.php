<?php
include "/var/epg/inc/includas.inc";


$xmlUrl = "/var/epg/xmltv.xml"; // XML feed file/URL
$xmlStr = file_get_contents($xmlUrl);
//------ taisom klaidas simbolius blogus -------
$xmlStr = str_replace('&apos;', "\'", $xmlStr);
//$xmlStr = str_replace(';', ",", $xmlStr);
$xmlObj = simplexml_load_string($xmlStr);
$arrxml = objectsIntoArray($xmlObj);

$conn = mysql_connect($sqlhost, $mysqluser, $mysqlpass);
mysql_set_charset('cp1257',$conn);
mysql_select_db ($dbname, $conn)
    or die("Nera dumbazes ".$dbname);
	
	$trunk = "TRUNCATE TABLE `epg_sched`";
	mysql_query($trunk,$conn);
	sleep(1);

foreach($arrxml["programme"] as $a =>$value)
{

//$konvert = $value["title"];
$konvert = mb_convert_encoding($value["title"], "ISO_8859-13", "UTF-8");// keichiam kodavima
$konvert_or = $konvert;
if (strlen($konvert) > 30) 
	{
	$pos = strpos($konvert, ". ", 10);
		if($pos > 10 AND $pos <= 30) {
		$konvert = substr($konvert, 0, $pos+1);
	
			}	
			elseif($pos == FALSE OR $pos >30) {
			$pos = strpos($konvert, " ", 12);
			$konvert = substr($konvert, 0, $pos+1);
			$konvert = $konvert."...";
		}
	}
//--------------- laidos trukme -----------------------
$start = substr($value['@attributes']['start'], 0, 14);
$startik = substr($value['@attributes']['start'], 0, 14);
$laikjuos = substr($value['@attributes']['start'], 16, 2);
$stop = substr($value['@attributes']['stop'], 0, 14);
$start = strtotime($start);
$stop = strtotime($stop);
$tim_diff = $stop - $start;
$laik_tar = strftime("%H%M%S", $tim_diff + 79200);	


$insert2 = "INSERT INTO epg_sched (pid, estart, estop, title, edesc) VALUES ('".$value['@attributes']['channel']."', SUBTIME('".$startik."', '".$laikjuos.":00:00'), '$laik_tar', TRIM('$konvert'), TRIM('$konvert_or'));";


//$insert2 = "INSERT INTO epg_sched (pid, estart, estop, title, edesc) VALUES ('".$value['@attributes']['channel']."', '".substr($value['@attributes']['start'], 0, 14)."', '$laik_tar', TRIM('$konvert'), TRIM('$konvert_or'));";

		$err =mysql_query($insert2,$conn);	
}
	
//print_r($value);
	
mysql_close($conn);

?>