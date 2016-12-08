<?php
// $c="sh /var/www/ce/rrd_avg.sh /var/www/ce/rrd/91_60002_3_6_0_1.rrd 1480780800 1480859244 3600";
// exec ( $c, $output );
// print_r($output);


$m = ":6:,:7:";
$m2 = str_replace ( ":", "", $m );
$temp = explode(",", $m2);
print_r($temp);

