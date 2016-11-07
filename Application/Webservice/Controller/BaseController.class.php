<?php

namespace Webservice\Controller;

use Think\Controller;

class BaseController extends Controller {
	public function index() {
	}
	
	/**
	 * Rrd操作
	 */
	private function CreateRrd($name, $step, $ds_name) {
		$flag = 0;
		$filename = C ( "rrd_dir" ) . $name . ".rrd";
		system ( "/usr/bin/rrdtool  create $filename  --start -32092970 --step $step DS:$ds_name:GAUGE:600:U:U RRA:AVERAGE:0.5:1:210240 RRA:AVERAGE:0.5:12:17520 RRA:AVERAGE:0.5:288:730 RRA:AVERAGE:0.5:2016:104 RRA:AVERAGE:0.5:8640:24  RRA:MAX:0.5:1:210240  RRA:MAX:0.5:12:17520  RRA:MAX:0.5:288:730  RRA:MAX:0.5:2016:104   RRA:MAX:0.5:8640:24 ", $status );
		if (($status == 0) and (file_exists ( $filename ))) {
			$flag = 1;
		}
		return $flag;
	}
	private function UpdateRrd($name, $ds_name, $data) {
		$flag = 0;
		$filename = C ( "rrd_dir" ) . $name . ".rrd";
		
		if (! file_exists ( $filename )) {
			$r = $this->CreateRrd ( $name, '300', $ds_name );
			if ($r != 1) {
				return "error:create";
			}
		}
		
		$data = time () . ":" . $data;
		$c = "/usr/bin/rrdtool update  $filename  --template  $ds_name  $data";
		system ( $c, $status );
		if ($status == 0) {
			return "OK";
		} else {
			return "ERROR:update:" . $c;
		}
	}
	private function format_rddname($tid, $uid, $mid, $sid, $ssid, $itemid) {
		return $tid . "_" . $uid . "_" . $mid . "_" . $sid . "_" . $ssid . "_" . $itemid;
	}

/**
 * Rrd操作END
 */
}