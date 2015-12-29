<?php

$sqlhost = "localhost";
$sqlport = "3306";
$dbname = "epg";
$mysqluser = "root";
$mysqlpass = "1234";

function mjd($datlaik) {
	settype($datlaik, "string");
	$met = substr($datlaik, 0, 4);
	$men = substr($datlaik, 4, 2);
	$dien = substr($datlaik, 6, 2);
	$laik = substr($datlaik, 8, 6);
	$jd = GregorianToJD($men, $dien, $met);
	$jd = $jd - 2400000.5;
	$mjdt = pack("H*", dechex($jd).$laik);
    return $mjdt;
}



function passeknr($pidas, $sidd, $conn, $sek_nr_cont) {
//$sql2 = 'SELECT epg_kan.pid AS pidas, SELECT epg_kan.pid AS pidas, epg_kan.esid AS sidas, epg_kan.kalba FROM `epg_kan` WHERE esid = '.$sidd.' ORDER BY //pidas; AS sidas, epg_kan.kalba FROM `epg_kan` WHERE esid = '.$sidd.' ORDER BY pidas;';
//$result2 = mysql_query($sql2, $conn);

//	while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
//		$sek_nr = 2;
//		$sek_nr = 0;
//		$sek_nr_cont = 0;
//---------165 val. =  7  dienos i prieki ----
//		unset($eit);
	$ivy_nr = 0;
	for ($i=0; $i<168; $i = $i + 3){
	$pip = $i + 3;
	
//	$ltabid = tableid($sek_nr_cont);

//	echo"------------------------------------  Segmentas ---------------------\n";	    
//----------------------  Ishrenka kiekvieno pid informacija 3 val. laiko tarpe ( segmentas ) -------------------------	
		$sql3 = 'SELECT epg_sched.pid AS pidas, epg_kan.esid, DATE_FORMAT(epg_sched.estart, \'%Y%m%d%H%i%S\') as startas, epg_sched.estop AS stopas , trim( epg_sched.title ) as title , trim( epg_sched.edesc ) as edesc, epg_kan.kalba FROM epg_sched, epg_kan WHERE epg_kan.pid = epg_sched.pid AND epg_sched.estart >= (SELECT TIMESTAMPADD(HOUR,"'.$i.'",CURDATE())) AND epg_sched.estart < (SELECT TIMESTAMPADD(HOUR,"'.$pip.'",CURDATE())) AND epg_sched.pid = '.$pidas.' AND epg_kan.esid = '.$sidd.' ORDER BY pidas, startas;
';   
		$result3 =mysql_query($sql3,$conn);


		while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {

			}
			if(mysql_num_rows($result3)){

		$sek_nr += 8;
//		$sek_nr_cont += 8;
		}
			
		}
		
//	}
$lastabid = tableid($sek_nr);

//echo $sek_nr."- viso paskutine sekcija \n";
//echo $sek_nr_cont."- besitesenti sekcija \n";
if ($sek_nr_cont <= 248) {
	if($sek_nr >= 256) $sek_nr = 248;
    else $sek_nr = $sek_nr;
} elseif ($sek_nr_cont >= 256 AND $sek_nr_cont <= 512) {
	if($sek_nr >= 512) $sek_nr = 248;
    else $sek_nr = $sek_nr - (256 + 8);
} elseif ($sek_nr_cont >= 512 AND $sek_nr_cont <= 768) {
	if($sek_nr >= 768) $sek_nr = 248;
    else $sek_nr = $sek_nr - (512 + 8);
} elseif ($sek_nr_cont >= 768 AND $sek_nr_cont <= 1024) {
 	if($sek_nr >= 1024) $sek_nr = 248;
    else $sek_nr = $sek_nr - (768 + 8);
} elseif ($sek_nr_cont >= 1024 AND $sek_nr_cont <= 1280) {
	if($sek_nr >= 1280) $sek_nr = 248;
    else $sek_nr = $sek_nr - (10240 + 8);
} elseif ($sek_nr_cont >= 1280 AND $sek_nr_cont <= 1536) {
	if($sek_nr >= 1536) $sek_nr = 248;
    else $sek_nr = $sek_nr - (1280 + 8);
} elseif ($sek_nr_cont >= 1536 AND $sek_nr_cont <= 1792) {
	if($sek_nr >= 1792) $sek_nr = 248;
    else $sek_nr = $sek_nr - (1536 + 8);
} elseif ($sek_nr_cont >= 1792 AND $sek_nr_cont <= 2048) {
	if($sek_nr >= 2048) $sek_nr = 248;
    else $sek_nr = $sek_nr - (1792 + 8);
} elseif ($sek_nr_cont >= 2048 AND $sek_nr_cont <= 2304) {
	if($sek_nr >= 2304) $sek_nr = 248;
    else $sek_nr = $sek_nr - (2048 + 8);
} elseif ($sek_nr_cont >= 2304 AND $sek_nr_cont <= 2560) {
	if($sek_nr >= 2560) $sek_nr = 248;
    else $sek_nr = $sek_nr - (2304 + 8);
} elseif ($sek_nr_cont >= 2560 AND $sek_nr_cont <= 2816) {
	if($sek_nr >= 2816) $sek_nr = 248;
    else $sek_nr = $sek_nr - (2560 + 8);
} elseif ($sek_nr_cont >= 2816 AND $sek_nr_cont <= 3072) {
	if($sek_nr >= 3072) $sek_nr = 248;
    else $sek_nr = $sek_nr - (2816 + 8);
} elseif ($sek_nr_cont >= 3072 AND $sek_nr_cont <= 3328) {
	if($sek_nr >= 3328) $sek_nr = 248;
    else $sek_nr = $sek_nr - (3072 + 8);
} elseif ($sek_nr_cont >= 3328 AND $sek_nr_cont <= 3584) {
	if($sek_nr >= 3584) $sek_nr = 248;
    else $sek_nr = $sek_nr - (3328 + 8);
} elseif ($sek_nr_cont >= 3548 AND $sek_nr_cont <= 3840) {
	if($sek_nr >= 3840) $sek_nr = 248;
    else $sek_nr = $sek_nr - (3548 + 8);
} elseif ($sek_nr_cont >= 3840 AND $sek_nr_cont <= 4096) {
	if($sek_nr >= 4096) $sek_nr = 248;
    else $sek_nr = $sek_nr - (3840 + 8);
}

//mysql_free_result($result2);
mysql_free_result($result3);

$sek_nr_arr = array($sek_nr, $lastabid);

return $sek_nr_arr;

}

function tableid($i) {

if ($i < 256) {
    $tbid = 50;
} elseif ($i >= 256 AND $i < 512) {
    $tbid = 51;
} elseif ($i >= 512 AND $i < 768) {
    $tbid = "52";
} elseif ($i >= 768 AND $i < 1024) {
    $tbid = "53";
} elseif ($i >= 1024 AND $i < 1280) {
    $tbid = "54";
} elseif ($i >= 1280 AND $i < 1536) {
    $tbid = "55";
} elseif ($i >= 1536 AND $i < 1792) {
    $tbid = "56";
} elseif ($i >= 1792 AND $i < 2048) {
    $tbid = "57";
} elseif ($i >= 2048 AND $i < 2304) {
    $tbid = "58";
} elseif ($i >= 2304 AND $i < 2560) {
    $tbid = "59";
} elseif ($i >= 2560 AND $i < 2816) {
    $tbid = "5A";
} elseif ($i >= 2816 AND $i < 3072) {
    $tbid = "5B";
} elseif ($i >= 3072 AND $i < 3328) {
    $tbid = "5C";
} elseif ($i >= 3328 AND $i < 3584) {
    $tbid = "5D";
} elseif ($i >= 3548 AND $i < 3840) {
    $tbid = "5E";
} elseif ($i >= 3840 AND $i < 4096) {
    $tbid = "5F";
}
return $tbid;
}

function versija($esid, $conn) {

	$sqlv = "SELECT epg_sid.esid, epg_sid.version  FROM epg_sid  WHERE epg_sid.esid = '.$esid.';";
	$resultv = mysql_query($sqlv,$conn);
	//$vers = mysql_result($resultv, 0, 1);
	$versarr = mysql_fetch_array($resultv, MYSQL_NUM);
	$vers = $versarr[1];
	//$vers<<=1;
	//$verss = $vers + 193;
	mysql_free_result($resultv);

return $verss;
}

function tohex($desim, $baitu) {
	$bin = sprintf('%'.$baitu.'X',$desim);
	str_replace ( " " , 0 , $bin);
    $hexx = @pack("H*", $bin);
    return $hexx;
}

  function hex2bin($str) {
    $bin = "";
    $i = 0;
    do {
        $bin .= chr(hexdec($str{$i}.$str{($i + 1)}));
        $i += 2;
    } while ($i < strlen($str));
    return $bin;
}
//$output = shell_exec('python /var/epg/pycrc/pycrc.py --model=crc-32-mpeg --check-hexstring '.$str);
function crc32mpeg($str) {
$output = shell_exec('python /var/epg/pycrc/pycrc.py --model=crc-32-mpeg --check-hexstring '.$str);
$output = rtrim ($output, "\n");
$output = substr_replace($output, '', 0, 2);
$len = strlen($output);
	for ($len; $len < 8; $len++) {
		$output = '0'.$output;
		}
    return $output;
}

function starttime($str) {

$met = substr($datlai, 0, 4);
$men = substr($datlai, 4, 2);
$dien = substr($datlai, 6, 2);
$laik = substr($datlai, 8, 6);
$jd = GregorianToJD($men, $dien, $met);
$jd = $jd - 2400000.5;
$jd = dechex($jd).$laik;
return $jd;
}

function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();
   
    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }
   
    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}

?>
