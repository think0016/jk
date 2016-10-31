<?php

namespace Jk\Controller;

use Think\Controller;

class LoginController extends Controller {
	public function index() {
		$this->display ();
	}
	
	public function login(){
		$username = trim(I("get.username"));
		$password = trim(I("get.password"));
		
		if(!isset($username) || !isset($username)){
			$this->error("非法操作");
		}
		
		$userModel = D('jk_user');
		$map['username'] = $username;
		$map['password'] = $password;
		
		$user=$userModel->where($map)->find();
		
		if($user){
			
			if($user['status']==0){
				
			}else{
				$this->error("该账号已经被冻结");
			}
			
		}else{
			$this->error("账号或密码不正确");
		}
		
		
	}
	
	public function register() {
		$this->display ();
	}
	
	public function register2() {
		$username = trim(I("get.username"));
		$password = trim(I("get.password"));
		$email = I("get.email");
		
		if(!isset($username) || !isset($username) || !isset($email)){
			$this->error("非法操作");
		}
		
		$userModel = D('jk_user');
		
		//检查唯一性
		$map['username']= $username;
		$num=$userModel->where($map)->count();
		if($num==0){
			$data['username'] = $username;
			$data['password'] = md5(($password.C('md5_salt')));
			$data['email'] = $email;
			
			$r=$userModel->save($data);
			if($r){
				session("uid",$r);
				session("username",$username);
				$this->success('注册成功', 'Index/index');
			}else{
				$this->error("注册失败");
			}
		}else{
			$this->error("用户名已存在");
		}
		
	}
	
	public function logout($param) {
		session(null);
		$this->success("退出登录成功",'Login/index');
	}
}