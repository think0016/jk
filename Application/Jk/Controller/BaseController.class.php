<?php

namespace Jk\Controller;

use Think\Controller;

class BaseController extends Controller {
	
	public function is_login($type = 0) {
		if($type == 0){
			$uid = session("uid");
			if(isset($uid)){
				return true;
			}else {
				return false;
			}
		}
	}
}