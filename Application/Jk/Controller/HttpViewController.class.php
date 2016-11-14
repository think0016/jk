<?php
namespace Jk\Controller;

class HttpViewController extends MonitorController {
	public function index(){
		//检查登录情况
		$this->is_login(1);
		
		$taskid = I('get.taskid');
		
		$this->assignbase();
		
		$this->display();
	}
	
	
}