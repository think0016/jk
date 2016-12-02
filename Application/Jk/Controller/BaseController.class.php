<?php

namespace Jk\Controller;

use Think\Controller;

class BaseController extends Controller {
	/*
	 *
	 */
	function _initialize() {
		$this->is_login ( 1 );
	}
	
	/**
	 *
	 * @param number $type
	 *        	1跳转0不跳转
	 * @return boolean
	 */
	public function is_login($type = 0) {
		$uid = session ( "uid" );
		
		if (isset ( $uid )) {
			
			return true;
		} else {
			if ($type == 0) {
				return false;
			} else if ($type == 1) {
				$this->error ( "请登录后操作", U ( 'Login/index' ) );
			}
		}
	}
	
	/**
	 * 获取所有tasktype
	 * 
	 * @param unknown $param        	
	 * @param number $c
	 *        	0:sid 1:name
	 */
	public function getTaskType($param = "", $c = 0) {
		$model = D ( "jk_tasktype" );
		if ($param !== "") {
			$map = array ();
			if ($c == 0) {
				$map ["sid"] = $param;
				return $model->where ( $map )->find ();
			} else if ($c == 1) {
				$map ["name"] = $param;
				return $model->where ( $map )->find ();
			}
		} else {
			return $model->select ();
		}
	}
	
	/**
	 * 获取所有监控点
	 * 
	 * @param unknown $param        	
	 * @param number $c
	 *        	0:mid 1:ip
	 */
	public function getMonitoryPoint($param = "", $c = 0) {
		$model = D ( "jk_monitorypoint" );
		$map ["status"] = 1;
		if ($param !== "") {
			if ($c == 0) {
				$map ["id"] = $param;
				return $model->where ( $map )->find ();
			} else if ($c == 1) {
				$map ["ip"] = $param;
				return $model->where ( $map )->find ();
			}
		} else {
			return $model->where ( $map )->select ();
		}
	}
	
	/**
	 * 获取某一个用户信息
	 * 
	 * @param int $uid        	
	 */
	public function getUserinfo($uid = "") {
		$model = D ( "jk_user" );
		if ($uid !== "") {
			$map ['id'] = $uid;
			$map ['status'] = 1;
			return $model->where ( $map )->find ();
		} else {
			return array ();
		}
	}
	public function assignbase() {
		$this->assign ( "sitetitle", C ( 'sitetitle' ) );
	}
	
	/**
	 * 格式化JSON
	 * @param unknown $result
	 * @param number $status
	 */
	public function formatJSON($result, $status = 0) {
		$return = array();
		$return["status"]=$result;
		$return["result"]=$result;
	}
}
