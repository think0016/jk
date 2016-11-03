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
    	$type = I("get.type");
    	if($type=="http"){
    		$this->display('httpadd');
    	}
    	
    }
}