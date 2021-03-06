<?php

namespace Webservice\Controller;

class IndexController extends BaseController {
	
	public function postwebtask() {
		$type = $_POST ["type"];
		wlog ( "(postwebtask)[".$type."]");
		$r = 0;
		if ($type == "appcrash" || $type == "appmem" || $type == "appnet") {
			if($type == "appnet"){
				//wlog ( "(APPNET)POST:" . serialize ( $_POST ) );
			}
			
			$r = $this->saveappdata ( $_POST ); // 存APP数据
		} else {
			
			//wlog ( "(postwebtask)POST:" . serialize ( $_POST ) );
			// exit("测试停止");
			
			// 获取监控点信息
			$ip = get_client_ip ();
			
			$pointmodel = D ( "jk_monitorypoint" );
			$point = $pointmodel->field ( 'id' )->where ( array (
					'status' => 1,
					'ip' => $ip 
			) )->find ();
			
			if (! $point) {
				wlog ( "NO POINT BY " . $ip );
				exit ( "NO POINT BY " . $ip );
			}
			$mid = $point ['id'];
			
			$data = $_POST ["data"];
			$id = $_POST ["id"];
			
			$_POST ["taskid"] = $id;
			$_POST ["mid"] = $mid;
			$r = $this->savedata ( $_POST );
		}
		
		if ($r) {
			$msg ['result'] = "操作成功";
			echo json_encode ( $msg );
		} else {
			$msg ['result'] = "操作成功";
			echo json_encode ( $msg );
		}
	}
	
	public function getwebtask() {
		$debug = TRUE;
		
		// 获取监控点信息
		$ip = get_client_ip ();
		if(C('wsdebug')==1){
			$ip = "120.52.96.49";
		}
		
		$taskitem = array ();
		
		$pointmodel = D ( "jk_monitorypoint" );
		$point = $pointmodel->field ( 'id' )->where ( array (
				'status' => 1,
				'ip' => $ip 
		) )->find ();
		$mid = $point ['id'];
		if (! $point) {
			wlog ( "NO POINT BY " . $ip );
			exit ( "NO POINT BY " . $ip );
		}
		
		// 获取任务信息
		$taskmodel = D ( "jk_task" );
		$map ["is_del"] = 0;
		$map ["status"] = 1;
		$map ["mids"] = array (
				"like",
				"%:" . $point ['id'] . ":%" 
		);
		$tasklist = $taskmodel->where ( $map )->select ();
		foreach ( $tasklist as $k => $taskval ) {
			
			$taskval ['mid'] = $mid;
			$sid = $taskval ['sid'];
			$tid = $taskval ['id'];
			
			$task = array ();
			$task ["id"] = $tid;
			// 获取监控类型
			$tasktypemodel = D ( "jk_tasktype" );
			$type1 = $tasktypemodel->where ( array (
					"sid" => $sid 
			) )->find ();
			$type = $type1 ['name'];
			
			// 获取监控参数
			switch ($type) {
				case "http" :
					$task = $this->gethttptask ( $taskval );
					break;
				case "ping" :
					$task = $this->getpingtask ( $taskval );
					break;
				case "apache" :
					break;
				case "nginx" :
					break;
				case "ftp" :
					$task = $this->getftptask ( $taskval );
					break;
				case "udp" :
					$task = $this->getudptask ( $taskval );
					break;
				case "tcp" :
					$task = $this->gettcptask ( $taskval );
					break;
				case "dns" :
					$task = $this->getdnstask ( $taskval );
					break;
				case "mysql" :
					break;
			}
			// $task ["type"] = $type;
			
			$taskitem [] = $task;
			
			// LOG
// 			wlog ( "mid".$mid."  获取tid=" . $tid . "任务" );
		}
		
		$return ["count"] = count ( $taskitem );
		$return ["list"] = $taskitem;
		echo json_encode ( $return );
	}
	
	// public function getwebtask2() {
	
	// // 获取监控点信息
	// $ip = "182.118.3.134";
	// //$ip = get_client_ip ();
	// $taskitem = array ();
	
	// $pointmodel = D ( "jk_monitorypoint" );
	// $point = $pointmodel->field ( 'id' )->where ( array (
	// 'status' => 1,
	// 'ip' => $ip
	// ) )->find ();
	
	// if (! $point) {
	// wlog ( "NO POINT BY " . $ip );
	// exit ( "NO POINT BY " . $ip );
	// }
	
	// // 获取任务信息
	// $taskmodel = D ( "jk_task" );
	// $map ["status"] = 1;
	// $map ["mids"] = array (
	// "like",
	// "%:" . $point ['id'] . ":%"
	// );
	// $tasklist = $taskmodel->where ( $map )->select ();
	// foreach ( $tasklist as $k => $taskval ) {
	
	// $sid = $taskval ['sid'];
	// $ssid = $taskval ['ssid'];
	// $tid = $taskval ['id'];
	
	// $task = array ();
	// $task ["id"] = $tid;
	// // 获取监控类型
	// $tasktypemodel = D ( "jk_tasktype" );
	// $type1 = $tasktypemodel->where ( array (
	// "sid" => $sid
	// ) )->find ();
	// $type = $type1 ['name'];
	
	// // 获取监控参数
	// $table = "jk_taskdetails_" . $sid;
	// $taskdetailsmodel = D ( $table );
	// switch ($type) {
	// case "http" :
	// $detailslist = $taskdetailsmodel->where ( array (
	// "ssid" => $ssid
	// ) )->find ();
	// $task ["target"] = $detailslist ["target"];
	// break;
	// case "ping" :
	// $detailslist = $taskdetailsmodel->where ( array (
	// "ssid" => $ssid
	// ) )->find ();
	// $task ["target"] = $detailslist ["target"];
	// break;
	// case "apache" :
	// $detailslist = $taskdetailsmodel->where ( array (
	// "ssid" => $ssid
	// ) )->find ();
	// $task ["target"] = $detailslist ["target"];
	// break;
	// case "nginx" :
	// $detailslist = $taskdetailsmodel->where ( array (
	// "ssid" => $ssid
	// ) )->find ();
	// $task ["target"] = $detailslist ["target"];
	// break;
	// case "ftp" :
	// $detailslist = $taskdetailsmodel->where ( array (
	// "ssid" => $ssid
	// ) )->find ();
	// $task ["target"] = $detailslist ["target"];
	// $task ["port"] = $detailslist ["port"];
	// $task ["username"] = $detailslist ["username"];
	// $task ["password"] = $detailslist ["password"];
	// break;
	// case "udp" :
	// $detailslist = $taskdetailsmodel->where ( array (
	// "ssid" => $ssid
	// ) )->find ();
	// $task ["target"] = $detailslist ["target"];
	// $task ["port"] = $detailslist ["port"];
	// break;
	// case "tcp" :
	// $detailslist = $taskdetailsmodel->where ( array (
	// "ssid" => $ssid
	// ) )->find ();
	// $task ["target"] = $detailslist ["target"];
	// $task ["port"] = $detailslist ["port"];
	// break;
	// case "mysql" :
	// $detailslist = $taskdetailsmodel->where ( array (
	// "ssid" => $ssid
	// ) )->find ();
	// $task ["target"] = $detailslist ["target"];
	// $task ["port"] = $detailslist ["port"];
	// $task ["username"] = $detailslist ["username"];
	// $task ["password"] = $detailslist ["password"];
	// break;
	// }
	// $task ["type"] = $type;
	
	// $taskitem [] = $task;
	
	// // LOG
	// wlog ( "获取ssid=" . $ssid . "任务" );
	// }
	
	// $return ["count"] = count ( $taskitem );
	// $return ["list"] = $taskitem;
	// echo json_encode ( $return );
	// }
	public function index() {
		echo $this->CreateRrd ( "aaaa", 300, "asas" );
		echo "INDEX";
	}
	
	private function savedata($post) {
		$return = array ();
		$taskid = $post ['taskid'];
		$mid = $post ['mid'];
		$data = object_array ( json_decode ( $post ['data'] ) );
		$lasttime = $post ['dotime'];
		
		/*
		 * [status] => 1 [dotime] => 2016-10-23 12:53:31 [connecttime] => 2294.58 [httpcode] => 200 [downtime] => 3 [alltime] => 2300.48 [downspeek] => 3942KB/s [dnstime] => 2293.17
		 */
		
		$taskModel = D ( "jk_task" );
		$tasklist = $taskModel->where ( array (
				"id" => $taskid 
		) )->find ();
		if (! $tasklist) {
			exit ();
		}
		// 添加lasttime
		$lasttime1 = $tasklist ['lasttime'];
		$lasttime_arr = array ();
		if ($lasttime1 != "" && isset($lasttime1)) {
			$temp = unserialize ( $lasttime1 );
			if ($temp) {
				$lasttime_arr = $temp;
			}
		}
		$lasttime_arr [$mid] = $lasttime;
		$lasttime1 = serialize ( $lasttime_arr );
		$taskModel->where ( array (
				"id" => $taskid 
		) )->save ( array (
				"lasttime" => $lasttime1
		) );
		
		$uid = $tasklist ["uid"];
		$sid = $tasklist ["sid"];
		$frequency = $tasklist ["frequency"];
		$ssid = 0;
		
		$taskitemModel = D ( "jk_taskitem_" . $sid );
		// $taskdetailsModel=D("jk_taskdetails_".$sid);
		
		$taskitem_arr = $taskitemModel->where ( array (
				"is_use" => 1 
		) )->select ();
		foreach ( $taskitem_arr as $k => $val ) {
			$item_id = $val ['itemid']; // 指标名称
			$item_name = $val ['name']; // 指标名称
			$rdata = 0;
			if ($data [$item_name]) {
				$rdata = $data [$item_name];
			}
			$filename = $this->format_rddname ( $taskid, $uid, $mid, $sid, $ssid, $item_id );
			// $return [] = $this->UpdateRrd ( $filename, $item_name, $rdata );
			$this->UpdateRrdBysh ( $filename, $lasttime, $item_name, $rdata, $taskid, $frequency );
		}
		
		return count ( $return );
	}
	private function saveappdata($post) {
		$return = 0;
		$now = date ( "Y-m-d H:i:s" );
		$ip = get_client_ip ();
		switch ($post ['type']) {
			case "appcrash" :
				$logModel = D ( "taskapp_crash_log" );
				$data ['osversion'] = $_POST ['osversion'];
				$data ['deviceid'] = $_POST ['deviceid'];
				$data ['networks'] = $_POST ['networks'];
				$data ['reason'] = $_POST ['reason'];
				$data ['exception'] = $_POST ['exception'];
				$data ['remark'] = $_POST ['remark'];
				$data ['ip'] = $ip;
				$data ['stime'] = $now;
				$return = $logModel->add ( $data );
				break;
			case "appmem" :
				$logModel = D ( "taskapp_mem_log" );
				$data ['osversion'] = $_POST ['osversion'];
				$data ['deviceid'] = $_POST ['deviceid'];
				$data ['networks'] = $_POST ['networks'];
				$data ['used_mem'] = $_POST ['used_mem'];
				$data ['unused_mem'] = $_POST ['unused_mem'];
				$data ['remark'] = $_POST ['remark'];
				$data ['ip'] = $ip;
				$data ['stime'] = $now;
				$return = $logModel->add ( $data );
				break;
			case "appnet" :
				$logModel = D ( "taskapp_net_log" );
				$data ['osversion'] = $_POST ['osversion'];
				$data ['deviceid'] = $_POST ['deviceid'];
				$data ['networks'] = $_POST ['networks'];
				$data ['destination'] = $_POST ['destination'];
				$data ['remark'] = $_POST ['remark'];
				$data ['ip'] = $ip;
				$data ['stime'] = $now;
				$return = $logModel->add ( $data );
				break;
		}
		
		return $return;
	}
	private function gethttptask($taskval) {
		$return = array ();
		
		$taskdetailsModel = D ( "jk_taskdetails_1" );
		// $ssid = $taskval ['ssid'];
		$tid = $taskval ['id'];
		$isadv = $taskval ['isadv'];
		$frequency = $taskval ['frequency'];
		$lasttime = $taskval ['lasttime'];
		$lasttime = $this->rlasttime ( $taskval ['mid'], $lasttime );
		
		$return ['id'] = $tid;
		$return ['frequency'] = $frequency;
		$return ['lasttime'] = $lasttime;
		$taskdetail = $taskdetailsModel->where ( array (
				"taskid" => $tid 
		) )->find ();
		
		if ($taskdetail) {
			$return ['target'] = $taskdetail ['target'];
		}
		
		if ($isadv == 0) {
			$return ['type'] = "http1"; // 普通任务
		} else if ($isadv == 1) {
			$taskdetailsadvModel = D ( "jk_taskdetails_adv_1" );
			$taskadvdetail = $taskdetailsadvModel->where ( array (
					"taskid" => $tid 
			) )->find ();
			
			$return ['reqtype'] = $taskadvdetail ['reqtype']; // 高级任务
			$return ['postdata'] = $taskadvdetail ['postdata']; // 高级任务
			$return ['matchresp'] = $taskadvdetail ['matchresp']; // 高级任务
			$return ['matchtype'] = $taskadvdetail ['matchtype']; // 高级任务
			$return ['cookies'] = $taskadvdetail ['cookies']; // 高级任务
			$return ['httphead'] = $taskadvdetail ['httphead']; // 高级任务
			$return ['username'] = $taskadvdetail ['httpusername']; // 高级任务
			$return ['password'] = $taskadvdetail ['httppassword']; // 高级任务
			$return ['serverip'] = $taskadvdetail ['serverip']; // 高级任务
			
			$return ['type'] = "http2"; // 高级任务
		}
		
		return $return;
	}
	private function getpingtask($taskval) {
		$sid = 3;
		$return = array ();
		
		$taskdetailsModel = D ( "jk_taskdetails_" . $sid );
		// $ssid = $taskval ['ssid'];
		$tid = $taskval ['id'];
		// $isadv = $taskval ['isadv'];
		$frequency = $taskval ['frequency'];
		$lasttime = $taskval ['lasttime'];
		$lasttime = $this->rlasttime ( $taskval ['mid'], $lasttime );
		
		$return ['id'] = $tid;
		$return ['frequency'] = $frequency;
		$return ['lasttime'] = $lasttime;
		$taskdetail = $taskdetailsModel->where ( array (
				"taskid" => $tid 
		) )->find ();
		
		if ($taskdetail) {
			$return ['target'] = $taskdetail ['target'];
		}
		
		$return ['type'] = "ping"; // 普通任务
		
		return $return;
	}
	private function getftptask($taskval) {
		$sid = 6;
		$return = array ();
		
		$taskdetailsModel = D ( "jk_taskdetails_" . $sid );
		// $ssid = $taskval ['ssid'];
		$tid = $taskval ['id'];
		// $isadv = $taskval ['isadv'];
		$frequency = $taskval ['frequency'];
		$lasttime = $taskval ['lasttime'];
		$lasttime = $this->rlasttime ( $taskval ['mid'], $lasttime );
		
		$return ['id'] = $tid;
		$return ['frequency'] = $frequency;
		$return ['lasttime'] = $lasttime;
		$taskdetail = $taskdetailsModel->where ( array (
				"taskid" => $tid 
		) )->find ();
		
		if ($taskdetail) {
			$return ['target'] = $taskdetail ['target'];
			$return ['port'] = $taskdetail ['port'];
			$return ['username'] = $taskdetail ['username'];
			$return ['password'] = $taskdetail ['password'];
		}
		
		$return ['type'] = "ftp"; // 普通任务
		
		return $return;
	}
	private function gettcptask($taskval) {
		$sid = 8;
		$return = array ();
		
		$taskdetailsModel = D ( "jk_taskdetails_" . $sid );
		// $ssid = $taskval ['ssid'];
		$tid = $taskval ['id'];
		// $isadv = $taskval ['isadv'];
		$frequency = $taskval ['frequency'];
		$lasttime = $taskval ['lasttime'];
		$lasttime = $this->rlasttime ( $taskval ['mid'], $lasttime );
		
		$return ['id'] = $tid;
		$return ['frequency'] = $frequency;
		$return ['lasttime'] = $lasttime;
		$taskdetail = $taskdetailsModel->where ( array (
				"taskid" => $tid 
		) )->find ();
		
		if ($taskdetail) {
			$return ['target'] = $taskdetail ['target'];
			$return ['port'] = $taskdetail ['port'];
		}
		
		$return ['type'] = "tcp"; // 普通任务
		
		return $return;
	}
	private function getudptask($taskval) {
		$sid = 9;
		$return = array ();
		
		$taskdetailsModel = D ( "jk_taskdetails_" . $sid );
		// $ssid = $taskval ['ssid'];
		$tid = $taskval ['id'];
		// $isadv = $taskval ['isadv'];
		$frequency = $taskval ['frequency'];
		$lasttime = $taskval ['lasttime'];
		$lasttime = $this->rlasttime ( $taskval ['mid'], $lasttime );
		
		$return ['id'] = $tid;
		$return ['frequency'] = $frequency;
		$return ['lasttime'] = $lasttime;
		$taskdetail = $taskdetailsModel->where ( array (
				"taskid" => $tid 
		) )->find ();
		
		if ($taskdetail) {
			$return ['target'] = $taskdetail ['target'];
			$return ['port'] = $taskdetail ['port'];
			$return ['resptype'] = $taskdetail ['resptype'];
			$return ['resp'] = $taskdetail ['resp'];
			$return ['matchtype'] = $taskdetail ['matchtype'];
			$return ['matchresp'] = $taskdetail ['matchresp'];
		}
		
		$return ['type'] = "udp"; // 普通任务
		
		return $return;
	}
	
	private function getdnstask($taskval) {
		$sid = 13;
		$return = array ();
		
		$taskdetailsModel = D ( "jk_taskdetails_" . $sid );
		// $ssid = $taskval ['ssid'];
		$tid = $taskval ['id'];
		// $isadv = $taskval ['isadv'];
		$frequency = $taskval ['frequency'];
		$lasttime = $taskval ['lasttime'];
		$lasttime = $this->rlasttime ( $taskval ['mid'], $lasttime );
		
		$return ['id'] = $tid;
		$return ['frequency'] = $frequency;
		$return ['lasttime'] = $lasttime;
		$taskdetail = $taskdetailsModel->where ( array (
				"taskid" => $tid 
		) )->find ();
		
		if ($taskdetail) {
			$return ['target'] = $taskdetail ['target'];
			// $return ['domain'] = $taskdetail ['domain'];
			$return ['dnstype'] = $taskdetail ['dnstype'];
			$return ['server'] = $taskdetail ['server'];
			$return ['ip'] = $taskdetail ['ip'];
		}
		
		$return ['type'] = "dns"; // 普通任务
		
		return $return;
	}
}