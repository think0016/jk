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
	
	// 读RRD数据(服务性能)
	public function rrd_server_get($filename, $sdate, $edate, $data_type = 0, $step = 300) {
		$stime = strtotime ( $sdate );
		$etime = strtotime ( $edate );
		
		// $c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime." -e ".$etime;
		
		$c = "sh /var/www/ce/cmd/get_list_data.sh " . $filename . " " . $stime . " " . $etime . " " . $step . " " . $data_type;
		
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
		// print_r($output);
		return $output;
	}

	// 读RRD数据
	public function rrd_server_avg($filename, $sdate, $edate, $data_type = 0, $step = 300) {
		$stime = strtotime ( $sdate );
		$etime = strtotime ( $edate );
		
		// $c="/usr/bin/rrdtool fetch ".$filename." AVERAGE -r 300 -s ".$stime." -e ".$etime;
		
		$c = "sh /var/www/ce/cmd/rrd_avg.sh " . $filename . " " . $stime . " " . $etime . " " . $step . " " . $data_type;
		
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
		// wlog("[rrd_get_m]".$c);
		exec ( $c, $output );
		return $output;
	}
	public function getrrdfilename($tid, $uid, $mid, $sid, $ssid, $itemid, $type = 0) {
		$filename = C ( 'rrd_dir' ) . $tid . "_" . $uid . "_" . $mid . "_" . $sid . "_" . $ssid . "_" . $itemid . ".rrd";
		if ($type == 1) {
			$filename = C ( 'rrd_dir' ) . $tid . "_" . $uid . "_" . $mid . "_" . $sid . "_" . $ssid . "_" . $itemid . "_status.rrd";
		}
		
		return $filename;
	}
	
	/**
	 * 计算时间间隔
	 *
	 * @param unknown $param        	
	 */
	public function timeinterval($stime, $etime, $sel = "") {
		$arr = array ();
		if ($stime == "" || $etime == "") {
			if ($sel == "") {
				$sdate = date ( "Y-m-d 00:00:00" );
				$edate = date ( "Y-m-d H:i:s" );
				$arr [] = $sdate;
				$arr [] = $edate;
			} else if ($sel == "w") { // 一周前
				$sdate = date ( "Y-m-d 00:00:00", strtotime ( "-1 week" ) );
				$edate = date ( "Y-m-d H:i:s" );
				$arr [] = $sdate;
				$arr [] = $edate;
			}
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
			// if ($yesterday == $sdate) {
			// $sdate = date ( "Y-m-d" );
			// }
			$arr [] = $sdate;
			$arr [] = $edate;
		}
		return $arr;
	}
	
	/**
	 * 告警描述
	 *
	 * @param unknown $param        	
	 */
	public function get_alarm_comment($threshold, $itemname, $iunit, $operator_type) {
		$comment = "";
		switch ($operator_type) {
			case 'gt' :
				$comment = $itemname . "大于" . $threshold . $iunit;
				break;
			case 'lt' :
				$comment = $itemname . "小于" . $threshold . $iunit;
				break;
			default :
				$comment = $itemname . "等于" . $threshold . $iunit;
		}
		return $comment;
	}
}