<?php

$sqlhost = "localhost";
$sqlport = "3306";
$dbname = "tv24_epg";
$mysqluser = "root";
$mysqlpass = "root";

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

function multlan($tit, $tit_lan, $edes, $edes_lan){

	unset($shdesc); 
	unset($exdesc); 
$lang_id = 'ISO_8859-2';
$char_kod = '0A';
		
	if ($tit_lan == 'rus' OR $tit_lan == 'ukr' OR $tit_lan == 'xxx') {
		$lang_id = 'ISO_8859-5';
		$char_kod = '01';
		$edes = str_replace(chr(14844051), "-", $edes);
			}
	if ($tit_lan == 'lit' OR $tit_lan == 'lav' OR $tit_lan == 'est' OR $tit_lan == 'pol') { 
		$lang_id = 'ISO_8859-13';//
		$char_kod = '09';
		$edes = str_replace(chr(14844051), "-", $edes);
			}
	if ($tit_lan == 'spa' OR $tit_lan == 'ita' OR $tit_lan == 'ger') { 
		$lang_id = 'ISO_8859-1';
		$char_kod = '0A';
		$edes = str_replace(chr(14844051), "-", $edes);
			}
		
$tit = mb_convert_encoding($tit, $lang_id, "UTF-8");//
$edes = mb_convert_encoding($edes, $lang_id, "UTF-8");//
//echo $row3['title'] ."**".bin2hex ($row3['title'])."----" ;
//		$row3['title'] = trim($row3['title']);// 
		$row3['edesc'] = ltrim($edes);// 
//echo strlen($row3['edesc']);
//echo "\n";

		$titdesc_len = strlen($tit.$edes);

//-----------------------  Ziurim ar uzteks short desc ar dedam extended ---------------		
			if ($titdesc_len <= 248){
//--------------------  Short event descriptor --------------------------------------------------------

		$shdesc= pack("H*", "4D");// short event descriptoriaus zyme
		$shdesc.= @tohex($titdesc_len + 7, 2);// + 5 = 2 baitai char setai ir 6 baitai kalba
		$shdesc.= $tit_lan;
		$shdesc.= tohex(strlen($tit) + 1, 2);// 1 kad pailginti title ilgi
		$shdesc.= pack("H*", $char_kod);// Ikisham charset kodo simboli
		$shdesc.= $tit;
		$shdesc.= tohex(strlen($edes) + 1, 2);//  1 kad pailginti decription ilgi
		$shdesc.= pack("H*", $char_kod);// Ikisham charset kodo simboli
		$shdesc.= $edes;
			}
//--------------------  Extented event descriptor --------------------------------------------------------
			else{	
//----------------------  Idedam short desc titla  --------------------------
		$shdesc= pack("H*", "4D");// short event descriptoriaus zyme
		$shdesc.= @tohex(strlen($tit) + 6, 2);// + 5 = 2 baitai char setai ir 3 baitai kalba
		$shdesc.= $tit_lan;
		$shdesc.= tohex(strlen($tit) + 1, 2);// 1 kad pailginti title ilgi
		$shdesc.= pack("H*", $char_kod);// Ikisham charset kodo simboli
		$shdesc.= $tit;
		$exdesc.= pack("H*", "00");// Tekstas eina extendet !!!
		
		$exdesc_nr = 0;
		$pask_exdesc_nr = paskexdescnr(strlen($edes));
		$ex_tx = $edes;
		$exdesc_len = strlen($ex_tx);
		
//------------------------ Extendet tekstas --------------------------------------				
				for ($ia = $exdesc_len; $ia > 0; $ia = $ia - 246){
				$ex_tx_su = substr($ex_tx, 0, 246);
				$ex_tx = substr_replace($ex_tx, '', 0, 246);
		
		$exdesc.= pack("H*", "4E");// extended event descriptoriaus zyme
		$ex_len = strlen($ex_tx_su);
		$exdesc.= @tohex(($ex_len + 7), 2);// deskripshino ilgis + kiek reikia 
		$exdesc.= @tohex(($exdesc_nr * 16) + $pask_exdesc_nr, 2);
		$exdesc.= $edes_lan;
		$exdesc.= pack("H*", "00");//  item_description_length
		$exdesc.= @tohex($ex_len + 1, 2);// deskripshino teksto ilgis + 1 char set
		$exdesc.= pack("H*", $char_kod);// Ikisham charset kodo simboli
		$exdesc.= $ex_tx_su;// dedam teksta
		$exdesc_nr += 1;
		
				}// baigem FOR
			} //baigem ELSE

	return $shdesc.$exdesc;
}
//******************************************************************************************************

function passeknr($pidas, $sidd, $conn, $sek_nr_cont) {

	$ivy_nr = 0;
	for ($i=0; $i<168; $i = $i + 3){
	$pip = $i + 3;

//	echo"------------------------------------  Segmentas ---------------------\n";	    
//----------------------  Ishrenka kiekvieno pid informacija 3 val. laiko tarpe ( segmentas ) -------------------------	
	$sql3 = 'SELECT epg_sched.pid AS pidas, epg_kan.esid, DATE_FORMAT(epg_sched.estart, \'%Y%m%d%H%i%S\')
		as startas, epg_sched.estop AS stopas , trim( epg_sched.title ) as title , trim( epg_sched.edesc )
		as edesc, epg_sched.title_lang FROM epg_sched, epg_kan WHERE epg_kan.tv24_pid = epg_sched.pid AND epg_sched.estart >= 
		(SELECT TIMESTAMPADD(HOUR,"'.$i.'",CURDATE())) AND epg_sched.estart < (SELECT TIMESTAMPADD(HOUR,"'.$pip.'",CURDATE()))
		AND epg_sched.pid = '.$pidas.' AND epg_kan.esid = '.$sidd.' ORDER BY pidas, startas;';   
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
//------------------------------------------------

function paskexdescnr($i) {

if ($i < 248) {
    $tbid = 0;
} elseif ($i >= 248 AND $i < 496) {
    $tbid = 1;
} elseif ($i >= 496 AND $i < 744) {
    $tbid = "2";
} elseif ($i >= 744 AND $i < 992) {
    $tbid = "3";
}
return $tbid;
}

//------------------------------------------------
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


/**
 * xml2array() will convert the given XML text to an array in the XML structure.
 * Link: http://www.bin-co.com/php/scripts/xml2array/
 * Arguments : $contents - The XML text
 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
 *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
 * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
 * Examples: $array =  xml2array(file_get_contents('feed.xml'));
 *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
 */
function xml2array($contents, $get_attributes=1, $priority = 'tag') {
    if(!$contents) return array();

    if(!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }

    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);

    if(!$xml_values) return;//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
    foreach($xml_values as $data) {
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data);//We could use the array by itself, but this cooler.

        $result = array();
        $attributes_data = array();
        
        if(isset($value)) {
            if($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if(isset($attributes) and $get_attributes) {
            foreach($attributes as $attr => $val) {
                if($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if($type == "open") {//The starting of the tag '<tag>'
            $parent[$level-1] = &$current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                $repeated_tag_index[$tag.'_'.$level] = 1;

                $current = &$current[$tag];

            } else { //There was another element with the same tag name

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    $repeated_tag_index[$tag.'_'.$level]++;
                } else {//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2;
                    
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                        unset($current[$tag.'_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if(!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag.'_'.$level] = 1;
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

            } else { //If taken, put all things inside a list(array)
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    
                    if($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level]++;

                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $get_attributes) {
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }
                        
                        if($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                }
            }

        } elseif($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level-1];
        }
    }
    
    return($xml_array);
}  


?>
