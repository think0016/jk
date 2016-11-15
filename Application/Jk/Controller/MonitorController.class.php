<?php
namespace Jk\Controller;

class MonitorController extends BaseController {
    public function index(){
    	
    	//检查登录情况
    	$this->is_login(1);
    	$this->assign("sitetitle",C('sitetitle'));
        $this->display();
    }
    
    
    //读RRD数据
    public function rrd_get($filename,$sdate,$edate,$step=300){
    	$stime=strtotime($sdate);
    	$etime=strtotime($edate);
    
    	//$c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime."  -e ".$etime;

    	$c="sh /var/www/ce/rrd_get.sh ".$filename." ".$stime." ".$etime." ".$step;
    	
    	exec($c,$output);
    	return $output;
    }
    
    //读RRD数据
    public function rrd_avg($filename,$sdate,$edate,$step=300){
    	$stime=strtotime($sdate);
    	$etime=strtotime($edate);
    
    	//$c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime."  -e ".$etime;
    	$c="sh /var/www/ce/rrd_avg.sh ".$filename." ".$stime." ".$etime." ".$step;
    	exec($c,$output);
    	return $output;
    }
    
    //读RRD数据
    public function rrd_max($filename,$sdate,$edate,$step=300){
    	$stime=strtotime($sdate);
    	$etime=strtotime($edate);
    
    	//$c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime."  -e ".$etime;
    	$c="sh /var/www/ce/rrd_max.sh ".$filename." ".$stime." ".$etime." ".$step;
    	exec($c,$output);
    	return $output;
    }
    
    public function getrrdfilename($tid, $uid, $mid, $sid, $ssid, $itemid) {
    	return C('rrd_dir').$tid . "_" . $uid . "_" . $mid . "_" . $sid . "_" . $ssid . "_" . $itemid.".rrd";
    }
}