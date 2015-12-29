<?php
include __DIR__.'/inc/includas.inc';

function XmltvDateTime($str) {
    //20151219020000 +0000
    $v = explode(' ', $str);
    $t = $v[0];
    $y = substr($t, 0, 4);
    $m = substr($t, 4, 2);
    $d = substr($t, 6, 2);
    $h = substr($t, 8, 2);
    $mi =substr($t, 10, 2);
    return mktime($h, $mi, 0, $m, $d, $y);
}

$xmlStr = file_get_contents('xmltv.xml');

//------ Editing errors symbols of evil -------
$xmlStr = str_replace('&apos;', '\'', $xmlStr);
//$xmlStr = str_replace(';', ',', $xmlStr);//&
$xmlStr = str_replace('&amp', 'and ', $xmlStr);
$xmlStr = str_replace('&quot', '', $xmlStr);
$arrxml = xml2array($xmlStr, 1, 'attribute');

$conn = mysqli_connect($sqlhost, $mysqluser, $mysqlpass, $dbname);
mysqli_set_charset($conn, 'utf8');

$trunk = 'truncate table epg_sched';
mysqli_query($conn, $trunk);

foreach ($arrxml['tv']['programme'] as $a => $value) {
    $chan = $value['attr']['channel'];
    $start = XmltvDateTime($value['attr']['start']);
    $stop = XmltvDateTime($value['attr']['stop']);
    $duration = date('His', ($stop - $start + 79200));
    
    $titlas = $value['title']['value'];
    $title_lang = $value['title']['attr']['lang'];
    if (array_key_exists('desc', $value)) {
        $descripshinas = $value['desc']['value'];
        $descript_lang = $value['desc']['attr']['lang'];
    }
    else {
        $descripshinas = '';
        $descript_lang = 'ru';
    }
    
    $insert = "INSERT INTO epg_sched (ext_pid, estart, estop, title, title_lang, edesc, edesc_lang) VALUES ('$chan', FROM_UNIXTIME($start), FROM_UNIXTIME($stop), TRIM('$titlas'),'$title_lang', TRIM('$descripshinas'), '$descript_lang');";
    $err = mysqli_query($conn,$insert);
}

mysqli_close($conn);
