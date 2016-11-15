<?php
$a=array(
		"type"=>"aa",
		"aa"=>"aa",
		"aax"=>"aa"
);

//有一个修改
$stime = strtotime(date("Y-m-d"));
$etime= date("Y-m-d",strtotime("-1 weeks"));

echo $stime;