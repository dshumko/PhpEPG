<?php
include "./inc/includas.inc";

$feit = fopen('eit_01.eit', "r");
$fts = fopen('eit_01.ts', 'w');	
$cnt = 0;
fseek($feit, 2, SEEK_CUR);
//$data = fread($feit, 1);
while (!feof($feit)) {

$sekilgis = fread($feit, 2);
$ilgis = hexdec(bin2hex($sekilgis)) - 61436;
$kartoti = floor($ilgis / 184);
$likutis = $ilgis - (floor($ilgis / 184) * 184);
$ffff = 184 - ($ilgis - (floor($ilgis / 184) * 184));
fseek($feit, -4, SEEK_CUR);
for ($i=0; $i<$kartoti; $i++){
	If ($i == 0){////////////////////////////////// sitas panashu dirba kada nereikia
	//$head1 = '4740121'.dechex($cnt);
	fwrite  ($fts, pack("H*", '4740121'.dechex($cnt)));
	fwrite  ($fts, fread($feit, 184));
	//fwrite  ($fts, $uzpil, $ffff);
		if ($cnt == 15) {
		$cnt = 0;
		} else {
		$cnt++;
		}
	//continue;
	}else{
	fwrite  ($fts, pack("H*", "4700121".dechex($cnt)));
	$eilute = (fread($feit, 184));
//	echo bin2hex($eilute)."-\n";
	fwrite  ($fts, $eilute);
		if ($cnt == 15) {
		$cnt = 0;
		} else {
		$cnt++;
		}
	}		
}
	If ($kartoti == 0){
	fwrite  ($fts, pack("H*", "4740121".dechex($cnt)));
	fwrite  ($fts, fread($feit, $likutis));
	for ($i=0; $i<$ffff; $i++){
	fwrite ($fts, pack("H*", "FF"));}
	//fwrite  ($fts, $uzpil, $ffff);
		if ($cnt == 15) {
		$cnt = 0;
		} else {
		$cnt++;
		}
	}

	If ($kartoti > 0){
	fwrite  ($fts, pack("H*", "4700121".dechex($cnt)));
	fwrite  ($fts, fread($feit, $likutis));
echo strlen($likutis)." -\n";
	for ($i=0; $i<$ffff; $i++){
	fwrite ($fts, pack("H*", "FF"));}
		if ($cnt == 15) {
		$cnt = 0;
		} else {
		$cnt++;
		}
	}
//fwrite  ( resource $handle  , string $string  [, int $length  ] );

//fseek($feit, hexdec(bin2hex($content)) - 61439, SEEK_CUR);
//echo $ilgis." + ".$ffff." = ".($ilgis + $ffff)."\n---- ilgis ------\n";

//echo hexdec(bin2hex($content)) - 61439 ."\n----------\n";
//echo bin2hex($data)."\n-------------\n";
//echo $data."\n------------------------------------------------------------------------------------------\n";

fseek($feit, 2, SEEK_CUR);
}


fclose($feit);
fclose($fts);
?>