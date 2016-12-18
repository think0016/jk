<?php

namespace Webservice\Controller;

class ServiceController extends BaseController {
	private $type_arr = array (
			"apache",
			"tomcat",
			"nginx",
			"sqlserver",
			"oracle",
			"mysql" 
	);
	public function postwebtask() {
		$type = $_POST ["type"];
		wlog ( "(Servicepostwebtask)[" . $type . "]" );
		$r = 0;
		if ($type == "appcrash" || $type == "appmem" || $type == "appnet") {
		} else {
			
			if ($type == "tomcat") {
				wlog ( "(TOMCAT)POST:" . serialize ( $_POST ) );
			}
			
			// wlog ( "(postwebtask)POST:" . serialize ( $_POST ) );
			// exit("测试停止");
			
			// 获取监控点信息
			$ip = get_client_ip ();
			
			$pointmodel = D ( "jk_monitorypoint" );
			$point = $pointmodel->field ( 'id' )->where ( array (
					'status' => 1,
					'ip' => $ip 
			) )->find ();
			
			if (! $point) {
				// wlog ( "NO POINT BY " . $ip );
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
		if (C ( 'wsdebug' ) == 1) {
			$ip = "120.52.96.48";
		}
		
		$taskitem = array ();
		
		$pointmodel = D ( "jk_monitorypoint" );
		$point = $pointmodel->field ( 'id' )->where ( array (
				'status' => 1,
				'ip' => $ip 
		) )->find ();
		$mid = $point ['id'];
		if (! $point) {
			// wlog ( "NO POINT BY " . $ip );
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
				case "apache" :
					$task = $this->getapachetask ( $taskval );
					break;
				case "nginx" :
					$task = $this->getnginxtask ( $taskval );
					break;
				case "mysql" :
					$task = $this->getmysqltask ( $taskval );
					break;
				case "tomcat" :
					$task = $this->gettomcattask ( $taskval );
					break;
				case "sqlserver" :
					$task = $this->getsqlservertask ( $taskval );
					break;
				case "oracle" :
					$task = $this->getoracletask ( $taskval );
					break; // oracle
			}
			
			if (in_array ( $type, $this->type_arr )) {
				$taskitem [] = $task;
			}
			
			// LOG
			// wlog ( "mid".$mid." 获取tid=" . $tid . "任务" );
		}
		
		$return ["count"] = count ( $taskitem );
		$return ["list"] = $taskitem;
		echo json_encode ( $return );
	}
	public function index() {
		echo $this->CreateRrd ( "aaaa", 300, "asas" );
		echo "INDEX";
	}
	private function savedata($post) {
		$return = array ();
		$status = $post ['status'];
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
		if ($lasttime1 != "" && isset ( $lasttime1 )) {
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
		
		// 判断状态
		if ($status == 1) {
			// 正式存RRD
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
				$datatype = $val ['datatype']; // 指标data_type
				$rdata = 0;
				if ($data [$item_name]) {
					$rdata = $data [$item_name];
				}
				$filename = $this->format_rddname ( $taskid, $uid, $mid, $sid, $ssid, $item_id );
				// $return [] = $this->UpdateRrd ( $filename, $item_name, $rdata );
				$this->UpdateServiceRrdBysh ( $filename, $lasttime, $item_name, $rdata, $taskid, $frequency, $datatype );
			}
			
			// tomcat
			if ($post ["type"] == "tomcat") {
				$taskdetailsModel = D ( "jk_taskdetails_33" );
				$updatedata = array (
						"jvm_ver" => $data ['jvm_ver'],
						"appname" => $data ['appname'],
						"tomcat_ver" => $data ['tomcat_ver'],
						"os_ver" => $data ['os_ver'] 
				);
				$taskdetailsModel->where ( array (
						"taskid" => $taskid 
				) )->save ( $updatedata );
			}
		} else {
			// 调用task_status_process.py
			$this->task_status_process ( $taskid, $tasklist ["sid"], $status );
		}
		
		return count ( $return );
	}
	private function getapachetask($taskval) {
		$sid = 4;
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
		
		$return ['type'] = "apache"; // 普通任务
		
		return $return;
	}
	private function getnginxtask($taskval) {
		$sid = 5;
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
		
		$return ['type'] = "nginx"; // 普通任务
		
		return $return;
	}
	private function gettomcattask($taskval) {
		$sid = 33;
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
			$return ['servicename'] = $taskdetail ['servicename'];
			$return ['username'] = $taskdetail ['username'];
			$return ['password'] = $taskdetail ['password'];
		}
		
		$return ['type'] = "tomcat"; // 普通任务
		
		return $return;
	}
	private function getsqlservertask($taskval) {
		$sid = 34;
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
			$return ['databasename'] = $taskdetail ['databasename'];
			$return ['username'] = $taskdetail ['username'];
			$return ['password'] = $taskdetail ['password'];
			$return ['port'] = $taskdetail ['port'];
		}
		
		$return ['type'] = "sqlserver"; // 普通任务
		
		return $return;
	}
	private function getmysqltask($taskval) {
		$sid = 7;
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
			$return ['username'] = $taskdetail ['username'];
			$return ['password'] = $taskdetail ['password'];
			$return ['port'] = $taskdetail ['port'];
		}
		
		$return ['type'] = "mysql"; // 普通任务
		
		return $return;
	}
	private function getoracletask($taskval) {
		$sid = 35;
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
			$return ['username'] = $taskdetail ['username'];
			$return ['password'] = $taskdetail ['password'];
			$return ['port'] = $taskdetail ['port'];
		}
		
		$return ['type'] = "oracle"; // 普通任务
		
		return $return;
	}
}