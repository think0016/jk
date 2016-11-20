<?php

namespace Jk\Controller;

class HttpViewController extends MonitorController {
	private $sid = 1;
	private $showitem = array (
			"status" => 8,
			"connecttime" => 2 
	);
	private $showitemunit = array (
			"8" => '%',
			"2" => '毫秒' 
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
		$stime = date ( "Y-m-d 00:00:00" );
		$etime = date ( "Y-m-d H:i:s" );
		$step = 3600;
		if ($sel == "w") { // 上周
			$stime = date ( "Y-m-d", strtotime ( "-1 weeks" ) );
			// $step = 3600 * 24;
		} else if ($sel == "yd") { // 昨天
			$stime = date ( "Y-m-d", strtotime ( "-1 day" ) );
			$etime = date ( "Y-m-d" );
			// $step = 3600 * 24;
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
		$cn = array (); // 计数器
		$total = 0;
		$n = 0;
		foreach ( $mids_arr as $val ) {
			$n ++;
			$mid = str_replace ( ":", "", $val );
			if ($itemid == 8) {
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
				}else{
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
		$this->assign ( "sel", $sel );
		$this->assign ( "alarmdata", $alarmdatas );
		$this->assign ( "alarmsnum", $alarmsnum );
		$this->display ();
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
			$x = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, 2 );
			$rrdfilename [] = $x;
		}
		
		$rs1 = $this->rrd_get_m ( $rrdfilename, $stime, $etime, $step );
		// var_dump($rsx);
		
		// 响应时间的
		// 获取监控点集合
		$rrdfilename = array ();
		foreach ( $mids_arr as $val ) {
			$mid = str_replace ( ":", "", $val );
			$x = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, 8, 1 );
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
		// if ($this->is_login ( 0 )) {
		// exit ("ERROR1");
		// }
		
		// $taskid = I ( 'post.tid' );
		// $sdate = I ( 'post.sdate' );
		// $edate = I ( 'post.edate' );
		// $step = I ( 'post.step' );
		
		// if ($taskid == "" || $sdate == "" || $edate == "" || $step =="") {
		// exit ("ERROR2");
		// }
		$taskid = 78;
		$stime = '2016-11-16 00:00:00';
		$etime = '2016-11-16 17:04:24';
		$step = 3600;
		
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
		
		$mid = str_replace ( ":", "", $mids_arr [0] );
		
		// 响应时间
		$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, 2 );
		$rs1 = $this->rrd_get ( $rrdfilename, $stime, $etime, $step );
		
		$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, 8, 1 );
		$rs2 = $this->rrd_get ( $rrdfilename, $stime, $etime, $step );
		
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
}