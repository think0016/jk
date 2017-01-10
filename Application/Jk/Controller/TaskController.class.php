<?php

namespace Jk\Controller;

class TaskController extends BaseController {
	private $qxcfg = array (
			"1" => array (
					"stype1" => 2,
					"stype2" => 1,
					"stype3" => 2,
					"mnum" => 3,
					"minfrequency" => 10 
			),
			"2" => array (
					"stype1" => 4,
					"stype2" => 2,
					"stype3" => 4,
					"mnum" => 6,
					"minfrequency" => 5 
			),
			"3" => array (
					"stype1" => 6,
					"stype2" => 4,
					"stype3" => 8,
					"mnum" => 10,
					"minfrequency" => 5 
			),
			"10" => array (
					"stype1" => 100,
					"stype2" => 100,
					"stype3" => 100,
					"mnum" => 100,
					"minfrequency" => 1 
			) 
	);
	public function index() {
		
		// 检查登录情况
		$this->is_login ( 1 );
		
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
		$this->display ();
	}
	public function taskview() {
		// 检查登录情况
		$this->is_login ( 1 );
		
		$type = I ( 'get.type' );
		$tid = I ( 'get.tid' );
		
		switch ($type) {
			case 'http' :
				redirect ( U ( '/HttpView/index/tid/' . $tid ) );
				break;
			case 'ping' :
				redirect ( U ( '/PingView/index/tid/' . $tid ) );
				break;
			case 'ftp' :
				redirect ( U ( '/FtpView/index/tid/' . $tid ) );
				break;
			case 'udp' :
				redirect ( U ( '/UdpView/index/tid/' . $tid ) );
				break;
			case 'tcp' :
				redirect ( U ( '/TcpView/index/tid/' . $tid ) );
				break;
			case 'dns' :
				redirect ( U ( '/DnsView/index/tid/' . $tid ) );
				break;
			case 'apache' :
				redirect ( U ( '/ApacheView/index/tid/' . $tid ) );
				break;
			case 'nginx' :
				redirect ( U ( '/NginxView/index/tid/' . $tid ) );
				break;
			case 'tomcat' :
				redirect ( U ( '/TomcatView/index/tid/' . $tid ) );
				break;
			case 'mysql' :
				redirect ( U ( '/MysqlView/index/tid/' . $tid ) );
				break;
			case 'sqlserver' :
				redirect ( U ( '/SqlServerView/index/tid/' . $tid ) );
				break;
		}
	}
	public function test() {
		$this->is_login ( 1 );
		echo "AAA";
		echo $this->level;
	}
	public function tasklist() {
		
		// 检查登录情况
		$this->is_login ( 1 );
		
		$tasktype = I ( "get.type" );
		if ($tasktype == "") {
			$tasktype = "web";
		}
		
		$taskModel = D ( "jk_task" );
		$map ['uid'] = session ( "uid" );
		$map ['is_del'] = 0;
		
		$menu =array();
		if ($tasktype == "web") {
			$map ['jk_tasktype.stype'] = '1';
			$menu["name"]="web";
			$menu["stype"]="1";
		} else if ($tasktype == "server") {
			$map ['jk_tasktype.stype'] = '3';
			$menu["name"]="server";
			$menu["stype"]="3";
		} else {
			$map ['jk_tasktype.name'] = $tasktype;
			$menu = D ( "jk_tasktype" )->where ( array (
					"name" => $tasktype
			) )->find ();
		}
		$tasklist = $taskModel->where ( $map )->join ( 'jk_tasktype ON jk_task.sid = jk_tasktype.sid' )->select ();
		
// 		if ($tasktype != "web" && $tasktype != "server") {
// 			$tasktype = D ( "jk_tasktype" )->where ( array (
// 					"name" => $tasktype 
// 			) )->find ();
// 		}
		
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
		$this->assign ( "tasklist", $tasklist );
		$this->assign ( "tasktype", $menu );
		$this->display ();
	}
	public function create() {
		$this->is_login ( 1 );
		
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
		$this->assign ( "userlevel", $this->level );
		$tasktype_name = I ( "get.ttype" );
		
		if (! isset ( $tasktype_name ) || $tasktype_name == "") {
			$this->display ( 'createindex' );
			exit ();
		}
		
		$tasktype = $this->getTaskType ( $tasktype_name, 1 );
		if (count ( $tasktype ) == 0) {
			$this->error ( "请求错误" );
		}
		
		// 验证任务数量是否超期
		$this->verify_tasknum ( $tasktype ['stype'] );
		
		// 监控点
		$mps = $this->getMonitoryPoint ();
		// 监控报警项
		$alarmitems = D ( ("jk_taskitem_" . $tasktype ['sid']) )->where ( array (
				"is_alarm" => 1 
		) )->select ();
		
		$this->assign ( "grouplist", D ( "Alarmgroup" )->where ( array (
				"uid" => session ( "uid" ) 
		) )->select () );
		$this->assign ( "alarmitems", $alarmitems );
		$this->assign ( "mps", $mps );
		switch ($tasktype_name) {
			case "http" :
				$this->display ( 'httpadd' );
				break;
			case "ping" :
				$this->display ( 'pingadd' );
				break;
			case "ftp" :
				$this->display ( 'ftpadd' );
				break;
			case "tcp" :
				$this->display ( 'tcpadd' );
				break;
			case "udp" :
				$this->display ( 'udpadd' );
				break;
			case "dns" :
				$this->display ( 'dnsadd' );
				break;
			case "apache" :
				$this->display ( 'apacheadd' );
				break;
			case "nginx" :
				$this->display ( 'nginxadd' );
				break;
			case "mysql" :
				$this->display ( 'mysqladd' );
				break;
			case "tomcat" :
				$this->display ( 'tomcatadd' );
				break;
			case "sqlserver" :
				$this->display ( 'sqlserveradd' );
				break;
			case "oracle" :
				$this->display ( 'oracleadd' );
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
		}
	}
	public function updatealarm() {
		$r = $this->is_login ( 0 );
		
		if (! $r) {
			exit ( "请登录后操作" );
		}
		$aid = I ( "post.aid" );
		$sid = I ( "post.sid" );
		if ($aid == "" || $sid == "") {
			// $this->error("请求错误");
			exit ( "请求错误" );
		}
		
		// print_r($_POST);
		// exit();
		$taskModel = D ( "jk_trigger_ruls" );
		$alarm = array ();
		// if($sid == '1'){
		
		$taskitemtable = "jk_taskitem_" . $sid;
		$alarm = $taskModel->where ( array (
				"jk_trigger_ruls.id" => $aid 
		) )->join ( $taskitemtable . ' ON jk_trigger_ruls.index_id = ' . $taskitemtable . '.itemid' )->find ();
		// }
		
		$return = array ();
		$return ['status'] = 1;
		$return ['data'] = $alarm;
		echo json_encode ( $return );
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
		$this->assign ( "grouplist", D ( "Alarmgroup" )->where ( array (
				"uid" => session ( "uid" ) 
		) )->select () );
		switch ($sid) {
			case 1 :
				// http任务
				$this->display ( 'httpadd' );
				break;
			case 2 :
			case 3 :
				$this->display ( 'pingadd' );
				break;
			case 4 :
				$this->display ( 'apacheadd' );
				break;
			case 5 :
				$this->display ( 'nginxadd' );
				break;
			case 6 :
				// ftp任务
				$this->display ( 'ftpadd' );
				break;
			case 7 :
				$this->display ( 'mysqladd' );
				break;
			case 8 :
				// tcp任务
				$this->display ( 'tcpadd' );
				break;
			case 9 :
				// udp任务
				$this->display ( 'udpadd' );
				break;
			case 13 :
				// dns任务
				$ip = $taskdetail ['ip'];
				$server = $taskdetail ['server'];
				$cbip = 0;
				$cbserver = 0;
				$ip_arr = array ();
				if ($ip != "") {
					$ip_arr = explode ( ",", $ip );
					$cbip = 1;
					$this->assign ( "iparr", $ip_arr );
				} else {
					$this->assign ( "iparr", $ip_arr );
				}
				if ($server != "") {
					$cbserver = 1;
				}
				$this->assign ( "cbserver", $cbserver );
				$this->assign ( "cbip", $cbip );
				$this->display ( 'dnsadd' );
				break;
			case 33 :
				// tomcat任务
				$this->display ( 'tomcatadd' );
				break;
			case 34 :
				// sqlserver任务
				$this->display ( 'sqlserveradd' );
				break;
			case 35 :
				// oracle任务
				$this->display ( 'oracleadd' );
				break;
		}
	}
	public function pingtaskadd() {
		if (! $this->is_login ()) {
			exit ( "请登录" );
		}
		
		$now = date ( "Y-m-d H:i:s" );
		$sid = 3;
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_3' );
		
		/*
		 * Array ( [mids] => :3:,:4:,:5:,:6: [tid] => [update] => [adv] => 0 [alarm_num] => 2 [a0] => 1,gt,34,ms,0,1,当前响应时间,大于, [a1] => 3,gt,44,%,0,1,当前丢包率,大于, [title] => 131 [target] => 123123 [labels] => Array ( [0] => 1 [1] => 2 ) [frequency] => 5 )
		 */
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$mids = I ( 'post.mids' );
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if ($mids == "") {
			$this->error ( "监控点不能为空" );
		}
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		
		// 验证监控点数量是否超期
		$this->verify_pointnum ( $mids );
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mid,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"lasttime" => $this->initlasttime ( $mid ),
				"frequency" => $frequency,
				"labels" => $label,
				"isadv" => $adv,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group 
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
					"sid" => $sid,
					"taskid" => $taskid,
					"target" => $target 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
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
				$triggerid = $alist [9];
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
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function httptaskadd() {
		if (! $this->is_login ()) {
			exit ( "请登录" );
		}
		
		// var_dump($_POST);
		// exit();
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
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if ($mids == "") {
			$this->error ( "监控点不能为空" );
		}
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		// 验证监控点数量是否超期
		$this->verify_pointnum ( $mids );
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
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
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
					"sid" => 1,
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
			
			$n = $taskDetailsAdvModel->where ( array (
					"taskid" => $taskid 
			) )->count ();
			if ($update && $n > 0) {
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
				$triggerid = $alist [9];
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
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function ftptaskadd() {
		if (! $this->is_login ()) {
			exit ( "请登录" );
		}
		// print_r($_POST);
		// exit();
		
		$now = date ( "Y-m-d H:i:s" );
		$sid = 6; // FTP 类型
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_' . $sid );
		
		/*
		 * Array ( [mids] => :3:,:4:,:5:,:6: [tid] => [update] => [adv] => 0 [alarm_num] => 1 [a0] => 1,gt,152,ms,0,1,当前响应时间,大于, [title] => aa [target] => asd [port] => dasd [username] => dasd [password] => dddd [frequency] => 5 )
		 */
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$mids = I ( 'post.mids' );
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$port = I ( 'post.port' );
		$fusername = I ( 'post.username' );
		$fpassword = I ( 'post.password' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if ($mids == "") {
			$this->error ( "监控点不能为空" );
		}
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		if (! isset ( $port ) || $port == "") {
			$this->error ( "端口不能为空" );
		}
		if (! isset ( $fusername ) || $fusername == "") {
			$this->error ( "用户名不能为空" );
		}
		if (! isset ( $fpassword ) || $fpassword == "") {
			$this->error ( "密码不能为空" );
		}
		
		// 验证监控点数量是否超期
		$this->verify_pointnum ( $mids );
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mid,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
					"sid" => $sid,
					"port" => $port,
					"username" => $fusername,
					"password" => $fpassword,
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
					"sid" => $sid,
					"taskid" => $taskid,
					"port" => $port,
					"username" => $fusername,
					"password" => $fpassword,
					"target" => $target 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
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
				$triggerid = $alist [9];
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
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function tcptaskadd() {
		if (! $this->is_login ()) {
			exit ( "请登录" );
		}
		// print_r ( $_POST );
		// exit ();
		
		$now = date ( "Y-m-d H:i:s" );
		$sid = 8; // TCP 类型
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_' . $sid );
		
		/*
		 * Array ( [mids] => :3:,:4:,:5:,:6: [tid] => [update] => [adv] => 0 [alarm_num] => 1 [a0] => 1,gt,152,ms,0,1,当前响应时间,大于, [title] => aa [target] => asd [port] => dasd [username] => dasd [password] => dddd [frequency] => 5 )
		 */
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$mids = I ( 'post.mids' );
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$port = I ( 'post.port' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if ($mids == "") {
			$this->error ( "监控点不能为空" );
		}
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		if (! isset ( $port ) || $port == "") {
			$this->error ( "端口不能为空" );
		}
		
		// 验证监控点数量是否超期
		$this->verify_pointnum ( $mids );
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mid,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
					"sid" => $sid,
					"port" => $port,
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
					"sid" => $sid,
					"taskid" => $taskid,
					"port" => $port,
					"target" => $target 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
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
				$triggerid = $alist [9];
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
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function udptaskadd() {
		if (! $this->is_login ()) {
			exit ( "请登录" );
		}
		
		$now = date ( "Y-m-d H:i:s" );
		$sid = 9; // UDP 类型
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_' . $sid );
		
		/*
		 * Array ( [mids] => :3:,:4:,:5:,:6: [tid] => [update] => [adv] => 0 [alarm_num] => 1 [a0] => 1,gt,152,ms,0,1,当前响应时间,大于, [title] => aa [target] => asd [port] => dasd [username] => dasd [password] => dddd [frequency] => 5 )
		 */
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$mids = I ( 'post.mids' );
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$resptype = I ( 'post.resptype' );
		$resp = I ( 'post.resp' );
		$matchtype = I ( 'post.matchtype' );
		$matchresp = I ( 'post.matchresp' );
		$port = I ( 'post.port' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if ($mids == "") {
			$this->error ( "监控点不能为空" );
		}
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		if (! isset ( $port ) || $port == "") {
			$this->error ( "端口不能为空" );
		}
		
		// 验证监控点数量是否超期
		$this->verify_pointnum ( $mids );
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mid,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
					"sid" => $sid,
					"port" => $port,
					"resp" => $resp,
					"matchtype" => $matchtype,
					"matchresp" => $matchresp,
					"resptype" => $resptype,
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
					"sid" => $sid,
					"taskid" => $taskid,
					"port" => $port,
					"resp" => $resp,
					"matchtype" => $matchtype,
					"matchresp" => $matchresp,
					"resptype" => $resptype,
					"target" => $target 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
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
				$triggerid = $alist [9];
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
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function dnstaskadd() {
		if (! $this->is_login ()) {
			exit ( "请登录" );
		}
		// print_r ( $_POST );
		// exit ();
		
		$now = date ( "Y-m-d H:i:s" );
		$sid = 13; // DNS 类型
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_' . $sid );
		
		/*
		 * Array ( [mids] => :3:,:4:,:5:,:6: [tid] => [update] => [adv] => 0 [alarm_num] => 1 [a0] => 1,gt,152,ms,0,1,当前响应时间,大于, [title] => aa [target] => asd [port] => dasd [username] => dasd [password] => dddd [frequency] => 5 )
		 */
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$mids = I ( 'post.mids' );
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$dnstype = I ( 'post.dnstype' );
		$cbip = I ( 'post.cbip' );
		$ips = I ( 'post.ip' );
		$cbserver = I ( 'post.cbserver' );
		$server = I ( 'post.server' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if ($mids == "") {
			$this->error ( "监控点不能为空" );
		}
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		
		// 验证监控点数量是否超期
		$this->verify_pointnum ( $mids );
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mid,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
		$ips1 = "";
		if ($cbip == 1) {
			foreach ( $ips as $val ) {
				if ($ips1 !== "") {
					$ips1 = $ips1 . "," . $val;
				} else {
					$ips1 = $val;
				}
			}
		}
		if ($cbserver == 0) {
			$server = "";
		}
		if ($update) {
			$data = array (
					"sid" => $sid,
					"dnstype" => $dnstype,
					"ip" => $ips1,
					"server" => $server,
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
					"sid" => $sid,
					"taskid" => $taskid,
					"dnstype" => $dnstype,
					"ip" => $ips1,
					"server" => $server,
					"target" => $target 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
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
				$triggerid = $alist [9];
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
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function apachetaskadd() {
		$this->is_login ( 1 );
		// print_r($_POST);exit();
		/**
		 * Array ( [mids] => :4: [tid] => [update] => [adv] => 0
		 * [alarm_num] => 1
		 * [a0] => 1,gt,123,,,1,并发连接数,大于,
		 * [title] => ce
		 * [target] => 127.0.0.1
		 * [frequency] => 5 )
		 */
		$now = date ( "Y-m-d H:i:s" );
		$sid = 4;
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_' . $sid );
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$mids = ":4:"; // 固定监控点(以后使用优化算法)
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mids,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
					"sid" => $sid,
					"taskid" => $taskid,
					"target" => $target 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
			}
		}
		
		// 添加告警策略 2,gt,111111,ms,1,1,链接时间,大于,2;3;4
		// 添加告警策略 1,gt,999,,,1,并发连接数,大于,,145
		if ($alarm_num > 0) {
			$monitor_id = str_replace ( ":", "", $mids );
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
				$triggerid = $alist [9];
				// if ($unit == "s") {
				// $threshold *= 60;
				// }
				// if ($calc == 1) {
				// // $calc = "avg";
				// $amids = $alist [8];
				// $monitor_id = str_replace ( ";", ",", $amids );
				// }
				$data = array (
						"task_id" => $taskid,
						"data_calc_func" => "avg",
						"operator_type" => $a_operator,
						"threshold" => $threshold,
						"data_times" => $atimes,
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"rrd_name" => $this->getrrdfilename ( $taskid, session ( "uid" ), "4", $sid, "0", $a_itemid ),
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		// print_r ( $_POST );
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function nginxtaskadd() {
		$this->is_login ( 1 );
		/**
		 * Array ( [mids] => :4: [tid] => [update] => [adv] => 0
		 * [alarm_num] => 1
		 * [a0] => 1,gt,123,,,1,并发连接数,大于,
		 * [title] => ce
		 * [target] => 127.0.0.1
		 * [frequency] => 5 )
		 */
		$now = date ( "Y-m-d H:i:s" );
		$sid = 5;
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_' . $sid );
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$mids = ":4:"; // 固定监控点(以后使用优化算法)
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mids,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
					"sid" => $sid,
					"taskid" => $taskid,
					"target" => $target 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
			}
		}
		
		// 添加告警策略 2,gt,111111,ms,1,1,链接时间,大于,2;3;4
		// 添加告警策略 1,gt,123,,,1,并发连接数,大于,
		if ($alarm_num > 0) {
			$monitor_id = str_replace ( ":", "", $mids );
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
				$triggerid = $alist [9];
				// if ($unit == "s") {
				// $threshold *= 60;
				// }
				// if ($calc == 1) {
				// // $calc = "avg";
				// $amids = $alist [8];
				// $monitor_id = str_replace ( ";", ",", $amids );
				// }
				$data = array (
						"task_id" => $taskid,
						"data_calc_func" => "avg",
						"operator_type" => $a_operator,
						"threshold" => $threshold,
						"data_times" => $atimes,
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"rrd_name" => $this->getrrdfilename ( $taskid, session ( "uid" ), "4", $sid, "0", $a_itemid ),
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		// print_r ( $_POST );
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function oracletaskadd() {
	}
	public function mysqltaskadd() {
		$this->is_login ( 1 );
		
		$now = date ( "Y-m-d H:i:s" );
		$sid = 7;
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_' . $sid );
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$username = I ( 'post.username' );
		$password = I ( 'post.password' );
		$port = I ( 'post.port' );
		$mids = ":4:"; // 固定监控点(以后使用优化算法)
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mids,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
					"target" => $target,
					"username" => $username,
					"password" => $password,
					"port" => $port 
			);
			$r = $taskDetailsModel->where ( array (
					"taskid" => $taskid 
			) )->save ( $data );
			// if (! $r) {
			// $this->error ( "ERROR1" );
			// }
		} else {
			$data = array (
					"sid" => $sid,
					"taskid" => $taskid,
					"target" => $target,
					"username" => $username,
					"password" => $password,
					"port" => $port 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
			}
		}
		
		// 添加告警策略 2,gt,111111,ms,1,1,链接时间,大于,2;3;4
		// 添加告警策略 1,gt,123,,,1,并发连接数,大于,
		if ($alarm_num > 0) {
			$monitor_id = str_replace ( ":", "", $mids );
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
				$triggerid = $alist [9];
				// if ($unit == "s") {
				// $threshold *= 60;
				// }
				// if ($calc == 1) {
				// // $calc = "avg";
				// $amids = $alist [8];
				// $monitor_id = str_replace ( ";", ",", $amids );
				// }
				$data = array (
						"task_id" => $taskid,
						"data_calc_func" => "avg",
						"operator_type" => $a_operator,
						"threshold" => $threshold,
						"data_times" => $atimes,
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"rrd_name" => $this->getrrdfilename ( $taskid, session ( "uid" ), "4", $sid, "0", $a_itemid ),
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		// print_r ( $_POST );
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function tomcattaskadd() {
		$this->is_login ( 1 );
		
		/*
		 * [mids] => :4: [tid] => [update] => [adv] => 0 [alarm_num] => 1 [a0] => 7,gt,777,,,1,最大内存,大于,,0 [title] => CCCCCC [target] => ewewsd [username] => qwsdqwd [password] => qwedwsq [servicename] => 333333333 [frequency] => 5
		 */
		$now = date ( "Y-m-d H:i:s" );
		$sid = 33;
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_' . $sid );
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$username = I ( 'post.username' );
		$password = I ( 'post.password' );
		$servicename = I ( 'post.servicename' );
		$mids = ":4:"; // 固定监控点(以后使用优化算法)
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mids,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
					"target" => $target,
					"username" => $username,
					"password" => $password,
					"servicename" => $servicename 
			);
			$r = $taskDetailsModel->where ( array (
					"taskid" => $taskid 
			) )->save ( $data );
			// if (! $r) {
			// $this->error ( "ERROR1" );
			// }
		} else {
			$data = array (
					"sid" => $sid,
					"taskid" => $taskid,
					"target" => $target,
					"username" => $username,
					"password" => $password,
					"servicename" => $servicename 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
			}
		}
		
		// 添加告警策略 2,gt,111111,ms,1,1,链接时间,大于,2;3;4
		// 添加告警策略 1,gt,123,,,1,并发连接数,大于,
		if ($alarm_num > 0) {
			$monitor_id = str_replace ( ":", "", $mids );
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
				$triggerid = $alist [9];
				// if ($unit == "s") {
				// $threshold *= 60;
				// }
				// if ($calc == 1) {
				// // $calc = "avg";
				// $amids = $alist [8];
				// $monitor_id = str_replace ( ";", ",", $amids );
				// }
				$data = array (
						"task_id" => $taskid,
						"data_calc_func" => "avg",
						"operator_type" => $a_operator,
						"threshold" => $threshold,
						"data_times" => $atimes,
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"rrd_name" => $this->getrrdfilename ( $taskid, session ( "uid" ), "4", $sid, "0", $a_itemid ),
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		// print_r ( $_POST );
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	public function sqlservertaskadd() {
		$this->is_login ( 1 );
		
		/*
		 * [mids] => :4: [tid] => [update] => [adv] => 0 [alarm_num] => 1 [a0] => 7,gt,777,,,1,最大内存,大于,,0 [title] => CCCCCC [target] => ewewsd [username] => qwsdqwd [password] => qwedwsq [servicename] => 333333333 [frequency] => 5
		 */
		$now = date ( "Y-m-d H:i:s" );
		$sid = 34;
		$taskModel = D ( 'jk_task' );
		$taskDetailsModel = D ( 'jk_taskdetails_' . $sid );
		
		$taskid = I ( 'post.tid' );
		$update = FALSE;
		// $update = I ( 'post.update' );
		$alarm_num = I ( 'post.alarm_num' );
		$title = I ( 'post.title' );
		$target = I ( 'post.target' );
		$username = I ( 'post.username' );
		$password = I ( 'post.password' );
		$port = I ( 'post.port' );
		$databasename = I ( 'post.databasename' );
		$mids = ":4:"; // 固定监控点(以后使用优化算法)
		$labels = I ( 'post.labels' );
		$frequency = I ( 'post.frequency' );
		$adv = I ( 'post.adv' );
		$alarm_group = I ( 'post.alarm_group' );
		$warntimes = I ( 'post.warntimes' );
		
		// 数据验证（简单）
		if (! isset ( $title ) || $title == "") {
			$this->error ( "任务名不能为空" );
		}
		if (! isset ( $target ) || $target == "") {
			$this->error ( "监控地址不能为空" );
		}
		
		// 添加task表
		$mid = $mids;
		$label = "";
		// for($i = 0; $i < count ( $mids ); $i ++) {
		// if ($i > 0) {
		// $mid = $mid . ",";
		// }
		// $mid = $mid . ":" . $mids [$i] . ":";
		// }
		
		for($i = 0; $i < count ( $labels ); $i ++) {
			if ($i > 0) {
				$label = $label . ",";
			}
			$label = $label . ":" . $labels [$i] . ":";
		}
		$frequency = $frequency * 60;
		$data = array (
				"sid" => $sid,
				"mids" => $mids,
				"uid" => session ( "uid" ),
				"addtime" => $now,
				"title" => $title,
				"frequency" => $frequency,
				"lasttime" => $this->initlasttime ( $mid ),
				"labels" => $label,
				"warntimes" => $warntimes,
				"alarm_group" => $alarm_group,
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
					"target" => $target,
					"username" => $username,
					"password" => $password,
					"databasename" => $databasename,
					"port" => $port 
			);
			$r = $taskDetailsModel->where ( array (
					"taskid" => $taskid 
			) )->save ( $data );
			// if (! $r) {
			// $this->error ( "ERROR1" );
			// }
		} else {
			$data = array (
					"sid" => $sid,
					"taskid" => $taskid,
					"target" => $target,
					"username" => $username,
					"password" => $password,
					"databasename" => $databasename,
					"port" => $port 
			);
			$ssid = $taskDetailsModel->add ( $data );
			if (! $ssid) {
				$this->error ( "ERROR1" );
			}
		}
		
		// 添加告警策略 2,gt,111111,ms,1,1,链接时间,大于,2;3;4
		// 添加告警策略 1,gt,123,,,1,并发连接数,大于,
		if ($alarm_num > 0) {
			$monitor_id = str_replace ( ":", "", $mids );
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
				$triggerid = $alist [9];
				// if ($unit == "s") {
				// $threshold *= 60;
				// }
				// if ($calc == 1) {
				// // $calc = "avg";
				// $amids = $alist [8];
				// $monitor_id = str_replace ( ";", ",", $amids );
				// }
				$data = array (
						"task_id" => $taskid,
						"data_calc_func" => "avg",
						"operator_type" => $a_operator,
						"threshold" => $threshold,
						"data_times" => $atimes,
						"index_id" => $a_itemid,
						"monitor_id" => $monitor_id,
						"rrd_name" => $this->getrrdfilename ( $taskid, session ( "uid" ), "4", $sid, "0", $a_itemid ),
						"is_monitor_avg" => $calc 
				);
				
				// $data['monitor_id'] = $mid;
				if ($triggerid > 0) {
					$flag = $triggerModel->where ( array (
							"id" => $triggerid 
					) )->save ( $data );
				} else {
					$flag = $triggerModel->add ( $data );
				}
			}
			if ($flag == 0) {
				$this->error ( "ERROR4" );
			}
		}
		
		// print_r ( $_POST );
		$this->success ( "保存成功", U ( "Task/tasklist" ) );
	}
	private function getrrdfilename($tid, $uid, $mid, $sid, $ssid, $itemid, $type = 0) {
		$filename = $tid . "_" . $uid . "_" . $mid . "_" . $sid . "_" . $ssid . "_" . $itemid . ".rrd";
		
		// if ($type == 1) {
		// $filename = C ( 'rrd_dir' ) . $tid . "_" . $uid . "_" . $mid . "_" . $sid . "_" . $ssid . "_" . $itemid . "_status.rrd";
		// }
		
		return $filename;
	}
	
	/**
	 * 初始化时间间隔lasttime
	 *
	 * @param unknown $mids        	
	 */
	private function initlasttime($mids) {
		$m2 = str_replace ( ":", "", $mids );
		$temp = explode ( ",", $m2 );
		$return = array ();
		foreach ( $temp as $val ) {
			$return [$val] = time ();
		}
		return serialize ( $return );
	}
	
	/**
	 * 验证任务数量是否超期
	 *
	 * @param
	 *        	$stype
	 */
	private function verify_tasknum($stype) {
		$uid = session ( "uid" );
		
		$taskModel = D ( "jk_task" );
		$where ["jk_task.uid"] = $uid;
		$where ["jk_tasktype.stype"] = $stype;
		$num = $taskModel->join ( 'jk_tasktype ON jk_tasktype.sid = jk_task.sid' )->where ( $where )->count ();
		
		$key = "stype" . $stype;
		$dnum = $this->qxcfg [$this->level] [$key];
		
		if ($num >= $dnum) {
			$this->error ( "任务设置已达到上限" );
		}
	}
	
	/**
	 * 验证监控点数量是否超期
	 *
	 * @param
	 *        	$mids
	 */
	private function verify_pointnum($mids) {
		$uid = session ( "uid" );
		$arr = explode ( ",", $mids );
		
		// $taskModel = D("jk_task");
		// $where["jk_task.uid"]=$uid;
		// $where["jk_tasktype.stype"]=$stype;
		// $num = $taskModel->join('jk_tasktype ON jk_tasktype.sid = jk_task.sid')->where($where)->count();
		
		// $key = "stype".$stype;
		$dnum = $this->qxcfg [$this->level] ["mnum"];
		
		if (count ( $arr ) > $dnum) {
			$this->error ( "监控点数量选择不能超过" . $dnum . "个" );
		}
	}
}