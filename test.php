<?php
$time_start = microtime(true);

// Sleep for a while
usleep(900000);

$time_end = microtime(true);
$time = $time_end - $time_start;

if ($time >= 1.0000){
echo "Daugiau nei sekunde $time seconds\n";
}else{
echo "Maziau nei sekunde $time seconds\n";
}
?>