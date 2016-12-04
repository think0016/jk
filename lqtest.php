<?php
$a1=array(
		"type"=>"aa",
		"a"=>12,
		"aax"=>"aa"
);


$a2=array(
		"type"=>"aa",
		"a"=>2,
		"aax"=>"aa"
);

$a3=array(
		"type"=>"aa",
		"a"=>22,
		"aax"=>"aa"
);

$aa = array($a1,$a2,$a3);
//有一个修改
// $stime = strtotime(date("Y-m-d"));
// $etime= date("Y-m-d",strtotime("-1 weeks"));

print_r($aa);

usort($aa, 'cmp');

print_r($aa);


function cmp($a, $b) {
	if ($a['a'] == $b['a']) {
		return 0;
	}
	return ($a < $b) ? -1 : 1;
}
