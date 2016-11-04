<?php

namespace Webservice\Controller;

use Think\Controller;

class IndexController extends Controller {
	public function postwebtask() {	
		
		$type = $_POST ["type"];
		
		$r=0;
		if($type == "appcrash" || $type == "appmem" || $type == "appnet"){
			wlog ( "(app)POST:" . serialize ( $_POST ) );
			$r =$this->saveappdata($_POST);//存APP数据
		}else{
			wlog ( "(postwebtask)POST:" . serialize ( $_POST ) );
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
			$r =$this->savedata($_POST);
		}
		
		if($r){
			$msg['result'] = "操作成功";
			echo json_encode($msg);
		}else {
			$msg['result'] = "操作成功";
			echo json_encode($msg);
		}
	}
	
	public function getwebtask() {
		
		// 获取监控点信息
		//$ip = "182.118.3.134";
		$ip = get_client_ip ();
		$taskitem = array ();
		
		$pointmodel = D ( "jk_monitorypoint" );
		$point = $pointmodel->field ( 'id' )->where ( array (
				'status' => 1,
				'ip' => $ip 
		) )->find ();
		
		if (! $point) {
			wlog ( "NO POINT BY " . $ip );
			exit ( "NO POINT BY " . $ip );
		}
		
		// 获取任务信息
		$taskmodel = D ( "jk_task" );
		$map ["status"] = 1;
		$map ["mids"] = array (
				"like",
				"%:" . $point ['id'] . ":%" 
		);
		$tasklist = $taskmodel->where ( $map )->select ();
		foreach ( $tasklist as $k => $taskval ) {
			
			$sid = $taskval ['sid'];
			$ssid = $taskval ['ssid'];
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
			$table = "jk_taskdetails_" . $sid;
			$taskdetailsmodel = D ( $table );
			switch ($type) {
				case "http" :
					$detailslist = $taskdetailsmodel->where ( array (
							"ssid" => $ssid 
					) )->find ();
					$task ["target"] = $detailslist ["target"];
					break;
				case "ping" :
					$detailslist = $taskdetailsmodel->where ( array (
							"ssid" => $ssid
					) )->find ();
					$task ["target"] = $detailslist ["target"];
					break;
				case "apache" :
					$detailslist = $taskdetailsmodel->where ( array (
							"ssid" => $ssid
					) )->find ();
					$task ["target"] = $detailslist ["target"];
					break;
				case "nginx" :
					$detailslist = $taskdetailsmodel->where ( array (
							"ssid" => $ssid
					) )->find ();
					$task ["target"] = $detailslist ["target"];
					break;
				case "ftp" :
					$detailslist = $taskdetailsmodel->where ( array (
							"ssid" => $ssid
					) )->find ();
					$task ["target"] = $detailslist ["target"];
					$task ["port"] = $detailslist ["port"];
					$task ["username"] = $detailslist ["username"];
					$task ["password"] = $detailslist ["password"];
					break;
				case "udp" :
					$detailslist = $taskdetailsmodel->where ( array (
							"ssid" => $ssid
					) )->find ();
					$task ["target"] = $detailslist ["target"];
					$task ["port"] = $detailslist ["port"];
					break;
				case "tcp" :
					$detailslist = $taskdetailsmodel->where ( array (
							"ssid" => $ssid
					) )->find ();
					$task ["target"] = $detailslist ["target"];
					$task ["port"] = $detailslist ["port"];
					break;
				case "mysql" :
					$detailslist = $taskdetailsmodel->where ( array (
							"ssid" => $ssid
					) )->find ();
					$task ["target"] = $detailslist ["target"];
					$task ["port"] = $detailslist ["port"];
					$task ["username"] = $detailslist ["username"];
					$task ["password"] = $detailslist ["password"];
					break;
			}
			$task ["type"] = $type;
			
			$taskitem [] = $task;
			
			// LOG
			wlog ( "获取ssid=" . $ssid . "任务" );
		}
		
		$return ["count"] = count ( $taskitem );
		$return ["list"] = $taskitem;
		echo json_encode ( $return );
	}

	public function index() {
		$model = D ( "Taskserverlog" );
		$data = $_POST ["data"];
		if (! $data)
			exit ();
		$data = object_array ( json_decode ( $data ) );
		unset ( $_POST );
		foreach ( $data as $k => $v ) {
			if (in_array ( $k, array (
					"partition_usage",
					"partition_list",
					"cpu_logical_usage" 
			) ))
				$_POST [$k] = serialize ( $v );
			else
				$_POST [$k] = $v;
		}
		$_POST ["dotime"] = date ( "Y-m-d H:i:s" );
		$_POST ["taskid"] = $_POST ["taskid"] ? $_POST ["taskid"] : 7;
		// print_r($_POST);
		// exit();
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		$list = $model->add ();
		if ($list !== false) { // 保存成功
			$this->success ( '新增成功!' );
		} else {
			// 失败提示
			$this->error ( '新增失败!' );
		}
	}
	public function postservicetask() {
		$data = $_POST ["data"];
		$id = $_POST ["id"];
		$type = $_POST ["type"];
		if (! $data)
			exit ();
		$_POST = object_array ( json_decode ( $data ) );
		$_POST ["taskid"] = $id;
		$_POST ["type"] = $type;
		unset ( $_POST ["id"] );
		$model = D ( "Task" . $_POST ["type"] . "log" );
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		$list = $model->add ();
		if ($list !== false) { // 保存成功
			$this->success ( '新增成功!' );
		} else {
			// 失败提示
			$this->error ( '新增失败!' );
		}
	}
	public function tasklist() {
		$ip = $_POST ["ip"];
		$model = D ( "" );
		$task = $model->select ();
	}
	public function webping() {
	}
	
	/**
	 * Rrd操作
	 */
	private function CreateRrd($name, $step, $ds_name) {
		$flag = 0;
		$filename = C("rrd_dir") . $name . ".rrd";
		system ( "/usr/bin/rrdtool  create $filename  --start -32092970 --step $step DS:$ds_name:GAUGE:600:U:U RRA:AVERAGE:0.5:1:210240 RRA:AVERAGE:0.5:12:17520 RRA:AVERAGE:0.5:288:730 RRA:AVERAGE:0.5:2016:104 RRA:AVERAGE:0.5:8640:24  RRA:MAX:0.5:1:210240  RRA:MAX:0.5:12:17520  RRA:MAX:0.5:288:730  RRA:MAX:0.5:2016:104   RRA:MAX:0.5:8640:24 ", $status );
		if (($status == 0) and (file_exists ( $filename ))) {
			$flag = 1;
		}
		return $flag;
	}
	private function UpdateRrd($name, $ds_name, $data ) {
		$flag = 0;
		$filename = C("rrd_dir") . $name . ".rrd";
		
		if (! file_exists ( $filename )) {
			$r = $this->CreateRrd ( $name, '300', $ds_name );
			if ($r != 1) {
				return "error:create";
			}
		}
		
		$data = time () . ":" . $data;
		$c = "/usr/bin/rrdtool update  $filename  --template  $ds_name  $data";
		system ( $c, $status );
		if ($status == 0) {
			return "OK";
		} else {
			return "ERROR:update:" . $c;
		}
	}
	private function format_rddname($tid, $uid, $mid, $sid, $ssid, $itemid) {
		return $tid . "_" . $uid . "_" . $mid . "_" . $sid . "_" . $ssid . "_" . $itemid;
	}

	public function fx() {
		// $m = M ( "lqtest" );
		// $list = $m->where ( 'id=258' )->find ();
		// $data = $list ['data'];
		// $z = unserialize ( $data );
		// $xxx = object_array ( json_decode ( $z ['data'] ) );
		// print_r ( $xxx );
		wlog("XDCDC");
	}
	
	private function lqlog($data) {
		$testm = M ( "lqtest" );
		$test_datax = array (
				'data' => $data,
				'tid' => 0,
				'stime'=>date("Y-m-d H:i:s")
		);
		$testm->data ( $test_datax )->add ();
	}
/**
 * Rrd操作END
 */
	
	private function savedata($post) {
		$return = array ();
		$taskid = $post ['taskid'];
		$mid = $post ['mid'];
		$data = object_array ( json_decode ( $post ['data'] ) );
		
		/*
		 * [status] => 1
		 * [dotime] => 2016-10-23 12:53:31
		 * [connecttime] => 2294.58
		 * [httpcode] => 200
		 * [downtime] => 3
		 * [alltime] => 2300.48
		 * [downspeek] => 3942KB/s
		 * [dnstime] => 2293.17
		*/
	    
		$taskModel = D ( "jk_task" );
		$tasklist = $taskModel->where ( array (
				"id" => $taskid
		) )->find ();
		if (! $tasklist) {
			exit ();
		}
		$uid = $tasklist ["uid"];
		$sid = $tasklist ["sid"];
		$ssid = $tasklist ["ssid"];
	
		$taskitemModel = D ( "jk_taskitem_" . $sid );
		// $taskdetailsModel=D("jk_taskdetails_".$sid);
	
		$taskitem_arr = $taskitemModel->where(array("is_use"=>1))->select ();
	
		foreach ( $taskitem_arr as $k => $val ) {
			$item_id = $val ['itemid']; // 指标名称
			$item_name = $val ['name']; // 指标名称
			$rdata = 0;
			if ($data [$item_name]) {
				$rdata = $data [$item_name];
			}
			$filename = $this->format_rddname ( $taskid, $uid,$mid, $sid, $ssid, $item_id );
			$return [] = $this->UpdateRrd ( $filename, $item_name, $rdata );
		}
	
		wlog ( serialize ( $return ) );
		return count($return);
	}
	
	private function saveappdata($post) {
		$return = 0;
		$now = date("Y-m-d H:i:s");
		$ip=get_client_ip();
		switch ($post['type']){
			case "appcrash":
				$logModel=D("taskapp_crash_log");
				$data['osversion']=$_POST['osversion'];
				$data['deviceid']=$_POST['deviceid'];
				$data['networks']=$_POST['networks'];
				$data['reason']=$_POST['reason'];
				$data['exception']=$_POST['exception'];
				$data['remark']=$_POST['remark'];
				$data['ip']=$ip;
				$data['stime']=$now;
				$return=$logModel->add($data);
				break;
			case "appmem":
				$logModel=D("taskapp_mem_log");
				$data['osversion']=$_POST['osversion'];
				$data['deviceid']=$_POST['deviceid'];
				$data['networks']=$_POST['networks'];
				$data['used_mem']=$_POST['used_mem'];
				$data['unused_mem']=$_POST['unused_mem'];
				$data['remark']=$_POST['remark'];
				$data['ip']=$ip;
				$data['stime']=$now;
				$return=$logModel->add($data);
				break;
			case "appnet":
				$logModel=D("taskapp_net_log");
				$data['osversion']=$_POST['osversion'];
				$data['deviceid']=$_POST['deviceid'];
				$data['networks']=$_POST['networks'];
				$data['destination']=$_POST['destination'];
				$data['remark']=$_POST['remark'];
				$data['ip']=$ip;
				$data['stime']=$now;
				$return=$logModel->add($data);
				break;
		}
	
		return $return;
	}
}