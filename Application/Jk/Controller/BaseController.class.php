<?php

namespace Jk\Controller;

use Think\Controller;

class BaseController extends Controller {
	public function is_login($type = 0) {
		$uid = session ( "uid" );
		
		if (isset ( $uid )) {
			
				return true;
		} else {
			if ($type == 0) {
				return false;
			} else if ($type == 1) {
				$this->error("请登录后操作",U('Login/index'));
			}
		}
	}
}