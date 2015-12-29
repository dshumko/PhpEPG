<?php
include __DIR__."/inc/includas.inc";

//#####################################################  Keichiam!!!##################################
$feit = fopen( __DIR__. '/now_01.eit', "r");
$fts = fopen( __DIR__. '/now_01.ts', 'w');
//$cnt = 0;
$connfa = array($connf, $fts);

while (ftell($feit) < filesize( __DIR__. '/now_01.eit')) {
    $pirmdu = fread($feit, 2);
    $sekilgis = fread($feit, 2);//skaitom sekcijos ilgi
    $ilgis = hexdec(bin2hex($sekilgis)) - 61440;
    $kartoti = floor($ilgis / 184);
    $likutis = $ilgis - (floor($ilgis / 184) * 184);
    $ffff = 184 - ($ilgis - (floor($ilgis / 184) * 184));
    fseek($feit, -4, SEEK_CUR);
    $sekcija = fread($feit, $ilgis + 4);
    if (!$sekcija)
        die('sekcija');

    $sekarr = str_split($sekcija, 184);
    array_walk($sekarr, 'ffffunk', $connfa);
}


function ffffunk(&$item1, $key, $connfa) {
    $eilil = strlen($item1);
    if ($eilil < 184) {
        $skirt = 184 - $eilil;
        for ($i = 0; $i < $skirt; $i++)
            $item1 .= pack("H*", "FF");
    }
    if ($key == 0)
        $head = pack("H*", "47401210");

    if ($key > 0)
        $head = pack("H*", "47001210");
    fwrite($connfa[1], $head . $item1);
}


fclose($feit);
fclose($fts);
