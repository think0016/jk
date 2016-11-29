<?php

namespace Jk\Controller;

class MonitorController extends BaseController {
	public function index() {
		
		// 检查登录情况
		$this->is_login ( 1 );
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
		$this->display ();
	}
	
	// 读RRD数据
	public function rrd_get($filename, $sdate, $edate, $step = 300) {
		$stime = strtotime ( $sdate );
		$etime = strtotime ( $edate );
		
		// $c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime." -e ".$etime;
		
		$c = "sh /var/www/ce/rrd_get.sh " . $filename . " " . $stime . " " . $etime . " " . $step;
		
		exec ( $c, $output );
		return $output;
	}
	
	// 读RRD数据
	public function rrd_avg($filename, $sdate, $edate, $step = 300) {
		$stime = strtotime ( $sdate );
		$etime = strtotime ( $edate );
		
		// $c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime." -e ".$etime;
		$c = "sh /var/www/ce/rrd_avg.sh " . $filename . " " . $stime . " " . $etime . " " . $step;
		exec ( $c, $output );
		return $output;
	}
	
	// 读RRD数据
	public function rrd_max($filename, $sdate, $edate, $step = 300) {
		$stime = strtotime ( $sdate );
		$etime = strtotime ( $edate );
		
		// $c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime." -e ".$etime;
		$c = "sh /var/www/ce/rrd_max.sh " . $filename . " " . $stime . " " . $etime . " " . $step;
		exec ( $c, $output );
		return $output;
	}
	
	// 读RRD数据（多文件）
	public function rrd_get_m($filename, $sdate, $edate, $step = 300) {
		$stime = strtotime ( $sdate );
		$etime = strtotime ( $edate );
		
		$fn = "";
		if (is_array ( $filename )) {
			foreach ( $filename as $val ) {
				if ($fn != "") {
					$fn = $fn . " " . $val;
				} else {
					$fn = $val;
				}
			}
		} else {
			exit ();
		}
		// $c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime." -e ".$etime;
		$c = "sh /var/www/ce/cmd/getdata.sh \"" . $fn . "\" " . $stime . " " . $etime . " " . $step;
		exec ( $c, $output );
		// print_r($c);
		// print_r($output);
		return $output;
	}
	public function getrrdfilename($tid, $uid, $mid, $sid, $ssid, $itemid, $type = 0) {
		$filename = C ( 'rrd_dir' ) . $tid . "_" . $uid . "_" . $mid . "_" . $sid . "_" . $ssid . "_" . $itemid . ".rrd";
		if ($type == 1) {
			$filename = C ( 'rrd_dir' ) . $tid . "_" . $uid . "_" . $mid . "_" . $sid . "_" . $ssid . "_" . $itemid . "_status.rrd";
		}
		
		return $filename;
	}
	
	public function timeinterval($stime, $etime) {
		$arr = array ();
		if ($stime == "" || $etime == "") {
			$sdate = date ( "Y-m-d 00:00:00" );
			$edate = date ( "Y-m-d H:i:s" );
			$arr[] = $sdate;
			$arr[] = $edate;
		} else {
			$sdate = $stime;
			$edate = $etime;
			$now = date ( "Y-m-d" );
			$yesterday = date ( "Y-m-d", strtotime ( "-1 day" ) );
			if ($now == $edate) {
				$edate = date ( "Y-m-d H:i:s" );
			}
			if ($yesterday == $edate) {
				$edate = date ( "Y-m-d" );
			}
// 			if ($yesterday == $sdate) {
// 				$sdate = date ( "Y-m-d" );
// 			}
			$arr[] = $sdate;
			$arr[] = $edate;
		}
		return $arr;
	}
}