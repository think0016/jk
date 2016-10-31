<?php
namespace Jk\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
    	
    	//检查登录情况
    	
    	$this->is_login(1);
    	
        $this->display();
    }
    
    
}