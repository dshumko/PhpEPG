<?php


exec ("php /var/epg/parser.php");

usleep(1000);

exec ("php /var/epg/stream_maker.php");
usleep(1000);

exec ("php /var/epg/pack01.php");
exec ("php /var/epg/pack02.php");
exec ("php /var/epg/pack03.php");
exec ("php /var/epg/pack04.php");
exec ("php /var/epg/pack05.php");
exec ("php /var/epg/pack06.php");
exec ("php /var/epg/pack07.php");
exec ("php /var/epg/pack08.php");

usleep(1000);

//exec("killall php", $output);

usleep(1000);

exec ("php /var/epg/udp01.php >/dev/null &");
exec ("php /var/epg/udp02.php >/dev/null &");
exec ("php /var/epg/udp03.php >/dev/null &");
exec ("php /var/epg/udp04.php >/dev/null &");
exec ("php /var/epg/udp05.php >/dev/null &");
exec ("php /var/epg/udp06.php >/dev/null &");
exec ("php /var/epg/udp07.php >/dev/null &");
exec ("php /var/epg/udp08.php >/dev/null &");

?>