<?php


exec ("php /var/tv24_epg/parser.php");

usleep(1000);

exec ("php /var/tv24_epg/stream_maker.php");
usleep(1000);

exec ("php /var/tv24_epg/pack01.php");
exec ("php /var/tv24_epg/pack02.php");
exec ("php /var/tv24_epg/pack03.php");
exec ("php /var/tv24_epg/pack04.php");
exec ("php /var/tv24_epg/pack05.php");
exec ("php /var/tv24_epg/pack06.php");
exec ("php /var/tv24_epg/pack07.php");
exec ("php /var/tv24_epg/pack08.php");

usleep(1000);

//exec("killall php", $output);

usleep(1000);

exec ("php /var/tv24_epg/udp01.php >/dev/null &");
exec ("php /var/tv24_epg/udp02.php >/dev/null &");
exec ("php /var/tv24_epg/udp03.php >/dev/null &");
exec ("php /var/tv24_epg/udp04.php >/dev/null &");
exec ("php /var/tv24_epg/udp05.php >/dev/null &");
exec ("php /var/tv24_epg/udp06.php >/dev/null &");
exec ("php /var/tv24_epg/udp07.php >/dev/null &");
exec ("php /var/tv24_epg/udp08.php >/dev/null &");

?>