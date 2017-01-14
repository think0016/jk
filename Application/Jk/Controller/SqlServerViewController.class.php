<?php

namespace Jk\Controller;

class SqlServerViewController extends MonitorController {
	private $tasktype = 'sqlserver';
	private $sid = 34;
	private $defaultitem = "responsetime";
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
		
		$setime = $this->timeinterval ( $stime, $etime );
		$sdate = $setime [0];
		$edate = $setime [1];
		//$step = 3600;
		$step = $this->getstep($sdate, $edate,$task['frequency']);
		// 取吞吐率值
		// $mids = $task ['mids'];
		// $mid = str_replace ( ":", "", $mids ); // 服务性能只有一个监控点
		// $uid = session ( "uid" );
		// $ssid = $task ['ssid'];
		// $itemid = 2;
		// $rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid );
		// $ttl_avg = $this->rrd_server_avg ( $rrdfilename, $sdate, $edate, 0, $step );
		// $ttl_max = $this->rrd_server_max ( $rrdfilename, $sdate, $edate, 0, $step );
		// // $ttl_avg = floatval($ttl_avg[0])*100;
		// // $ttl_max = floatval($ttl_max[0])*100;
		// $ttl_avg = $ttl_avg [0];
		// $ttl_max = $ttl_max [0];
		// // 取并发连接数值
		// $itemid = 1;
		// $rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid );
		// $ljs_avg = $this->rrd_server_avg ( $rrdfilename, $sdate, $edate, 0, $step );
		// $ljs_max = $this->rrd_server_max ( $rrdfilename, $sdate, $edate, 0, $step );
		
		$this->assignbase ();
		$this->assign ( "task", $task );
		$this->assign ( "taskdetails", $taskdetails );
		$this->assign ( "taskdetailsadv", $taskdetailsadv );
		$this->assign ( "sdate", $sdate );
		$this->assign ( "edate", $edate );
		// $this->assign ( "ttl_avg", $ttl_avg );
		// $this->assign ( "ttl_max", $ttl_max );
		// $this->assign ( "ljs_avg", intval ( $ljs_avg [0] ) );
		// $this->assign ( "ljs_max", intval ( $ljs_max [0] ) );
		// $this->assign ( "itemid", $itemid );
		$this->assign ( "step", $step );
		$this->display ();
	}
	
	/**
	 * 整体概况
	 */
	public function allindex() {
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
	
		$setime = $this->timeinterval ( $stime, $etime );
		$sdate = $setime [0];
		$edate = $setime [1];
		$step = 3600;
	
		// 取吞吐率值
	
		$this->assignbase ();
		$this->assign ( "task", $task );
		$this->assign ( "taskdetails", $taskdetails );
		$this->assign ( "taskdetailsadv", $taskdetailsadv );
		$this->assign ( "sdate", $sdate );
		$this->assign ( "edate", $edate );
		$this->assign ( "step", $step );
		$this->display ();
	}
	
	/**
	 * 整体概况
	 */
	public function dbindex() {
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
	
		$setime = $this->timeinterval ( $stime, $etime );
		$sdate = $setime [0];
		$edate = $setime [1];
		$step = 3600;
	
		// 取吞吐率值
	
		$this->assignbase ();
		$this->assign ( "task", $task );
		$this->assign ( "taskdetails", $taskdetails );
		$this->assign ( "taskdetailsadv", $taskdetailsadv );
		$this->assign ( "sdate", $sdate );
		$this->assign ( "edate", $edate );
		$this->assign ( "step", $step );
		$this->display ();
	}
	
	/**
	 * 详细报表
	 */
	public function details() {
		$this->is_login ( 1 );
		
		// $sdate = I ( 'get.sdate' );
		// $edate = I ( 'get.edate' );
		$taskid = I ( 'get.tid' );
		
		if ($taskid == "") {
			$this->error ( "参数错误1" );
		}
		
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
		
		// 假设时间间隔固定
		$etime = strtotime ( date ( "Y-m-d" ) );
		$stime = $etime - (86400 * 30); // 30天
		$step = 86400;
		
		$this->assignbase ();
		$this->assign ( "sdate", date ( "Y-m-d", $stime ) );
		$this->assign ( "edate", date ( "Y-m-d", $etime ) );
		$this->assign ( "task", $task );
		$this->assign ( "taskdetails", $taskdetails );
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
		
		$setime = $this->timeinterval ( $stime, $etime, "w" );
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
	public function gedetailstabledata() {
		if (! $this->is_login ( 0 )) {
			exit ( "ERROR" );
		}
		
		$taskid = I ( 'get.tid' );
		// $stime = I ( 'get.sdate' );
		// $etime = I ( 'get.edate' );
		$itemid = I ( 'get.itemid' );
		
		if ($taskid == "" || $itemid == "") {
			$this->error ( "参数错误1" );
		}
		
		$taskModel = D ( 'jk_task' );
		
		$task = $taskModel->where ( array (
				"id" => $taskid,
				"is_del" => 0 
		) )->find ();
		
		if (! $task) {
			$this->error ( "参数错误2" );
		}
		$mids = $task ['mids'];
		$mid = str_replace ( ":", "", $mids ); // 服务性能只有一个监控点
		$uid = session ( "uid" );
		$ssid = $task ['ssid'];
		
		// 假设时间间隔固定
		$etime = strtotime ( date ( "Y-m-d" ) );
		$stime = $etime - (86400 * 30); // 30天
		$step = 86400;
		
		$filename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid );
		
		$avg_rs = $this->rrd_server_avg_list ( $filename, $stime, $etime, $step );
		$max_rs = $this->rrd_server_max_list ( $filename, $stime, $etime, $step );
		$min_rs = $this->rrd_server_min_list ( $filename, $stime, $etime, $step );
		
		$return = array ();
		for($i = count ( $avg_rs ) - 1; $i > 0; $i --) {
			// for ($i = 0; $i < count($avg_rs) ; $i++) {
			$row = array ();
			$min_data = 0;
			$avg_data = 0;
			$max_data = 0;
			
			// 算平均
			$val = $avg_rs [$i];
			$mtime = explode ( " ", $val )[0];
			$avg_data = (explode ( " ", $val ) == null) ? 0 : explode ( " ", $val )[1];
			
			// 算最大
			if (isset ( $max_rs [$i] )) {
				$val = $max_rs [$i];
				// $mtime = explode(" ", $val)[0];
				$max_data = (explode ( " ", $val ) == null) ? 0 : explode ( " ", $val )[1];
			}
			
			// 算最小
			if (isset ( $min_rs [$i] )) {
				$val = $min_rs [$i];
				// $mtime = explode(" ", $val)[0];
				$min_data = (explode ( " ", $val ) == null) ? 0 : explode ( " ", $val )[1];
			}
			
			$row [] = date ( "Y年m月d日", $mtime );
			$row [] = $min_data;
			$row [] = $avg_data;
			$row [] = $max_data;
			$return [] = $row;
		}
		
		// var_dump($return);
		echo json_encode ( $return );
	}
	public function getalarmtabledata() {
		if (! $this->is_login ( 0 )) {
			exit ( "ERROR" );
		}
		
		$taskid = I ( 'get.tid' );
		$aid = I ( 'get.aid' );
		$stime = I ( 'get.sdate' );
		$etime = I ( 'get.edate' );
		$limit = I ( 'get.limit' );
		
		if ($limit == "" || $limit == 0) {
			$limit = 1000;
		}
		
		$setime = $this->timeinterval ( $stime, $etime, "w" );
		// $setime = $this->timeinterval ( $stime, $etime );
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
			) )->limit ( $limit )->select ();
		} else {
			$triggerlist = $triggerModel->join ( "jk_taskitem_" . $this->sid . " ON jk_trigger_ruls.index_id=" . "jk_taskitem_" . $this->sid . ".itemid" )->where ( array (
					"jk_trigger_ruls.task_id" => $taskid,
					"jk_trigger_ruls.id" => $aid 
			) )->limit ( $limit )->select ();
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
				} else {
					$alarmslist [$i] ['ltime'] = '';
				}
				$alarmslist [$i] ['alarmcomment'] = $val ['alarmcomment'];
				$alarmslist [$i] ['atime'] = date ( "Y年m月d日 H:i:s", $alarmslist [$i] ['times'] );
				// $return [$alarmslist [$i] ['id']] = $alarmslist [$i];
			}
			
			$result = array_merge ( $result, $alarmslist );
		}
		usort ( $result, array (
				"HttpViewController",
				"alarm_sort" 
		) );
		
		$return = array ();
		
		for($i = 0; $i < count ( $result ); $i ++) {
			$temp = array ();
			$temp [] = $i + 1;
			$temp [] = $result [$i] ['atime']; // 监控时间
			$temp [] = $task ['title']; // 监控任务名
			$temp [] = $taskdetails ['target']; // 监控对象
			$temp [] = $this->tasktype; // 监控类型
			$temp [] = $result [$i] ['alarmcomment']; // 事件信息
			$temp [] = $result [$i] ['ltime']; // 持续时间
			if ($result [$i] ['type'] == 1) {
				$temp [] = '恢复'; // 持续时间
			} else {
				$temp [] = '故障'; // 持续时间
			}
			$return [] = $temp;
		}
		$return = array_slice ( $return, 0, $limit );
		echo json_encode ( $return );
	}
	public function getlinedata() {
		if (! $this->is_login ( 0 )) {
			exit ( "ERROR1" );
		}
		
		$taskid = I ( 'post.tid' );
		$sdate = I ( 'post.sdate' );
		$edate = I ( 'post.edate' );
		$step = I ( 'post.step' );
		$itemids = I ( 'post.itemid' );
		
		$taskModel = D ( 'jk_task' );
		$taskitemModel = D ( 'jk_taskitem_' . $this->sid );
		
		$task = $taskModel->where ( array (
				"id" => $taskid,
				"is_del" => 0 
		) )->find ();
		
		if (! $task) {
			$this->error ( "no task" );
		}
		
		$itemid_arr = explode ( ",", $itemids );
		$c_series = array ();
		$xv = array (); // x轴刻度
		$yAxis = array (); // Y轴单位
		$yAxis ['type'] = 'value';
		$yAxis ['name'] = "连接数";
		$legend = array ();
		
		foreach ( $itemid_arr as $itemid ) {
			// 获取item 信息 以后要用缓存
			$taskitem = $taskitemModel->where ( array (
					"itemid" => $itemid 
			) )->find ();
			
			if ($taskitem ['iunit'] != '') {
				$yAxis ['name'] = $taskitem ['iunit'];
			}
			$legend [] = $taskitem ['comment'];
			$return = array ();
			$mids = $task ['mids'];
			$mid = str_replace ( ":", "", $mids ); // 服务性能只有一个监控点
			$uid = session ( "uid" );
			$ssid = $task ['ssid'];
			
			// echo "开始时间:".strtotime($stime).PHP_EOL;
			// echo "开始时间:".strtotime($etime).PHP_EOL;
			// echo "间隔:".$step.PHP_EOL;
			$setime = $this->timeinterval ( $sdate, $edate );
			$sdate = $setime [0];
			$edate = $setime [1];
			// $step = 3600;
			$step = $this->getstep($sdate, $edate,$task['frequency']);
			$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid );
			
			// 开始读数据
			$rs = $this->rrd_server_get ( $rrdfilename, $sdate, $edate, $taskitem ['datatype'], $step );
			
			$value = array (); // 数值
			$xv_flag = true;
			if (count ( $xv ) > 0) { // X轴刻度只记一次
				$xv_flag = false;
			}
			foreach ( $rs as $val ) {
				$temp = explode ( " ", $val );
				if ($xv_flag) { // X轴刻度只记一次
					$xv [] = date ( "Y-m-d h:i:s", $temp [0] );
				}
				// if ($taskitem ['iunit'] == '%') {
				// $yAxis ['name'] = "吞吐率(%)";
				// $vv = ( float ) $temp [1];
				// $value [] = ($vv * 100);
				// } else {
				$vv = ( float ) $temp [1];
				$value [] = $vv;
				// }
			}
			$c_series [] = array (
					"name" => $taskitem ['comment'],
					"type" => "line",
					"smooth" => true,
					"smoothMonotone" => "x",
					"data" => $value 
			);
		}
		
		$c_title = array ();
		// "text"=> '堆叠区域图'
		
		$c_legend = array (
				"data" => $legend 
		);
		$c_tooltip = array (
				"trigger" => 'axis' 
		);
		$c_toolbox = array (
				"feature" => array (
						"saveAsImage" => new \stdClass () 
				) 
		);
		$c_xAxis = array (
				"type" => "category",
				"boundaryGap" => false,
				"interval" => 100,
				"data" => $xv 
		);
		$c_yAxis [] = $yAxis;
		$return = array ();
		
		$return ['title'] = $c_title;
		$return ['legend'] = $c_legend;
		$return ['tooltip'] = $c_tooltip;
		$return ['toolbox'] = $c_toolbox;
		$return ['xAxis'] = $c_xAxis;
		$return ['yAxis'] = $c_yAxis;
		$return ['series'] = $c_series;
		
		echo json_encode ( $return );
	}
	protected function alarm_sort($a, $b) {
		if ($a ['times'] == $b ['times']) {
			return 0;
		}
		return ($a ['times'] < $b ['times']) ? 1 : - 1;
	}
}