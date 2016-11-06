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
		
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
		$this->display ();
	}
	public function create() {
		$this->is_login ( 1 );
		
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
		$tasktype_name = I ( "get.ttype" );
		
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
		// 添加detail表
		$data = array (
				"sid" => 1,
				"target" => $target 
		);
		$ssid = $taskDetailsModel->add ( $data );
		if (! $ssid) {
			$this->error ( "ERROR1" );
		}
		
		// 添加task表
		$mid = "";
		$label = "";
		for($i = 0; $i < count ( $mids ); $i ++) {
			if ($i > 0) {
				$mid = $mid . ",";
			}
			$mid = $mid . $mids [$i];
		}
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . $labels [$i];
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => 1,
				"ssid" => $ssid,
				"mids" => $mid,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"labels" => $label,
				"isadv" => $adv 
		);
		$taskid = $taskModel->add ( $data );
		if (! $taskid) {
			$this->error ( "ERROR2" );
		}
		// 添加高级选项
		if ($adv == 1) {
			$data = array (
					"ssid" => $ssid,
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
			
			$r = $taskDetailsAdvModel->add ( $data );
			if (! $r) {
				$this->error ( "ERROR3" );
			}
		}
		
		// 添加告警策略 2,gt,111111,ms,0,1,链接时间,大于
		if ($alarm_num > 0) {
			$flag = 0;
			$triggerModel=D('jk_trigger_ruls');
			for($i = 0; $i < $alarm_num; $i ++) {
				$key = "post.a" . $i;
				$alarm = I ( $key );
				$alist = explode ( ",", $alarm );
				list ( $a_itemid, $a_operator, $threshold, $unit, $calc, $atimes ) = $alist;
				if ($unit == "s") {
					$threshold *= 60;
				}
				if($calc == 0){
					$calc = "avg";
				}
				$data =array(
						"task_id" => $taskid,
						"data_calc_func" => $calc,
						"operator_type" => $a_operator,
						"threshold" => $threshold,
						"data_times" => $atimes,
						"httphead" => $httphead,
						"index_id" => $a_itemid,
						"monitor_id" => $mid
				);
				
					//$data['monitor_id'] = $mid;
					$flag=$triggerModel->add($data);
			}
			if($flag==0){
				$this->error ( "ERROR4" );
			}
		}
		
		$this->success("保存成功",U("Index/index"));
	}
}