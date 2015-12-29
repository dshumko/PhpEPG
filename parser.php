<?php
include "/var/tv24_epg/inc/includas.inc";

function tikilg($ilgi) {
	$ilgu = mb_strlen($ilgi, "UTF-8");
    return $ilgu;
}

$time_start = microtime(true);

//

//$xmlUrl = "/var/epg/xmltv.xml"; // XML feed file/URL
$xmlStr = file_get_contents("http://username:password@sync.xprsdata.com/tv_data_v1.php?dump");
//

//print_r($xmlStr);

//------ taisom klaidas simbolius blogus -------
$xmlStr = str_replace('&apos;', "\'", $xmlStr);
//$xmlStr = str_replace(';', ",", $xmlStr);//&
$xmlStr = str_replace("&amp", "and ", $xmlStr);
$xmlStr = str_replace("&quot", "", $xmlStr);
//$xmlStr = str_replace(chr(hexdec('E28098')), chr(hexdec('E28099')), $xmlStr);// rusishkos I ir J
$arrxml =  xml2array($xmlStr, 1, 'attribute');

//print_r($xmlStr);
//echo "\n----------------------------------------------- \n";
//print_r($arrxml);

//$skait = 0;


//$file = 'people.txt';
//file_put_contents($file, $xmlStr);


//print_r($xmlObj);
//print_r($arrxml);


$conn = mysql_connect($sqlhost, $mysqluser, $mysqlpass);
//mysql_set_charset('cp1257',$conn);
mysql_set_charset('utf8',$conn);
mysql_select_db ($dbname, $conn)
    or die("Nera duombazes ".$dbname);
	
	$trunk = "TRUNCATE TABLE `epg_sched`";
	mysql_query($trunk,$conn);
	sleep(1);

//foreach($arrxml["event"] as $a =>$value)
foreach($arrxml["schedule"]["event"] as $a =>$value)
{
//print_r($value);
//echo "-------------------------------------------------------------------\r";
//echo $value['channel_id']["value"]."\n";

//$konvert = $value["title"];
//$konvert_title = mb_convert_encoding($value["title"]["value"], "ISO_8859-13", "UTF-8");// keichiam kodavima
//$konvert_desc = mb_convert_encoding($value["description"]["value"], "ISO_8859-13", "UTF-8");// keichiam kodavima

//echo $konvert_title."\r";

//$konvert_or = $konvert;



//--------------- laidos trukme -----------------------
//$start = substr($value['@attributes']['start'], 0, 14);
$start = date("YmdHis", $value["unix_start"]["value"]);
//$startik = substr($value['@attributes']['start'], 0, 14);
$startik = date("YmdHis", $value["unix_start"]["value"]);

//$laikjuos = $arrxml["schedule"]["timezone"];
$laikjuos = 03;///////////////////////////////////////////////////// Laiko juosta reikia imti ish Shedule
//$stop = substr($value['@attributes']['stop'], 0, 14);
$stop = date("YmdHis", $value["unix_stop"]["value"]);
//$start = strtotime($start);
//$stop = strtotime($stop);
//$tim_diff = $stop - $start;
//$laik_tar = strftime("%H%M%S", $tim_diff + 79200);	
$laik_tar = date("His", ($value["unix_stop"]["value"] - $value["unix_start"]["value"]) + 79200);

//echo $laik_tar."\r";
//echo $start."\r";
//echo $stop."\r";

if (array_key_exists('0', $value["title"])) {

$tit[0] = NULL;
$tit[1] = NULL;
$tit[2] = NULL;
$tit[3] = NULL;
$tit[4] = NULL;
$tit_lan[0] = NULL;
$tit_lan[1] = NULL;
$tit_lan[2] = NULL;
$tit_lan[3] = NULL;
$tit_lan[4] = NULL;
$edes[0] = NULL;
$edes[1] = NULL;
$edes[2] = NULL;
$edes[3] = NULL;
$edes[4] = NULL;
$edes_lan[0] = NULL;
$edes_lan[1] = NULL;
$edes_lan[2] = NULL;
$edes_lan[3] = NULL;
$edes_lan[4] = NULL;

//--------------------------------  Darom daugekalbius masyvus  ----------------------
// ------------------ Reikia paskaichiuoti aprashu ilgi, ir jei reikia juos suvienodinti -----------

foreach($value["title"] as $b =>$value_t){
		$tit[$b] = $value_t["value"];
		$tit_lan[$b] = $value_t["attr"]["lang"];
		$edes[$b] = $value["description"][$b]["value"];
		$edes[$b] = $value["description"][$b]["value"];
		$edes_lan[$b] = $value["description"][$b]["attr"]["lang"];

			}

		$ilgiau = max(array_map('tikilg', $edes));
foreach($edes as $bc =>$value_bc){
		$edilg = mb_strlen($value_bc, "UTF-8");
		$ilg = $ilgiau - $edilg;
	if($edilg < $ilgiau AND $edilg != 0){
		$intarpas = str_repeat(" ", $ilg);//** Uzpildas ***
		$edes[$bc] = $edes[$bc].$intarpas;
			}
		}
		
$insert2 = "INSERT INTO epg_sched (pid, estart, estop, title, title_lang, edesc, edesc_lang, tit_1, tit_lan_1, edes_1, edes_lan_1,
 tit_2, tit_lan_2, edes_2, edes_lan_2, tit_3, tit_lan_3, edes_3, edes_lan_3, tit_4, tit_lan_4, edes_4, edes_lan_4) VALUES
 ('".$value['channel_id']["value"]."', SUBTIME('".$startik."', '".$laikjuos.":00:00'), '$laik_tar', TRIM('$tit[0]'),'".$tit_lan[0]."',
 LTRIM('$edes[0]'), '".$edes_lan[0]."', TRIM('$tit[1]'),'".$tit_lan[1]."', LTRIM('$edes[1]'), '".$edes_lan[1]."', TRIM('$tit[2]'),
 '".$tit_lan[2]."', LTRIM('$edes[2]'), '".$edes_lan[2]."', TRIM('$tit[3]'),'".$tit_lan[3]."', LTRIM('$edes[3]'), '".$edes_lan[3]."',
 TRIM('$tit[4]'),'".$tit_lan[4]."', LTRIM('$edes[4]'), '".$edes_lan[4]."');";
	$err =mysql_query($insert2,$conn);		

	
}else{

	
	$title_lang = $value["title"]["attr"]["lang"];	
	$descript_lang = $value["description"]["attr"]["lang"];	
		
		
	$titlas = $value["title"]["value"];	
	$descripshinas = $value["description"]["value"];

//echo $title_lang." - pavadinimo kalba\n";
//echo $titlas."\n\r";
//echo $descript_lang." - aprashymo kalba\n";
//echo $descripshinas."\n\r";
	

$insert2 = "INSERT INTO epg_sched (pid, estart, estop, title, title_lang, edesc, edesc_lang) VALUES ('".$value['channel_id']["value"]."',
 SUBTIME('".$startik."', '".$laikjuos.":00:00'), '$laik_tar', TRIM('$titlas'),'".$title_lang."', TRIM('$descripshinas'), '".$descript_lang."');";
		$err =mysql_query($insert2,$conn);	

}


//echo "-------------------------------------------------------------------\r";		
}
	
//print_r($value);

$time_end = microtime(true);
$time = $time_end - $time_start;
$timemin = $time /60;



//fclose($file);	
mysql_close($conn);

$procoid = posix_getpid();
//echo "$procoid Baigem shita reikala \n";
echo "\n * Did script in $time seconds or in $timemin minutes\n";
posix_kill ($procoid , SIGTERM);




?>