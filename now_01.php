<?php
include __DIR__ . "/inc/includas.inc";

$esidas = "1001";//*#*#*#*#*#*#*  Keichiam esid!!!!!!! *#*#*#*#*#*#*#*#*#*#*#*#*#*#*#**#*#**#*#*#*#**#*#**#*#*#**#

$conn = mysqli_connect($sqlhost, $mysqluser, $mysqlpass, $dbname);
mysqli_set_charset($conn, 'utf8');

//-------------------  Ishrenka ish eiles visus service ID  ----------------
$sql1 = 'SELECT * FROM epg_kan WHERE epg_kan.esid = "' . $esidas . '" ORDER BY epg_kan.pid ASC';
$result1 = mysqli_query($conn, $sql1);
//$row2 = mysqli_fetch_assoc($result1);

$now1 = 'SELECT * FROM `configas`';
$nowres1 = mysqli_query($conn, $sql1);


$fp1 = fopen(__DIR__ . "/now_01.eit", 'w');    // Keichiam!!!*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#*#

$sqlver = 'SELECT now_ver FROM `configas`';
$resver1 = mysqli_query($conn, $sqlver);
$ver1 = mysqli_fetch_assoc($resver1);

$vers = $ver1['now_ver']; // versija ish duombazes
$verss = $vers + 1;
if ($verss == 32)
    $verss = 0;
$sqlu = 'UPDATE configas SET now_ver = ' . $verss . ' ';// Atnaujinam versija duombazej
mysqli_query($conn, $sqlu);
$vers <<= 1;
$versijj = $vers + 193;//buvo 193
$ivy_nr = 0;


while ($row1 = mysqli_fetch_assoc($result1)) {

    //#################################   Pradedam now ivyki   ###################################
    //   Ishrenkam kas eina dabar
    $sql_now = 'SELECT
        epg_sched.pid AS pidas,
        epg_kan.esid,
        DATE_FORMAT(epg_sched.estart, "%Y%m%d%H%i%S") AS startas,
        TIME_FORMAT(epg_sched.estop, "%H%i%s") AS stopas,
        ADDTIME(epg_sched.estart, epg_sched.estop) AS stoplaik,
        TRIM(epg_sched.title) AS title,
        LTRIM(epg_sched.edesc) AS edesc,
        epg_sched.edesc_lang,
        epg_sched.title_lang
      FROM epg_sched,
           epg_kan
      WHERE epg_kan.esid = "' . $esidas . '"
        AND epg_sched.pid = "' . $row1['ext_pid'] . '"
        AND epg_sched.estart <= DATE_SUB(NOW(), INTERVAL 3 HOUR)
        AND ADDTIME(epg_sched.estart, epg_sched.estop) > DATE_SUB(NOW(), INTERVAL 3 HOUR) LIMIT 1';

    $result_now = mysqli_query($conn, $sql_now);
    $row3 = mysqli_fetch_assoc($result_now);

    $sek_nr = 0;
    $pasksecnr = 1;

    unset($eventall);
    unset($descall);
    unset($ben_sek_il);

    unset($shdesc);
    unset($exdesc);
    unset($muldesc);
    unset($event);
    unset($desc_len);
    unset($eventall);

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

    $row3['title'] = mb_convert_encoding($row3['title'], $lang_id, "UTF-8");//
    $row3['edesc'] = mb_convert_encoding($row3['edesc'], $lang_id, "UTF-8");//
    $titdesc_len = strlen($row3['title'] . $row3['edesc']);

    //-----------------------  Ziurim ar uzteks short desc ar dedam extended ---------------
    if ($titdesc_len <= 248) {
        //--------------------  Short event descriptor --------------------------------------------------------

        $shdesc = pack("H*", "4D");// short event descriptoriaus zyme
        $shdesc .= @tohex($titdesc_len + 7, 2);// + 5 = 2 baitai char setai ir 6 baitai kalba
        $shdesc .= $row3['edesc_lang'];
        $shdesc .= tohex(strlen($row3['title']) + 1, 2);// 1 kad pailginti title ilgi
        $shdesc .= pack("H*", $char_kod);// Ikisham charset kodo simboli
        $shdesc .= $row3['title'];
        $shdesc .= tohex(strlen($row3['edesc']) + 1, 2);//  1 kad pailginti decription ilgi
        $shdesc .= pack("H*", $char_kod);// Ikisham charset kodo simboli
        $shdesc .= $row3['edesc'];
    } //--------------------  Extented event descriptor --------------------------------------------------------
    else {
        //----------------------  Idedam short desc titla  --------------------------

        $shdesc = pack("H*", "4D");// short event descriptoriaus zyme
        $shdesc .= @tohex(strlen($row3['title']) + 6, 2);// + 5 = 2 baitai char setai ir 3 baitai kalba
        $shdesc .= $row3['edesc_lang'];
        $shdesc .= tohex(strlen($row3['title']) + 1, 2);// 1 kad pailginti title ilgi
        $shdesc .= pack("H*", $char_kod);// Ikisham charset kodo simboli
        $shdesc .= $row3['title'];
        $exdesc = pack("H*", "00");// Tekstas eina extendet !!!

        $exdesc_nr = 0;
        $pask_exdesc_nr = paskexdescnr(strlen($row3['edesc']));
        $ex_tx = $row3['edesc'];
        $exdesc_len = strlen($ex_tx);

        //------------------------ Extendet tekstas --------------------------------------
        for ($ia = $exdesc_len; $ia > 0; $ia = $ia - 246) {
            $ex_tx_su = substr($ex_tx, 0, 246);
            $ex_tx = substr_replace($ex_tx, '', 0, 246);

            $exdesc .= pack("H*", "4E");// extended event descriptoriaus zyme
            $ex_len = strlen($ex_tx_su);
            $exdesc .= @tohex(($ex_len + 7), 2);// deskripshino ilgis + kiek reikia
            $exdesc .= @tohex(($exdesc_nr * 16) + $pask_exdesc_nr, 2);
            $exdesc .= $row3['edesc_lang'];
            $exdesc .= pack("H*", "00");// Item_description_length
            $exdesc .= @tohex($ex_len + 1, 2);// deskripshino teksto ilgis + 1 char set
            $exdesc .= pack("H*", $char_kod);// Ikisham charset kodo simboli
            $exdesc .= $ex_tx_su;// dedam teksta
            $exdesc_nr += 1;

        }
    }

    //--------------------- Jei yra kalbu kviechiam multi_lang funkcija ------------------------------

    if ($row3['tit_1']) {
        $muldesc .= multlan($row3['tit_1'], $row3['tit_lan_1'], $row3['edes_1'], $row3['edes_lan_1']);
    }
    if ($row3['tit_2']) {
        $muldesc .= multlan($row3['tit_2'], $row3['tit_lan_2'], $row3['edes_2'], $row3['edes_lan_2']);
    }
    if ($row3['tit_3']) {
        $muldesc .= multlan($row3['tit_3'], $row3['tit_lan_3'], $row3['edes_3'], $row3['edes_lan_3']);
    }
    if ($row3['tit_4']) {
        $muldesc .= multlan($row3['tit_4'], $row3['tit_lan_4'], $row3['edes_4'], $row3['edes_lan_4']);
    }

    //-----------------------------  Event ------------------------------------------------------------
    $desc_len = strlen($shdesc . $exdesc . $muldesc);
    $ben_sek_il = $ben_sek_il + $desc_len + 12;
    if ($ben_sek_il + 14 > 4000)
        break;

    $event = tohex($ivy_nr, 4);//Event nr.
    $event .= mjd($row3['startas']);
    $event .= pack("H*", $row3['stopas']);

    $event .= tohex($desc_len + 32768, 4);// Visu descripshinu ilgis + running status

    $eventall = $event;
    $eventall .= $shdesc;
    $eventall .= $exdesc;
    $eventall .= $muldesc;

    $ivy_nr++;

    //------------------------------------  Nuo chia eina EIT sekcijos aprashas --------------------------------------------
    $eit = tohex($row1[pid], 4);//Programos ID kliento transliuojamos programos
    $eit .= tohex($versijj, 2);//versija, curent next
    $eit .= tohex($sek_nr, 2);//Sekcijos Nr.
    $eit .= tohex($pasksecnr, 2);//Paskutines sekcijos Nr.
    $eit .= tohex($row1[esid], 4);//Transport stream ID
    $eit .= pack("H*", "0457");//Originalus tinklo ID
    $eit .= tohex($pasksecnr, 2);//Segmeno paskutines sekcijos numeris !!!!!!!!!!kolkas nera,  ideta sekcijos numeris
    $eit .= pack("H*", "4E");//Paskutines lentos ID
    $sekilg = strlen($eit . $eventall . $descall);//Sekcijos ilgis

    $sekilg = $sekilg + 61444;//  61444 reikalingas bitu prastumimui
    $sekilg = tohex($sekilg, 4);
    $tabid = pack("H*", "4E");// Lentos ID
    $creit = $tabid . $sekilg . $eit . $eventall . $descall;
    $creit = bin2hex($creit);
    $crc = pack("H*", crc32mpeg($creit));//CRC mpeg2
    $eit = $tabid . $sekilg . $eit;
    fwrite($fp1, pack("H*", "00"));// prieki sekcijos du 00
    fwrite($fp1, $eit);
    fwrite($fp1, $eventall);
    fwrite($fp1, $descall);
    fwrite($fp1, $crc);

    //#################################   Baigiam now ivyki   ####################################
    //#################################   Pradedam next ivyki   ################################################
    //   Ishrenkam kas bus next, renkam po viena

    $sek_nr = 1;
    $pasksecnr = 1;

    unset($eventall);
    unset($descall);
    unset($ben_sek_il);

    unset($shdesc);
    unset($exdesc);
    unset($muldesc);
    unset($event);
    unset($desc_len);

    $sql_next = '
    SELECT
      epg_sched.pid AS pidas,
      epg_kan.esid,
      DATE_FORMAT(epg_sched.estart, "%Y%m%d%H%i%S") AS startas,
      TIME_FORMAT(epg_sched.estop, "%H%i%s") AS stopas,
      ADDTIME(epg_sched.estart, epg_sched.estop) AS stoplaik,
      TRIM(epg_sched.title) AS title,
      LTRIM(epg_sched.edesc) AS edesc,
      epg_sched.edesc_lang,
      epg_sched.title_lang
    FROM epg_sched,
         epg_kan
    WHERE epg_kan.esid = "' . $esidas . '"
      AND epg_sched.pid = "' . $row1['ext_pid'] . '"
      AND epg_sched.estart > DATE_SUB(NOW(), INTERVAL 3 HOUR)
    ORDER BY epg_sched.estart ASC
    LIMIT 1
    ';

    $result_next = mysqli_query($conn, $sql_next);
    $row4 = mysqli_fetch_assoc($result_next);

    $lang_id = 'ISO_8859-2';
    $char_kod = '0A';

    if ($row4['title_lang'] == 'rus' OR $row4['title_lang'] == 'ukr' OR $row4['title_lang'] == 'xxx') {
        $lang_id = 'ISO_8859-5';
        $char_kod = '01';
        $row4['edesc'] = str_replace(chr(14844051), "-", $row4['edesc']);
    }
    if ($row4['title_lang'] == 'lit' OR $row4['title_lang'] == 'lav' OR $row4['title_lang'] == 'est' OR $row4['title_lang'] == 'pol') {
        $lang_id = 'ISO_8859-13';//
        $char_kod = '09';
        $row4['edesc'] = str_replace(chr(14844051), "-", $row4['edesc']);
    }
    if ($row4['title_lang'] == 'spa' OR $row4['title_lang'] == 'ita' OR $row4['title_lang'] == 'ger') {
        $lang_id = 'ISO_8859-1';
        $char_kod = '0A';
        $row4['edesc'] = str_replace(chr(14844051), "-", $row4['edesc']);
    }

    $row4['title'] = mb_convert_encoding($row4['title'], $lang_id, "UTF-8");//
    $row4['edesc'] = mb_convert_encoding($row4['edesc'], $lang_id, "UTF-8");//
    $titdesc_len = strlen($row4['title'] . $row4['edesc']);

    //-----------------------  Ziurim ar uzteks short desc ar dedam extended ---------------
    if ($titdesc_len <= 248) {
        //--------------------  Short event descriptor --------------------------------------------------------

        $shdesc = pack("H*", "4D");// short event descriptoriaus zyme
        $shdesc .= @tohex($titdesc_len + 7, 2);// + 5 = 2 baitai char setai ir 6 baitai kalba
        $shdesc .= $row4['edesc_lang'];
        $shdesc .= tohex(strlen($row4['title']) + 1, 2);// 1 kad pailginti title ilgi
        $shdesc .= pack("H*", $char_kod);// Ikisham charset kodo simboli
        $shdesc .= $row4['title'];
        $shdesc .= tohex(strlen($row4['edesc']) + 1, 2);//  1 kad pailginti decription ilgi
        $shdesc .= pack("H*", $char_kod);// Ikisham charset kodo simboli
        $shdesc .= $row4['edesc'];
    } //--------------------  Extented event descriptor --------------------------------------------------------
    else {
        //----------------------  Idedam short desc titla  --------------------------
        $shdesc = pack("H*", "4D");// short event descriptoriaus zyme
        $shdesc .= @tohex(strlen($row4['title']) + 6, 2);// + 5 = 2 baitai char setai ir 3 baitai kalba
        $shdesc .= $row4['edesc_lang'];
        $shdesc .= tohex(strlen($row4['title']) + 1, 2);// 1 kad pailginti title ilgi
        $shdesc .= pack("H*", $char_kod);// Ikisham charset kodo simboli
        $shdesc .= $row4['title'];
        $exdesc .= pack("H*", "00");// Tekstas eina extendet !!!

        $exdesc_nr = 0;
        $pask_exdesc_nr = paskexdescnr(strlen($row4['edesc']));
        $ex_tx = $row4['edesc'];
        $exdesc_len = strlen($ex_tx);

        //------------------------ Extendet tekstas --------------------------------------
        for ($ia = $exdesc_len; $ia > 0; $ia = $ia - 246) {
            $ex_tx_su = substr($ex_tx, 0, 246);
            $ex_tx = substr_replace($ex_tx, '', 0, 246);

            $exdesc .= pack("H*", "4E");// extended event descriptoriaus zyme
            $ex_len = strlen($ex_tx_su);
            $exdesc .= @tohex(($ex_len + 7), 2);// deskripshino ilgis + kiek reikia
            $exdesc .= @tohex(($exdesc_nr * 16) + $pask_exdesc_nr, 2);
            $exdesc .= $row4['edesc_lang'];
            $exdesc .= pack("H*", "00");//  item_description_length
            $exdesc .= @tohex($ex_len + 1, 2);// deskripshino teksto ilgis + 1 char set
            $exdesc .= pack("H*", $char_kod);// Ikisham charset kodo simboli
            $exdesc .= $ex_tx_su;// dedam teksta
            $exdesc_nr += 1;

        }// baigem FOR
    } //baigem ELSE

    //--------------------- Jei yra kalbu kviechiam multi_lang funkcija ------------------------------

    if ($row4['tit_1']) {
        $muldesc .= multlan($row4['tit_1'], $row4['tit_lan_1'], $row4['edes_1'], $row4['edes_lan_1']);
    }
    if ($row4['tit_2']) {
        $muldesc .= multlan($row4['tit_2'], $row4['tit_lan_2'], $row4['edes_2'], $row4['edes_lan_2']);
    }
    if ($row4['tit_3']) {
        $muldesc .= multlan($row4['tit_3'], $row4['tit_lan_3'], $row4['edes_3'], $row4['edes_lan_3']);
    }
    if ($row4['tit_4']) {
        $muldesc .= multlan($row4['tit_4'], $row4['tit_lan_4'], $row4['edes_4'], $row4['edes_lan_4']);
    }

    //-----------------------------  Event ------------------------------------------------------------

    $desc_len = strlen($shdesc . $exdesc . $muldesc);
    $ben_sek_il = $ben_sek_il + $desc_len + 12;
    if ($ben_sek_il + 14 > 4000)
        break;

    $event = tohex($ivy_nr, 4);//Event nr.
    $event .= mjd($row4['startas']);
    $event .= pack("H*", $row4['stopas']);

    $event .= tohex($desc_len, 4);// Visu descripshinu ilgis + running status
    //$event.= tohex($desc_len + 8192, 4);// Visu descripshinu ilgis + running status

    $eventall = $event;
    $eventall .= $shdesc;
    $eventall .= $exdesc;
    $eventall .= $muldesc;

    $ivy_nr++;

    //#################################   Baigiam next ivyki   ####################################
    //------------------------------------  Nuo chia eina EIT sekcijos aprashas --------------------------------------------
    $eit = tohex($row1[pid], 4);//Programos ID kliento transliuojamos programos
    $eit .= tohex($versijj, 2);//versija, curent next
    $eit .= tohex($sek_nr, 2);//Sekcijos Nr.
    $eit .= tohex($pasksecnr, 2);//Paskutines sekcijos Nr.
    $eit .= tohex($row1[esid], 4);//Transport stream ID
    $eit .= pack("H*", "0457");//Originalus tinklo ID
    $eit .= tohex($pasksecnr, 2);//Segmeno paskutines sekcijos numeris !!!!!!!!!!kolkas nera,  ideta sekcijos numeris
    $eit .= pack("H*", "4E");//Paskutines lentos ID
    $sekilg = strlen($eit . $eventall . $descall);//Sekcijos ilgis
    $sekilg = $sekilg + 61444;//  61444 reikalingas bitu prastumimui
    $sekilg = tohex($sekilg, 4);
    $tabid = pack("H*", "4E");// Lentos ID
    $creit = $tabid . $sekilg . $eit . $eventall . $descall;
    $creit = bin2hex($creit);
    $crc = pack("H*", crc32mpeg($creit));//CRC mpeg2
    $eit = $tabid . $sekilg . $eit;
    fwrite($fp1, pack("H*", "00"));// prieki sekcijos du 00
    fwrite($fp1, $eit);
    fwrite($fp1, $eventall);
    fwrite($fp1, $descall);
    fwrite($fp1, $crc);
}

fclose($fp1);

mysqli_free_result($result1);
mysqli_free_result($nowres1);
mysqli_free_result($result_now);
mysqli_close($conn);
