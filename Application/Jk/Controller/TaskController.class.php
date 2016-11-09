<?php

namespace Jk\Controller;

use Think\Controller;

class TaskController extends BaseController {
	public function index() {
		
		// 检查登录情况
		$this->is_login ( 1 );
		
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
		$this->display ();
	}
	
	public function tasklist() {
		
		// 检查登录情况
		$this->is_login ( 1 );
		
		$taskModel = D ( "jk_task" );
		$map ['uid'] = session ( "uid" );
		$map ['is_del'] = 0;
		$tasklist = $taskModel->where ( $map )->join ( 'jk_tasktype ON jk_task.sid = jk_tasktype.sid' )->select ();
		
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
		$this->assign ( "tasklist", $tasklist );
		$this->display ();
	}
	
	public function create() {
		$this->is_login ( 1 );
		
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
		$tasktype_name = I ( "get.ttype" );
		
		if (! isset ( $tasktype_name ) || $tasktype_name == "") {
			$this->display ( 'createindex' );
			exit ();
		}
		
		$tasktype = $this->getTaskType ( $tasktype_name, 1 );
		if (count ( $tasktype ) == 0) {
			$this->error ( "请求错误" );
		}
		
		// 监控点
		$mps = $this->getMonitoryPoint ();
		// 监控报警项
		$alarmitems = D ( ("jk_taskitem_" . $tasktype ['sid']) )->where ( array (
				"is_alarm" => 1 
		) )->select ();
		
		$this->assign ( "alarmitems", $alarmitems );
		$this->assign ( "mps", $mps );
		switch ($tasktype_name) {
			case "http" :
				$this->display ( 'httpadd' );
				break;
		}
	}
	
	public function delete() {
		$r = $this->is_login ( 0 );
		
		if (! $r) {
			exit ( "请登录后操作" );
		}
		$taskid = I ( "post.tid" );
		
		if ($taskid == "") {
			// $this->error("请求错误");
			exit ( "请求错误" );
		}
		
		$taskModel = D ( "jk_task" );
		$data ["is_del"] = 1;
		$data ["status"] = 2;
		$n = $taskModel->where ( array (
				"id" => $taskid 
		) )->save ( $data );
		if ($n) {
			// $this->success("删除成功",U("Task/tasklist"));
			echo "1";
		} else {
			echo "2";
			;
		}
	}
	
	public function delalarm() {
		$r = $this->is_login ( 0 );
		
		if (! $r) {
			exit ( "请登录后操作" );
		}
		$aid = I ( "post.aid" );
		
		if ($aid == "") {
			// $this->error("请求错误");
			exit ( "请求错误" );
		}
		
		$taskModel = D ( "jk_trigger_ruls" );
		// $data ["is_del"] = 1;
		// $data ["status"] = 2;
		// $n = $taskModel->where ( array (
		// "id" => $taskid
		// ) )->save ( $data );
		$n = $taskModel->delete ( $aid );
		if ($n) {
			// $this->success("删除成功",U("Task/tasklist"));
			echo "1";
		} else {
			echo "2";
		}
	}
	
	public function update() {
		$this->is_login ( 1 );
		
		$taskid = I ( "get.tid" );
		
		if ($taskid == "") {
			$this->error ( "请求错误" );
			// exit("请求错误");
		}
		
		$taskModel = D ( "jk_task" );
		$map = array ();
		$map ['id'] = $taskid;
		$map ['is_del'] = 0;
		$task = $taskModel->where ( $map )->find ();
		
		if (! $task) {
			$this->error ( "任务不存在！" );
		}
		
		$sid = $task ['sid'];
		$task ['frequency'] = ($task ['frequency'] / 60);
		
		// 预置监控报警项
		$alarmitems = D ( ("jk_taskitem_" . $sid) )->where ( array (
				"is_alarm" => 1 
		) )->select ();
		
		// 获取details
		$taskdetailsModel = D ( 'jk_taskdetails_' . $sid );
		$taskdetailsAdvModel = D ( 'jk_taskdetails_adv_' . $sid );
		$triggerModel = D ( 'jk_trigger_ruls' );
		
		$map = array ();
		$map ['taskid'] = $taskid;
		$taskdetail = $taskdetailsModel->where ( $map )->find ();
		
		// 获取高级选项
		$taskdetailsAdv = $taskdetailsAdvModel->where ( $map )->find ();
		
		// 获取告警项
		$map = array ();
		$map ['task_id'] = $taskid;
		$triggers = $triggerModel->where ( $map )->join ( "jk_taskitem_" . $sid . " on jk_trigger_ruls.index_id = jk_taskitem_" . $sid . ".itemid" )->select ();
		
		// 监控点
		$mps = $this->getMonitoryPoint ();
		for($i = 0; $i < count ( $mps ); $i ++) {
			$mid = ":" . $mps [$i] ['id'] . ":";
			$r = strstr ( $task ['mids'], $mid );
			if ($r === FALSE) {
				$mps [$i] ['isdefault'] = 0;
			} else {
				$mps [$i] ['isdefault'] = 1;
			}
		}
		
		// print_r ( $task );
		// print_r ( $taskdetail );
		// print_r ( $taskdetailsAdv );
		// print_r ( $triggers );
		// exit ();
		
		$this->assignbase ();
		$this->assign ( "update", 1 );
		$this->assign ( "task", $task );
		$this->assign ( "taskdetail", $taskdetail );
		$this->assign ( "taskadvdata", $taskdetailsAdv );
		$this->assign ( "triggers", $triggers );
		$this->assign ( "alarmitems", $alarmitems );
		$this->assign ( "mps", $mps );
		switch ($sid) {
			case 1 :
				// http任务
				$this->display ( 'httpadd' );
				break;
		}
	}
	
	public function httptaskadd() {
		if (! $this->is_login ()) {
			exit ( "请登录" );
		}
		$now = date ( "Y-m-d H:i:s" );
		$sid = 1;
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_1' );
		$taskDetailsAdvModel = D ( 'jk_taskdetails_adv_1' );
		/*
		 * Array ( [adv] => 1 [alarm_num] => 1 [a0] => 2,gt,111111,ms,0,1,链接时间,大于 [title] => aaa [target] => aa [mids] => Array ( [0] => 2 [1] => 3 ) [labels] => Array ( [0] => 2 ) [frequency] => 10 [reqtype] => get [postdata] => asdasdasd [matchresp] => asdasd [cookies] => asdasd [httphead] => ad [httpusername] => asd [httppassword] => asd [serverip] => asd )
		 */
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$adv = I ( 'post.adv' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$mids = I ( 'post.mids' );
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$reqtype = I ( 'post.reqtype' );
		$postdata = I ( 'post.postdata' );
		$matchresp = I ( 'post.matchresp' );
		$matchtype = I ( 'post.matchtype' );
		$cookies = I ( 'post.cookies' );
		$httphead = I ( 'post.httphead' );
		$httpusername = I ( 'post.httpusername' );
		$httppassword = I ( 'post.httppassword' );
		$serverip = I ( 'post.serverip' );
		
		// 数据验证（简单）
		if (count ( $mids ) == 0) {
			$this->error ( "监控定不能为空" );
		}
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		
		// 添加task表
		$mid = "";
		$label = "";
		for($i = 0; $i < count ( $mids ); $i ++) {
			if ($i > 0) {
				$mid = $mid . ",";
			}
			$mid = $mid . ":" . $mids [$i] . ":";
		}
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => 1,
				"mids" => $mid,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => time (),
				"labels" => $label,
				"isadv" => $adv 
		);
		
		if ($taskid == "") {
			$taskid = $taskModel->add ( $data );
			if (! $taskid) {
				$this->error ( "ERROR2" );
			}
		} else {
			$r = $taskModel->where ( array (
					"id" => $taskid 
			) )->save ( $data );
			if (! $r) {
				$this->error ( "ERROR2" );
			}
			$update = TRUE;
		}
		
		// 添加detail表
		if ($update) {
			$data = array (
					"target" => $target 
			);
			$r = $taskDetailsModel->where ( array (
					"taskid" => $taskid 
			) )->save ( $data );
			// if (! $r) {
			// $this->error ( "ERROR1" );
			// }
		} else {
			$data = array (
					"sid" => 1,
					"taskid" => $taskid,
					"target" => $target 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
			}
		}
		
		// 添加高级选项
		if ($adv == 1) {
			$data = array (
					"reqtype" => $reqtype,
					"postdata" => $postdata,
					"matchresp" => $matchresp,
					"matchtype" => $matchtype,
					"cookies" => $cookies,
					"httphead" => $httphead,
					"httpusername" => $httpusername,
					"httppassword" => $httppassword,
					"serverip" => $serverip 
			);
			
			$n=$taskDetailsAdvModel->where ( array (
						"taskid" => $taskid 
				) )->count();
			if ($update && $n>0) {
				$r = $taskDetailsAdvModel->where ( array (
						"taskid" => $taskid 
				) )->save ( $data );
				// if (! $r) {
				// $this->error ( "ERROR3" );
				// }
			} else {
				$data ["taskid"] = $taskid;
				$r = $taskDetailsAdvModel->add ( $data );
				if (! $r) {
					$this->error ( "ERROR3" );
				}
			}
		}
		
		// 添加告警策略 2,gt,111111,ms,0,1,链接时间,大于
		// 添加告警策略 2,gt,111111,ms,1,1,链接时间,大于,2;3;4
		if ($alarm_num > 0) {
			$monitor_id = str_replace ( ":", "", $mid );
			$flag = 0;
			$triggerModel = D ( 'jk_trigger_ruls' );
			for($i = 0; $i < $alarm_num; $i ++) {
				$key = "post.a" . $i;
				$alarm = I ( $key );
				if ($alarm == "del") {
					continue;
				}
				$alist = explode ( ",", $alarm );
				list ( $a_itemid, $a_operator, $threshold, $unit, $calc, $atimes ) = $alist;
				if ($unit == "s") {
					$threshold *= 60;
				}
				if ($calc == 1) {
					// $calc = "avg";
					$amids = $alist [8];
					$monitor_id = str_replace ( ";", ",", $amids );
				}
				$data = array (
						"task_id" => $taskid,
						"data_calc_func" => "avg",
						"operator_type" => $a_operator,
						"threshold" => $threshold,
						"data_times" => $atimes,
						"httphead" => $httphead,
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"is_monitor_avg" => 0 
				);
				
				// $data['monitor_id'] = $mid;
				$flag = $triggerModel->add ( $data );
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
}