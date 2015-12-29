<?php
include "./inc/includas.inc";

$hex = 3;
echo $hex."\n";
 $places = 1;
$shiftas = $hex << $places;
printf("%012b\n", $hex<<=6);
printf("%012b\n", $shiftas);
echo pack("H*", "4D");
echo pack("H*", 137)."\n";
$bin = sprintf('%4X',13);
echo str_replace ( " " , "0" , $bin)."\n";
echo $bin."\n";

echo tohex('DB2f011500', 5)."\n";

$eit = "4EF0500104C5000003EA000000000001D95618000001000080154D136C6974044E616D650A4576656E7420746578740002D95619000001000000144D126C6974065661726461730741707261736173";
echo crc32mpeg($eit)."  crc \n";


$i = 2;
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
echo tableid(2)," --*- \n";
for ($i=0; $i<6; $i++){
	echo $i."\n";
	}

$feit = fopen('eit_01.ts', "r");

while (ftell($feit) < filesize('eit_01.ts')) {

echo fread($feit, 2)."\n";
fseek($feit, 186, SEEK_CUR);
}


fclose($feit);
?>