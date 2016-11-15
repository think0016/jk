<?php

namespace Jk\Controller;

class HttpViewController extends MonitorController {
	private $sid = 1;
	private $showitem = array (
			"status" => 8,
			"connecttime" => 2 
	);
	public function index() {
		// 检查登录情况
		$this->is_login ( 1 );
		
		$taskid = I ( 'get.tid' );
		$sel = I ( 'get.sel' );
		$itemid = I ( 'get.itemid' );
		
		if ($taskid == "") {
			$this->error ( "参数错误1" );
		}
		
		// $sid = 1;
		// $pointModel = D ( 'jk_monitorypoint' );
		$taskModel = D ( 'jk_task' );
		$taskdetailsModel = D ( 'jk_taskdetails_' . $this->sid );
		$taskdetailsAdvModel = D ( 'jk_taskdetails_adv_' . $this->sid );
		
		$task = $taskModel->where ( array (
				"id" => $taskid,
				"is_del" => 0 
		) )->find ();
		
		if (! $task) {
			$this->error ( "参数错误2" );
		}
		
		$taskdetails = $taskdetailsModel->where ( array (
				"taskid" => $taskid 
		) )->find ();
		$taskdetailsadv = $taskdetailsAdvModel->where ( array (
				"taskid" => $taskid 
		) )->find ();
		
		// 最慢排名表单
		$stime = date ( "Y-m-d" );
		$etime = date ( "Y-m-d H:i:s" );
		$step = 3600;
		if ($sel == "w") {
			$stime = date ( "Y-m-d", strtotime ( "-1 weeks" ) );
			$step = 3600 * 24;
		} else if ($sel == "yd") {
			$stime = date ( "Y-m-d", strtotime ( "-1 day" ) );
			$etime = date ( "Y-m-d" );
			$step = 3600 * 24;
		}
		if ($itemid == "") {
			$itemid = "connecttime"; // 默认响应时间
				                         // $itemid = "status"; // 默认响应时间
		}
		$mids = $task ['mids'];
		$uid = $task ['uid'];
		$mids_arr = explode ( ",", $mids );
		$ssid = $task ['ssid'];
		$itemid = $this->showitem [$itemid];
		
		$result1 = array (); // 地区最慢
		$result2 = array (); // 运营商最慢
		foreach ( $mids_arr as $val ) {
			$mid = str_replace ( ":", "", $val );
			$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid );
			// $sdate = strtotime($stime);
			// $edate = strtotime($etime);
			$rs = $this->rrd_avg ( $rrdfilename, $stime, $etime, 3600 );
			$list = $this->getMonitoryPoint ( $mid );
			
			$province = $list ['province'];
			if (isset ( $result1 [$operator] )) {
				$result1 [$province] = ($rs [0] + $result1 [$province]) / 2;
			} else {
				$result1 [$province] = $rs [0];
			}
			$operator = $list ['operator'];
			if (isset ( $result2 [$operator] )) {
				$result2 [$operator] = ($rs [0] + $result2 [$operator]) / 2;
			} else {
				$result2 [$operator] = $rs [0];
			}
			
			// $marr =array();
			// foreach ($rs as $val){
			// $temp = explode(" ",$val);
			// $temp[0] = date("Y-m-d H:i:s",$temp[0]);
			// $marr[] = $temp;
			// }
			// $result[$mid][]=$marr;
		};
		print_r ( $result1 );
		print_r ( $result2 );
		exit ();
		$this->assignbase ();
		$this->assign ( "task", $task );
		$this->assign ( "taskdetails", $taskdetails );
		$this->assign ( "taskdetailsadv", $taskdetailsadv );
		$this->display ();
	}
}