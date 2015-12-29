<?php
include __DIR__. "/inc/includas.inc";

$conn = mysqli_connect($sqlhost, $mysqluser, $mysqlpass, $dbname);
mysqli_set_charset($conn, 'utf8');

//-------------------  Selects a row in the visual service ID  ----------------
$sql1 = 'SELECT * FROM `epg_sid`';
$result1 = mysqli_query($conn, $sql1);

while ($row1 = mysqli_fetch_assoc($result1)) {
    $fp1 = fopen($row1['eit_file'], 'w');    //Open the EIT file name of the database "epg_sid" boards
    $vers = $row1['version']; // version of the database the
    $verss = $vers + 1;
    if ($verss == 32)
        $verss = 0;
    $sqlu = 'UPDATE epg_sid SET version = ' . $verss . ' WHERE esid = (' . $row1['esid'] . ')';// Updated version of a database
    mysqli_query($conn, $sqlu);
    $vers <<= 1;
    $versijj = $vers + 193;//buvo 193
    $sek_nr_all = 0;

    //-------------------  Ishrenka existing sid rows all pid'us -------------
    $sql2 = 'SELECT ext_pid AS pidas, esid AS sidas, pid FROM `epg_kan` WHERE esid = ' . $row1['esid'] . ' ORDER BY pidas;';
    $result2 = mysqli_query($conn, $sql2);
    while ($row2 = mysqli_fetch_assoc($result2)) {
        $sek_nr = 0;
        $sek_nr_cont = 0;
        //--------- 165 hours. = 7 days ahead ----
        unset($eit);
        $ivy_nr = 0;
        for ($i = 0; $i < 168; $i = $i + 3) {
            //***** Sections after 3 hours
            $pip = $i + 3;
            $ltabid = tableid($sek_nr_cont);// finds tableID
            $sek_nr_arr = passeknr($row2[pidas], $row1[esid], $conn, $sek_nr_cont);// calculates the last section number
            $pasksecnr = $sek_nr_arr[0];
            $lastabid = $sek_nr_arr[1];

            if ($sek_nr == 256)
                $sek_nr = 0;

            //----------------------------------------  segment ----------------------------
            //---------------  Selects each pid information for 3 hours. time (segment) -------------------------
            $sql3 = "SELECT
                  epg_sched.ext_pid AS pidas,
                  epg_kan.esid,
                  DATE_FORMAT(epg_sched.estart, '%Y%m%d%H%i%S') AS startas,
                  TIME_FORMAT(epg_sched.estop, '%H%i%s') AS stopas,
                  TRIM(epg_sched.title) AS title,
                  LTRIM(epg_sched.edesc) AS edesc,
                  epg_sched.edesc_lang,
                  epg_sched.title_lang
                FROM epg_sched,
                     epg_kan
                WHERE epg_kan.ext_pid = epg_sched.ext_pid
                  AND epg_sched.estart >= (SELECT TIMESTAMPADD(HOUR, '" . $i . "', CURDATE()))
                  AND epg_sched.estart < (SELECT TIMESTAMPADD(HOUR, '" . $pip . "', CURDATE()))
                  AND epg_sched.pid = " . $row2['pidas'] . "
                ORDER BY startas;";
            $result3 = mysqli_query($conn, $sql3);

            unset($eventall);
            unset($descall);
            unset($ben_sek_il);
            //------------------------------  Rotated wheel events (events)  ---------------------------------------
            while ($row3 = mysqli_fetch_assoc($result3)) {
                //--------------------  Since chia Darom event (event)  ----------------------------------------------

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

                $row3['title'] = mb_convert_encoding($row3['title'], $lang_id, "UTF-8");//
                $row3['edesc'] = mb_convert_encoding($row3['edesc'], $lang_id, "UTF-8");//
                $titdesc_len = strlen($row3['title'] . $row3['edesc']);

                //-----------------------  WATCHING sure you will have short desc or added extended  ---------------
                if ($titdesc_len <= 248) {
                    //--------------------  Short event descriptor --------------------------------------------------------
                    $shdesc = pack("H*", "4D");// short event descriptor
                    $shdesc .= @tohex($titdesc_len + 7, 2);// + 5 = 2 bytes char years and 6 bytes language
                    $shdesc .= $row3['edesc_lang'];
                    $shdesc .= tohex(strlen($row3['title']) + 1, 2);// 1 to extend the long title
                    $shdesc .= pack("H*", $char_kod);// charset code symbol
                    $shdesc .= $row3['title'];
                    $shdesc .= tohex(strlen($row3['edesc']) + 1, 2);//  1 that extend long description
                    $shdesc .= pack("H*", $char_kod);// charset code symbol
                    $shdesc .= $row3['edesc'];
                }
                //--------------------  Extented event descriptor --------------------------------------------------------
                else {
                    //----------------------  Idedam short desc titla  --------------------------
                    $shdesc = pack("H*", "4D");// short event descriptor
                    $shdesc .= @tohex(strlen($row3['title']) + 6, 2);// + 5 = 2 bytes char years and 3 bytes language
                    $shdesc .= $row3['edesc_lang'];
                    $shdesc .= tohex(strlen($row3['title']) + 1, 2);// 1 to extend the long title
                    $shdesc .= pack("H*", $char_kod);//  charset code symbol
                    $shdesc .= $row3['title'];
                    $exdesc .= pack("H*", "00");// Text goes Extended!!!

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
                        $exdesc .= pack("H*", "00");//  item_description_length
                        $exdesc .= @tohex($ex_len + 1, 2);// deskripshino teksto ilgis + 1 char set
                        $exdesc .= pack("H*", $char_kod);// Ikisham charset kodo simboli
                        $exdesc .= $ex_tx_su;// dedam teksta
                        $exdesc_nr += 1;
                    }
                }

                //-----------------------------  Event ------------------------------------------------------------

                $desc_len = strlen($shdesc . $exdesc . $muldesc);
                $ben_sek_il = $ben_sek_il + $desc_len + 12;
                if ($ben_sek_il + 14 > 4000)
                    break;

                $event = tohex($ivy_nr, 4);//Event nr.
                $event .= mjd($row3['startas']);
                $event .= pack("H*", $row3['stopas']);
                $event .= tohex($desc_len, 4);// Full-length description

                $eventall .= $event;
                $eventall .= $shdesc;
                $eventall .= $exdesc;
                $eventall .= $muldesc;
                $ivy_nr++;
            }
            /////////////////////////// Baigiam ivykiu rata

            if (mysql_num_rows($result3)) {

                //------------------------------------  Since chia goes EIT section description  --------------------------------------------
                $eit = tohex($row2[pid], 4);//The program broadcasts to the client's ID
                $eit .= tohex($versijj, 2);//versija, curent next
                $eit .= tohex($sek_nr, 2);//N sections.
                $eit .= tohex($pasksecnr, 2);//The last section N
                $eit .= tohex($row1[esid], 4);//Transport stream ID
                $eit .= pack("H*", "0457");//The original network ID
                $eit .= tohex($sek_nr, 2);//Segment last section number !!!!!!!!!! we still are not, put the section number
                $eit .= pack("H*", $lastabid);//The last board ID
                $sekilg = strlen($eit . $eventall . $descall);//Section length
                $sekilg = $sekilg + 61444;//  61444 reikalingas bitu prastumimui
                $sekilg = tohex($sekilg, 4);
                $tabid = pack("H*", $ltabid);// Lentos ID
                $creit = $tabid . $sekilg . $eit . $eventall . $descall;
                $creit = bin2hex($creit);
                $crc = pack("H*", crc32mpeg($creit));//CRC mpeg2
                $eit = $tabid . $sekilg . $eit;

                fwrite($fp1, pack("H*", "00"));// prieki sekcijos du 00
                fwrite($fp1, $eit);
                fwrite($fp1, $eventall);
                fwrite($fp1, $descall);
                fwrite($fp1, $crc);

                $sek_nr += 8;
                $sek_nr_cont += 8;
            }
        }
    }
    fclose($fp1);
}
mysqli_free_result($result1);
mysqli_free_result($result2);
mysqli_free_result($result3);
mysqli_close($conn);

