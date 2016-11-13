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
    	redirect(U('/Index/testx'), 0, '页面跳转中...');
    }
    
    public function testx() {
    	echo "aaa";
    }
    
}