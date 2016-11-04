<?php
namespace Jk\Controller;
use Think\Controller;
class TaskController extends BaseController {
    public function index(){
    	
    	//检查登录情况
    	$this->is_login(1);
    	
    	$this->assign("sitetitle",C('sitetitle'));
        $this->display();
    }
    
    public function tasklist(){
        
        //检查登录情况
        $this->is_login(1);
        
        $this->assign("sitetitle",C('sitetitle'));
        $this->display();
    }

    public function create() {
    	$this->is_login(1);
    	 
    	$this->assign("sitetitle",C('sitetitle'));
    	$tasktype_name = I("get.ttype");
    	
    	$tasktype = $this->getTaskType($tasktype_name,1);
    	if(count($tasktype)==0){
    		$this->error("请求错误");
    	}
    	
    	//监控点
    	$mps=$this->getMonitoryPoint();
    	//监控报警项
    	$alarmitems = D(("jk_taskitem_".$tasktype['sid']))->where(array("is_alarm"=>1,"is_use"=>1))->select();
    	
    	$this->assign("alarmitems",$alarmitems);
    	$this->assign("mps",$mps);
    	switch ($tasktype_name){
    		case "http":
    			$this->display('httpadd');
    			break;
    	}
    	
    }
    
    
}