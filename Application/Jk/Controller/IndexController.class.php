<?php
namespace Jk\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
    	
    	//检查登录情况
    	$this->is_login(1);
    	
    	$this->assign("sitetitle",C('sitetitle'));
        $this->display();
    }
    
    public function test() {
    	$this->display();
    }
    
    public function testx() {
    	print_r($_POST);
    }
    
}