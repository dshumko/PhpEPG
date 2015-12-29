<?php


exec("php parser.php");

usleep(1000);

exec("php stream_maker.php");
usleep(1000);

exec("php pack01.php");

usleep(1000);

//exec("killall php", $output);

//usleep(1000);

exec("php udp01.php >/dev/null &");
