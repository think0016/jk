<?php

namespace Jk\Controller;

class FtpViewController extends MonitorController {
	private $sid = 6;
	private $defaultitem = "responsetime";
	private $showitem = array (
			"status" => 2,
			//"connecttime" => 2,
			"responsetime" => 1
	);
	private $showitemunit = array (
			"2" => '%',
			"1" => '毫秒' 
	);
	public function index() {
		// 检查登录情况
		$this->is_login ( 1 );
		
		$taskid = I ( 'get.tid' );
		$itemid = I ( 'get.itemid' );
		$stime = I ( 'get.sdate' );
		$etime = I ( 'get.edate' );
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
		$setime = $this->timeinterval ( $stime, $etime );
		$stime = $setime [0];
		$etime = $setime [1];
		$step = 3600;
		if ($itemid == "") {
			$itemid = $this->defaultitem; // 默认响应时间
			// $itemid = "status"; // 默认响应时间
		}
		$mids = $task ['mids'];
		$uid = $task ['uid'];
		$mids_arr = explode ( ",", $mids );
		$ssid = $task ['ssid'];
		$itemid = $this->showitem [$itemid];
		
		$result1 = array (); // 地区最慢
		$result2 = array (); // 运营商最慢
		$cn = array (); // 计数器
		$total = 0;
		$n = 0;
		foreach ( $mids_arr as $val ) {
			$n ++;
			$mid = str_replace ( ":", "", $val );
			if ($itemid == $this->showitem['status']) {
				$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid, 1 );
			} else {
				$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid );
			}
			
			$rs = $this->rrd_avg ( $rrdfilename, $stime, $etime, $step );
			
			
			$list = $this->getMonitoryPoint ( $mid );
			$total += $rs [0];
			
			$province = $list ['province'];
			if (isset ( $result1 [$operator] )) {
				$result1 [$province] = $rs [0] + $result1 [$province];
				$cn [0] [$province] = $cn [0] [$province] + 1;
			} else {
				$result1 [$province] = $rs [0];
				$cn [0] [$province] = 1;
			}
			
			$operator = $list ['operator'];
			if (isset ( $result2 [$operator] )) {
				$result2 [$operator] = $rs [0] + $result2 [$operator];
				$cn [1] [$operator] = $cn [1] [$operator] + 1;
			} else {
				$result2 [$operator] = $rs [0];
				$cn [1] [$operator] = 1;
			}
		}
		
		// 算平均
		foreach ( $result1 as $k => $v ) {
			$result1 [$k] = ( int ) ($v / $cn [0] [$k]);
		}
		foreach ( $result2 as $k => $v ) {
			$result2 [$k] = ( int ) ($v / $cn [1] [$k]);
		}
		
		// 排序
		arsort ( $result1 );
		arsort ( $result2 );
		foreach ( $result1 as $k => $v ) {
			$temp ['name'] = $k;
			$temp ['value'] = $v;
			$result1 [] = $temp;
			unset ( $result1 [$k] );
		}
		foreach ( $result2 as $k => $v ) {
			$temp [0] = $k;
			$temp [1] = $v;
			$result2 [] = $temp;
			unset ( $result2 [$k] );
		}
		$mapdata = addslashes ( json_encode ( $result1 ) );
		
		// print_r($stime);
		// print_r($etime);
		// exit ();
		
		// 告警查询
		/**
		$alarmsModel = D ( "jk_alarms_list" );
		$alarmslist = $alarmsModel->join ( 'jk_trigger_ruls ON jk_trigger_ruls.id = jk_alarms_list.trigger_id' )->where ( array (
				"jk_alarms_list.task_id" => $taskid 
		) )->order ( 'jk_alarms_list.id desc' )->limit ( 10 )->select ();
		
		$alarmdatas = array ();
		$alarmsnum = 0; // 警报数量( 暂时用于提示)
		
		for($i = 0; $i < count ( $alarmslist ); $i ++) {
			$temp = array ();
			
			$alarm_type = $alarmslist [$i] ['type'];
			$alarm_itemid = $alarmslist [$i] ['index_id'];
			$operator_type = $alarmslist [$i] ['operator_type'];
			$threshold = $alarmslist [$i] ['threshold'];
			// $ltime = abs ( $alarmslist [$i] ['times'] - $alarmslist [$i] ['times'] );
			if ($alarm_itemid == 2) { // 时延
				switch ($operator_type) {
					case 'gt' :
						$temp ['msg'] = "时延大于" . $threshold . "毫秒";
						break;
					case 'lt' :
						$temp ['msg'] = "时延小于" . $threshold . "毫秒";
						break;
					default :
						$temp ['msg'] = "时延等于" . $threshold . "毫秒";
				}
			} else if ($alarm_itemid == 8) {
				switch ($operator_type) {
					case 'gt' :
						$temp ['msg'] = "可用性大于" . $threshold . "%";
						break;
					case 'lt' :
						$temp ['msg'] = "可用性小于" . $threshold . "%";
						break;
					default :
						$temp ['msg'] = "可用性等于" . $threshold . "%";
				}
			}
			
			$temp ['stime'] = date ( "Y-m-d H:i:s", $alarmslist [$i] ['times'] ); // 告警时间
			
			if ($alarm_type == 0) {
				$temp ['status'] = "<span class=\"label label-warning\">故障</span>"; // 当前状态
				$ltime = "";
				if ($i == 0) {
					$ltime = abs ( time () - $alarmslist [$i] ['times'] );
				} else {
					$i2 = $i - 1;
					$ltime = abs ( $alarmslist [$i2] ['times'] - $alarmslist [$i] ['times'] );
				}
				$temp ['ltime'] = changeTimeType ( $ltime ); // 持续时间
			} else {
				$temp ['ltime'] = ""; // 持续时间
				$temp ['status'] = "<span class=\"label label-info\">恢复</span>"; // 当前状态
			}
			$alarmdatas [] = $temp;
		}
		**/
		// print_r ( $alarmdatas );
		// exit ();
		$this->assignbase ();
		$this->assign ( "task", $task );
		$this->assign ( "taskdetails", $taskdetails );
		$this->assign ( "taskdetailsadv", $taskdetailsadv );
		$this->assign ( "result1", $result1 );
		$this->assign ( "result2", $result2 );
		$this->assign ( "total_avg", $n == 0 ? 0 : ( int ) ($total / $n) );
		$this->assign ( "mapdata", $mapdata );
		$this->assign ( "sdate", $stime );
		$this->assign ( "edate", $etime );
		$this->assign ( "itemid", $itemid );
		$this->assign ( "unit", $this->showitemunit [$itemid] );
		$this->assign ( "step", $step );
		//$this->assign ( "sel", $sel );
		//$this->assign ( "alarmdata", $alarmdatas );
		//$this->assign ( "alarmsnum", $alarmsnum );
		$this->display ();
	}
	
	/**
	 * 响应时间INDEX
	 */
	public function ctindex() {
		$taskid = I ( 'get.tid' );
		$stime = I ( 'get.sdate' );
		$etime = I ( 'get.edate' );
		
		if ($taskid == "") {
			$this->error ( "参数错误1" );
		}
		
		// if ($stime == "" || $etime == "") {
		// $stime = date ( "Y-m-d 00:00:00" );
		// $etime = date ( "Y-m-d H:i:s" );
		// }
		
		$setime = $this->timeinterval ( $stime, $etime );
		$stime = $setime [0];
		$etime = $setime [1];
		
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
		
		$step = 3600;
		$mids = $task ['mids'];
		$uid = $task ['uid'];
		$mids_arr = explode ( ",", $mids );
		$ssid = $task ['ssid'];
		// $itemid = $this->showitem ["connecttime"];
		$item_arr = array (
				"2" => "响应时间"
		);
		
		$result1 = array (); // 地区最慢
		$result2 = array (); // 运营商最慢
		$cn = array (); // 计数器
		$total = array ();
		$n = 0;
		foreach ( $mids_arr as $val ) {
			$n ++;
			$mid = str_replace ( ":", "", $val );
			$list = $this->getMonitoryPoint ( $mid );
			$province = $list ['province'];
			$operator = $list ['operator'];
			
			// $temp = array();
			$item_arr_num = 0;
			foreach ( $item_arr as $itemid => $itemname ) {
				$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid );
				
				$rs = $this->rrd_avg ( $rrdfilename, $stime, $etime, $step );
				
				$total [$itemid] += $rs [0];
				
				// 监控次数(稍后添加)
				// if($item_arr_num == 0){
				// $rs1 = $this->rrd_avg ( $rrdfilename, $stime, $etime, $step );
				// $total["监控次数"] += count($rs1);
				
				// }
				
				// $province = $list ['province'];
				if (isset ( $result1 [$operator] )) {
					$result1 [$province] [$itemid] = $rs [0] + $result1 [$province] [$itemid];
					$cn [0] [$province] [$itemid] = $cn [0] [$province] [$itemid] + 1;
				} else {
					$result1 [$province] [$itemid] = $rs [0];
					$cn [0] [$province] [$itemid] = 1;
				}
				
				// $operator = $list ['operator'];
				if (isset ( $result2 [$operator] [$itemid] )) {
					$result2 [$operator] [$itemid] = $rs [0] + $result2 [$operator] [$itemid];
					$cn [1] [$operator] [$itemid] = $cn [1] [$operator] [$itemid] + 1;
				} else {
					$result2 [$operator] [$itemid] = $rs [0];
					$cn [1] [$operator] [$itemid] = 1;
				}
			}
		}
		
		// 算平均
		foreach ( $result1 as $k => $v ) {
			$temp = $result1 [$k];
			foreach ( $temp as $itemid => $itemvalue ) {
				$temp [$itemid] = ( int ) ($itemvalue / $cn [0] [$k] [$itemid]);
			}
		}
		foreach ( $result2 as $k => $v ) {
			$temp = $result2 [$k];
			foreach ( $temp as $itemid => $itemvalue ) {
				$temp [$itemid] = ( int ) ($itemvalue / $cn [0] [$k] [$itemid]);
			}
		}
		foreach ( $total as $key => $value ) {
			$total ['p'] [$key] = ( int ) ($value / $n);
			$total ['o'] [$key] = ( int ) ($value / count ( $result2 ));
		}
		
		// 排序
		// arsort ( $result1 );
		// arsort ( $result2 );
		$result_map = array ();
		foreach ( $result1 as $k => $v ) {
			$temp = array ();
			$temp ['name'] = $k;
			$temp ['value'] = $v;
			$result1 [] = $temp;
			$maptemp = array ();
			$maptemp ['name'] = $k;
			$maptemp ['value'] = $v [2];
			$result_map [] = $maptemp;
			unset ( $result1 [$k] );
		}
		foreach ( $result2 as $k => $v ) {
			$temp = array ();
			$temp ['name'] = $k;
			$temp ['value'] = $v;
			$result2 [] = $temp;
			unset ( $result2 [$k] );
		}
		
		// print_r ( $result1 );
		// print_r ( $result2 );
		// print_r ( $total );
		// exit ();
		
		// $mapdata = addslashes ( json_encode ( $result_map ) );
		
		$this->assignbase ();
		$this->assign ( "task", $task );
		$this->assign ( "taskdetails", $taskdetails );
		$this->assign ( "taskdetailsadv", $taskdetailsadv );
		$this->assign ( "result1", $result1 );
		$this->assign ( "result2", $result2 );
		$this->assign ( "result_tongji", $total );
		$this->assign ( "mapdata", addslashes ( json_encode ( $result_map ) ) );
		$this->assign ( "sdate", $stime );
		$this->assign ( "edate", $etime );
		$this->assign ( "itemid", $this->showitem ['connecttime'] );
		
		$this->display ();
	}
	
	/**
	 * 可用率INDEX
	 */
	public function stindex() {
		// 检查登录情况
		$this->is_login ( 1 );
		
		$taskid = I ( 'get.tid' );
		$stime = I ( 'get.sdate' );
		$etime = I ( 'get.edate' );
		
		if ($taskid == "") {
			$this->error ( "参数错误1" );
		}
		
		$setime = $this->timeinterval ( $stime, $etime );
		$stime = $setime [0];
		$etime = $setime [1];
		
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
		$step = 3600;
		
		$itemid = "status"; // 默认响应时间
		$mids = $task ['mids'];
		$uid = $task ['uid'];
		$mids_arr = explode ( ",", $mids );
		$ssid = $task ['ssid'];
		$itemid = $this->showitem [$itemid];
		
		$result1 = array (); // 地区最慢
		$result2 = array (); // 运营商最慢
		$cn = array (); // 计数器
		$total = 0;
		$n = 0;
		foreach ( $mids_arr as $val ) {
			$n ++;
			$mid = str_replace ( ":", "", $val );
			if ($itemid == $this->showitem['status']) {
				$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid, 1 );
			} else {
				$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid );
			}
			
			$rs = $this->rrd_avg ( $rrdfilename, $stime, $etime, $step );
			$list = $this->getMonitoryPoint ( $mid );
			$total += $rs [0];
			
			$province = $list ['province'];
			if (isset ( $result1 [$operator] )) {
				$result1 [$province] = $rs [0] + $result1 [$province];
				$cn [0] [$province] = $cn [0] [$province] + 1;
			} else {
				$result1 [$province] = $rs [0];
				$cn [0] [$province] = 1;
			}
			
			$operator = $list ['operator'];
			if (isset ( $result2 [$operator] )) {
				$result2 [$operator] = $rs [0] + $result2 [$operator];
				$cn [1] [$operator] = $cn [1] [$operator] + 1;
			} else {
				$result2 [$operator] = $rs [0];
				$cn [1] [$operator] = 1;
			}
		}
		
		// 算平均
		foreach ( $result1 as $k => $v ) {
			$result1 [$k] = ( int ) ($v / $cn [0] [$k]);
		}
		foreach ( $result2 as $k => $v ) {
			$result2 [$k] = ( int ) ($v / $cn [1] [$k]);
		}
		
		// 排序
		arsort ( $result1 );
		arsort ( $result2 );
		foreach ( $result1 as $k => $v ) {
			$temp ['name'] = $k;
			$temp ['value'] = $v;
			$result1 [] = $temp;
			unset ( $result1 [$k] );
		}
		foreach ( $result2 as $k => $v ) {
			$temp [0] = $k;
			$temp [1] = $v;
			$result2 [] = $temp;
			unset ( $result2 [$k] );
		}
		$mapdata = addslashes ( json_encode ( $result1 ) );
		
		// print_r($stime);
		// print_r($etime);
		// exit ();
		
		// 告警查询
		$alarmsModel = D ( "jk_alarms_list" );
		$alarmslist = $alarmsModel->join ( 'jk_trigger_ruls ON jk_trigger_ruls.id = jk_alarms_list.trigger_id' )->where ( array (
				"jk_alarms_list.task_id" => $taskid 
		) )->order ( 'jk_alarms_list.id desc' )->limit ( 10 )->select ();
		
		$alarmdatas = array ();
		$alarmsnum = 0; // 警报数量( 暂时用于提示)
		
		for($i = 0; $i < count ( $alarmslist ); $i ++) {
			$temp = array ();
			
			$alarm_type = $alarmslist [$i] ['type'];
			$alarm_itemid = $alarmslist [$i] ['index_id'];
			$operator_type = $alarmslist [$i] ['operator_type'];
			$threshold = $alarmslist [$i] ['threshold'];
			// $ltime = abs ( $alarmslist [$i] ['times'] - $alarmslist [$i] ['times'] );
			if ($alarm_itemid == $this->showitem['responsetime']) { // 时延
				switch ($operator_type) {
					case 'gt' :
						$temp ['msg'] = "时延大于" . $threshold . "毫秒";
						break;
					case 'lt' :
						$temp ['msg'] = "时延小于" . $threshold . "毫秒";
						break;
					default :
						$temp ['msg'] = "时延等于" . $threshold . "毫秒";
				}
			} else if ($alarm_itemid == $this->showitem['status']) {
				switch ($operator_type) {
					case 'gt' :
						$temp ['msg'] = "可用性大于" . $threshold . "%";
						break;
					case 'lt' :
						$temp ['msg'] = "可用性小于" . $threshold . "%";
						break;
					default :
						$temp ['msg'] = "可用性等于" . $threshold . "%";
				}
			}
			
			$temp ['stime'] = date ( "Y-m-d H:i:s", $alarmslist [$i] ['times'] ); // 告警时间
			
			if ($alarm_type == 0) {
				$temp ['status'] = "<span class=\"label label-warning\">故障</span>"; // 当前状态
				$ltime = "";
				if ($i == 0) {
					$ltime = abs ( time () - $alarmslist [$i] ['times'] );
				} else {
					$i2 = $i - 1;
					$ltime = abs ( $alarmslist [$i2] ['times'] - $alarmslist [$i] ['times'] );
				}
				$temp ['ltime'] = changeTimeType ( $ltime ); // 持续时间
			} else {
				$temp ['ltime'] = ""; // 持续时间
				$temp ['status'] = "<span class=\"label label-info\">恢复</span>"; // 当前状态
			}
			$alarmdatas [] = $temp;
		}
		
		// print_r ( $alarmdatas );
		// exit ();
		$this->assignbase ();
		$this->assign ( "task", $task );
		$this->assign ( "taskdetails", $taskdetails );
		$this->assign ( "taskdetailsadv", $taskdetailsadv );
		$this->assign ( "result1", $result1 );
		$this->assign ( "result2", $result2 );
		$this->assign ( "total_avg", $n == 0 ? 0 : ( int ) ($total / $n) );
		$this->assign ( "mapdata", $mapdata );
		$this->assign ( "sdate", $stime );
		$this->assign ( "edate", $etime );
		$this->assign ( "itemid", $itemid );
		$this->assign ( "unit", $this->showitemunit [$itemid] );
		$this->assign ( "step", $step );
		$this->assign ( "alarmdata", $alarmdatas );
		$this->assign ( "alarmsnum", $alarmsnum );
		$this->display ();
	}
	
	/**
	 * 告警记录INDEX
	 */
	public function alarmindex() {
		$this->is_login ( 1 );
		
		$taskid = I ( 'get.tid' );
		$stime = I ( 'get.sdate' );
		$etime = I ( 'get.edate' );
		if ($taskid == "") {
			$this->error ( "参数错误1" );
		}
		
		$setime = $this->timeinterval ( $stime, $etime , "w");
		$stime = $setime [0];
		$etime = $setime [1];
		
		$taskModel = D ( 'jk_task' );
		$taskdetailsModel = D ( 'jk_taskdetails_' . $this->sid );
		$taskdetailsAdvModel = D ( 'jk_taskdetails_adv_' . $this->sid );
		$triggerModel = D ( 'jk_trigger_ruls' );
		$task = $taskModel->where ( array (
				"id" => $taskid,
				"is_del" => 0 
		) )->find ();
		
		if (! $task) {
			$this->error ( "参数错误2" );
		}
		
		$triggerlist = $triggerModel->join ( "jk_taskitem_" . $this->sid . " ON jk_trigger_ruls.index_id=" . "jk_taskitem_" . $this->sid . ".itemid" )->where ( array (
				"jk_trigger_ruls.task_id" => $taskid 
		) )->select ();
		
		for($i = 0; $i < count ( $triggerlist ); $i ++) {
			$triggerlist [$i] ["alarmcomment"] = $this->get_alarm_comment ( $triggerlist [$i] ["threshold"], $triggerlist [$i] ["comment"], $triggerlist [$i] ["iunit"], $triggerlist [$i] ["operator_type"] );
		}
		
		$this->assignbase ();
		$this->assign ( "sdate", $stime );
		$this->assign ( "edate", $etime );
		$this->assign ( "task", $task );
		$this->assign ( "triggerlist", $triggerlist );
		$this->display ();
	}
	
	public function getalarmtabledata() {
		if (! $this->is_login ( 0 )) {
		}
		
		$taskid = I ( 'get.tid' );
		$aid = I ( 'get.aid' );
		$stime = I ( 'get.sdate' );
		$etime = I ( 'get.edate' );
		$limit = I ( 'get.limit' );
		
		if($limit=="" || $limit==0){
			$limit = 1000;
		}
		
		//$setime = $this->timeinterval ( $stime, $etime, "w" );
		$setime = $this->timeinterval ( $stime, $etime  );
		$stime = strtotime ( $setime [0] );
		$etime = strtotime ( $setime [1] );
		
		$taskModel = D ( 'jk_task' );
		$taskdetailsModel = D ( 'jk_taskdetails_' . $this->sid );
		// $taskdetailsAdvModel = D ( 'jk_taskdetails_adv_' . $this->sid );
		$triggerModel = D ( 'jk_trigger_ruls' );
		
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
		
		
		$triggerlist = array ();
		if ($aid == 0 || $aid == "") { // 全部告警信息
			$triggerlist = $triggerModel->join ( "jk_taskitem_" . $this->sid . " ON jk_trigger_ruls.index_id=" . "jk_taskitem_" . $this->sid . ".itemid" )->where ( array (
					"jk_trigger_ruls.task_id" => $taskid 
			) )->limit($limit)->select ();
		} else {
			$triggerlist = $triggerModel->join ( "jk_taskitem_" . $this->sid . " ON jk_trigger_ruls.index_id=" . "jk_taskitem_" . $this->sid . ".itemid" )->where ( array (
					"jk_trigger_ruls.task_id" => $taskid,
					"jk_trigger_ruls.id" => $aid 
			) )->limit($limit)->select ();
		}

		for($i = 0; $i < count ( $triggerlist ); $i ++) {
			$triggerlist [$i] ["alarmcomment"] = $this->get_alarm_comment ( $triggerlist [$i] ["threshold"], $triggerlist [$i] ["comment"], $triggerlist [$i] ["iunit"], $triggerlist [$i] ["operator_type"] );
		}
		
		$result = array ();
		
		foreach ( $triggerlist as $val ) {
			$map = array ();
			$map ['jk_alarms_list.task_id'] = $taskid;
			$map ['jk_alarms_list.times'] = array (
					array (
							"GT",
							$stime 
					),
					array (
							"LT",
							$etime 
					) 
			);
			$map ['jk_alarms_list.trigger_id'] = $val ['id'];
			
			$alarmsModel = D ( "jk_alarms_list" );
			$alarmslist = $alarmsModel->where ( $map )->order ( 'jk_alarms_list.times desc' )->select ();
			
			// echo $alarmsModel->getLastSql();
			
			for($i = 0; $i < count ( $alarmslist ); $i ++) {
				if ($alarmslist [$i] ['type'] == 0) {
					$letime = time ();
					if ($i > 0) {
						$i2 = $i - 1;
						$letime = $alarmslist [$i2] ['times'];
					}
					$alarmslist [$i] ['ltime'] = changeTimeType ( abs ( $letime - $alarmslist [$i] ['times'] ) );
				}else{
					$alarmslist [$i] ['ltime'] = '';
				}
				$alarmslist [$i] ['alarmcomment'] = $val ['alarmcomment'];
				$alarmslist [$i] ['atime'] = date ( "Y年m月d日 H:i:s", $alarmslist [$i] ['times'] );
				// $return [$alarmslist [$i] ['id']] = $alarmslist [$i];
			}
			
			$result = array_merge ( $result, $alarmslist );
		}
		usort($result, array("HttpViewController","alarm_sort"));
		
		$return = array();

		for ($i = 0; $i < count($result); $i++) {
			$temp = array();
			$temp[] = $i+1;
			$temp[] = $result[$i]['atime'];//监控时间
			$temp[] = $task['title'];//监控任务名
			$temp[] = $taskdetails['target'];//监控对象
			$temp[] = 'http';//监控类型
			$temp[] = $result[$i]['alarmcomment'];//事件信息
			$temp[] = $result[$i]['ltime'];//持续时间
			if($result[$i]['type']==1){
				$temp[] = '恢复';//持续时间
			}else{
				$temp[] = '故障';//持续时间
			}
			$return[] = $temp;
		}
		$return = array_slice($return,0,$limit);
		echo json_encode($return);
	}
	public function getlinedatax() {
		if (! $this->is_login ( 0 )) {
			exit ( "ERROR1" );
		}
		
		$taskid = I ( 'post.tid' );
		$stime = I ( 'post.sdate' );
		$etime = I ( 'post.edate' );
		$step = I ( 'post.step' );
		
		if ($taskid == "" || $stime == "" || $etime == "" || $step == "") {
			exit ( "ERROR2" );
		}
		// $taskid = 78;
		// $stime = '2016-11-16 00:00:00';
		// $etime = '2016-11-16 17:04:24';
		// $step = 3600;
		
		$taskModel = D ( 'jk_task' );
		
		$task = $taskModel->where ( array (
				"id" => $taskid,
				"is_del" => 0 
		) )->find ();
		
		if (! $task) {
			$this->error ( "no task" );
		}
		
		$return = array ();
		$mids = $task ['mids'];
		$uid = $task ['uid'];
		$mids_arr = explode ( ",", $mids );
		$ssid = $task ['ssid'];
		
		// echo "开始时间:".strtotime($stime).PHP_EOL;
		// echo "开始时间:".strtotime($etime).PHP_EOL;
		// echo "间隔:".$step.PHP_EOL;
		
		// 响应时间的
		// 获取监控点集合
		$rrdfilename = array ();
		foreach ( $mids_arr as $val ) {
			$mid = str_replace ( ":", "", $val );
			$x = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $this->showitem['responsetime'] );
			$rrdfilename [] = $x;
		}
		
		$rs1 = $this->rrd_get_m ( $rrdfilename, $stime, $etime, $step );
		// var_dump($rsx);
		
		// 可用率
		// 获取监控点集合
		$rrdfilename = array ();
		foreach ( $mids_arr as $val ) {
			$mid = str_replace ( ":", "", $val );
			$x = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $this->showitem['status'], 1 );
			$rrdfilename [] = $x;
		}
		$rs2 = $this->rrd_get_m ( $rrdfilename, $stime, $etime, $step );
		
		$xv = array ();
		$yv1 = array ();
		$yv2 = array ();
		
		for($i = 0; $i < count ( $rs1 ); $i ++) {
			$val1 = explode ( " ", $rs1 [$i] );
			$val2 = explode ( " ", $rs2 [$i] );
			
			$xv [] = date ( "Y年m月d日  H时i分", $val1 [0] );
			$yv1 [] = $val1 [1];
			$yv2 [] = $val2 [1];
		}
		$return ['xv'] = $xv;
		$return ['yv1'] = $yv1;
		$return ['yv2'] = $yv2;
		

		echo json_encode ( $return );
	}
	
	public function getlinedata() {
		if (! $this->is_login ( 0 )) {
			exit ( "ERROR1" );
		}
		
		$taskid = I ( 'post.tid' );
		$stime = I ( 'post.sdate' );
		$etime = I ( 'post.edate' );
		$step = I ( 'post.step' );
		$itemid = I ( 'post.itemid' );
		$lb = I ( 'post.lb' ); // 线/柱
		$po = I ( 'post.po' ); // 省份/运营商
		
		$taskModel = D ( 'jk_task' );
		
		$task = $taskModel->where ( array (
				"id" => $taskid,
				"is_del" => 0 
		) )->find ();
		
		if (! $task) {
			$this->error ( "no task" );
		}
		
		$return = array ();
		$mids = $task ['mids'];
		$uid = session ( "uid" );
		$mids_arr = explode ( ",", $mids );
		$ssid = $task ['ssid'];
		
		// echo "开始时间:".strtotime($stime).PHP_EOL;
		// echo "开始时间:".strtotime($etime).PHP_EOL;
		// echo "间隔:".$step.PHP_EOL;
		
		$return = array ();
		$opclass = array (); // 先分类(同一类别可能有多个mid)
		foreach ( $mids_arr as $k => $v ) {
			$mid = str_replace ( ":", "", $v );
			$mname = "";
			if ($po == 1) {
				$mname = $this->getMonitoryPoint ( $mid )['province'];
			} else {
				$mname = $this->getMonitoryPoint ( $mid )['operator'];
			}
			$rrdfilename = "";
			if ($itemid == $this->showitem['responsetime']) {
				$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $this->showitem['responsetime'] );
			} else if ($itemid == $this->showitem['status']) {
				$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $this->showitem['status'], 1 );
			}
			
			$opclass [$mname] [] = $rrdfilename;
		}
		
		// 开始做数据
		$ii = 0;
		$xv = array ();
		$yv = array ();
		$series = array ();
		$legend = array ();
		foreach ( $opclass as $mname => $filename ) {
			$rs1 = $this->rrd_get_m ( $filename, $stime, $etime, $step );
			
			$legend [] = $mname;
			$yv = array ();
			for($i = 0; $i < count ( $rs1 ); $i ++) {
				$val1 = explode ( " ", $rs1 [$i] );
				if ($ii == 0) {
					$xv [] = date ( "Y年m月d日  H时i分", $val1 [0] );
				}
				
				$yv [] = $val1 [1];
			}
			
			$temp ['name'] = $mname;
			$temp ['type'] = 'line';
			$temp ['smooth'] = true;
			// smoothMonotone
			$temp ['smoothMonotone'] = "x";
			$temp ['data'] = $yv;
			
			$series [] = $temp;
			$ii ++;
		}
		
		$return ['legend'] = $legend;
		$return ['xv'] = $xv;
		$return ['series'] = $series;
		
		echo json_encode ( $return );
	}
	
	public function getbardata() {
		if (! $this->is_login ( 0 )) {
			exit ( "ERROR1" );
		}
		
		$taskid = I ( 'post.tid' );
		$stime = I ( 'post.sdate' );
		$etime = I ( 'post.edate' );
		$step = I ( 'post.step' );
		$itemid = I ( 'post.itemid' );
		$lb = I ( 'post.lb' ); // 线/柱
		$po = I ( 'post.po' ); // 省份/运营商
		
		$taskModel = D ( 'jk_task' );
		
		$task = $taskModel->where ( array (
				"id" => $taskid,
				"is_del" => 0 
		) )->find ();
		
		if (! $task) {
			$this->error ( "no task" );
		}
		
		$return = array ();
		$mids = $task ['mids'];
		$uid = session ( "uid" );
		$mids_arr = explode ( ",", $mids );
		$ssid = $task ['ssid'];
		
		$return = array ();
		
		// 开始做数据
		// 先声明需要显示的指标项
		$itemarr = array ();
		$itemarr [0] = array (
				"id" => 2,
				"name" => "建立连接时间" 
		);
		$itemarr [1] = array (
				"id" => 7,
				"name" => "DNS解析时间" 
		);
		$itemarr [2] = array (
				"id" => 4,
				"name" => "内容下载时间" 
		);
		$legend = array (
				$itemarr [0] ['name'],
				$itemarr [1] ['name'],
				$itemarr [2] ['name'] 
		);
		
		$ii = 0;
		$series = array ();
		$xv = array ();
		// $yv_avg = array();
		foreach ( $itemarr as $iv ) {
			
			$opclass = array (); // 先分类(同一类别可能有多个mid)
			foreach ( $mids_arr as $k => $v ) {
				$mid = str_replace ( ":", "", $v );
				$mname = "";
				if ($po == 1) {
					$mname = $this->getMonitoryPoint ( $mid )['province'];
				} else {
					$mname = $this->getMonitoryPoint ( $mid )['operator'];
				}
				$rrdfilename = "";
				$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $iv ['id'] );
				
				$opclass [$mname] [] = $rrdfilename;
			}
			
			$yv = array ();
			$temp = array ();
			
			if ($ii == 0) {
				// $xv [] = "平均";
				// //$yv[] = ((int)array_sum($yv)/count($yv));
				// $temp ['name'] = $iv['name'];
				// $temp ['type'] = 'bar';
				// $temp ['label'] = array("normal"=>array("show"=>true,"position"=>'insideRight'));
				// $temp ['stack'] = '总量';
				// $temp ['data'] = $yv;
				// $series[]=$temp;
			}
			
			foreach ( $opclass as $mname => $filename ) {
				$rs1 = 0;
				foreach ( $filename as $fname ) {
					$rs1 += $this->rrd_avg ( $fname, $stime, $etime, $step )[0];
				}
				
				if ($ii == 0) {
					$xv [] = $mname;
				}
				
				$yv [] = ( int ) ($rs1 / count ( $filename ));
			}
			
			$yv_avg = ( int ) (array_sum ( $yv ) / count ( $yv ));
			array_unshift ( $yv, $yv_avg );
			// $series[0]['data']=$yvx;
			
			$temp ['name'] = $iv ['name'];
			$temp ['type'] = 'bar';
			$temp ['label'] = array (
					"normal" => array (
							"show" => true,
							"position" => "insideRight" 
					) 
			);
			$temp ['stack'] = '总量';
			$temp ['data'] = $yv;
			$series [] = $temp;
			$ii ++;
		}
		
		array_unshift ( $xv, "平均" ); // 加上平均列
		$return ['legend'] = $legend;
		$return ['xv'] = $xv;
		$return ['series'] = $series;
		echo json_encode ( $return );
	}
	protected function alarm_sort($a, $b) {
		if ($a['times'] == $b['times']) {
			return 0;
		}		
		return ($a['times'] < $b['times']) ? 1 : - 1;
	}
}