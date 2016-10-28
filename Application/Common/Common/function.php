<?php
function makefloatwithpoint2($num)
{
	return floor($num*100)/100;
}
function object_array($array){
  if(is_object($array)){
    $array = (array)$array;
  }
  if(is_array($array)){
    foreach($array as $key=>$value){
      $array[$key] = object_array($value);
    }
  }
  return $array;
} 

function wlog($data){
	$dir="/var/www/ce/log/";
	$filename = $dir.date("Ymd").".log";
	$logdata=date("[Y-m-d H:i:s]").PHP_EOL;
	$logdata=$logdata.$data.PHP_EOL;
	return file_put_contents($filename, $logdata,FILE_APPEND);
}

//读RRD数据
function rrd_get($filename,$sdate,$edate,$step=300){
	$stime=strtotime($sdate);
	$etime=strtotime($edate);
	
	//$c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime."  -e ".$etime;
	$c="sh /var/www/ce/rrd_get.sh ".$filename." ".$stime." ".$etime." ".$step;
	exec($c,$output);
	return $output;
}
?>