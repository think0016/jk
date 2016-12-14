<?php

namespace Jk\Controller;

class ApacheViewController extends MonitorController {
	private $tasktype = 'apache';
	private $sid = 4;
	private $defaultitem = "responsetime";
	private $showitem = array (
			"status" => 2,
			// "connecttime" => 2,
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
		
		$setime = $this->timeinterval ( $stime, $etime );
		$stime = $setime [0];
		$etime = $setime [1];
		$step = 3600;
		
		$this->assignbase ();
		$this->assign ( "task", $task );
		$this->assign ( "taskdetails", $taskdetails );
		$this->assign ( "taskdetailsadv", $taskdetailsadv );
		$this->assign ( "sdate", $stime );
		$this->assign ( "edate", $etime );
		$this->assign ( "itemid", $itemid );
		$this->assign ( "unit", $this->showitemunit [$itemid] );
		$this->assign ( "step", $step );
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
	public function getalarmtabledata() {
		if (! $this->is_login ( 0 )) {
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
		$stime = I ( 'post.sdate' );
		$etime = I ( 'post.edate' );
		$step = I ( 'post.step' );
		$itemid = I ( 'post.itemid' );
		
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
		$mid = str_replace ( ":", "", $mids );//因为只有一个监控点
		$uid = session ( "uid" );
		$ssid = $task ['ssid'];
		
		// echo "开始时间:".strtotime($stime).PHP_EOL;
		// echo "开始时间:".strtotime($etime).PHP_EOL;
		// echo "间隔:".$step.PHP_EOL;
		$setime = $this->timeinterval ( $stime, $etime );
		$stime = $setime [0];
		$etime = $setime [1];
		$step = 3600;
		$rrdfilename = $this->getrrdfilename ( $taskid, $uid, $mid, $this->sid, $ssid, $itemid );
		
		$return = array ();
		$c_title=array(
			"text"=>"实时监控曲线图",
			"subtext"=>"实时监控曲线图"
		);
		$c_tooltip=array(
				"trigger"=>"axis"
		);
		$c_toolbox=array();
		$c_xAxis=array();
		$c_yAxis=array("type"=>'value');
		$c_series=array();
		echo json_encode ( $return );
	}
	
	protected function alarm_sort($a, $b) {
		if ($a ['times'] == $b ['times']) {
			return 0;
		}
		return ($a ['times'] < $b ['times']) ? 1 : - 1;
	}
}