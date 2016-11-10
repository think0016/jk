<?php

namespace Jk\Controller;

class UtilsController extends BaseController {
	public function getmpdata() {
		/*
		 * var ddata = [{ name: '广东', selected: true, value: 1 }, { name: '四川', selected: true, value: 1 }];
		 */
		$return = array ();
		
		$points = $this->getMonitoryPoint ();
		
		$mids = I ( 'post.mids' );
		$mid_arr = explode ( ",", $mids );
		
		foreach ( $points as $point ) {
			$color = "yellow";
			$temp ['name'] = $point ['province'];
			$temp ['value'] = 1;
			$temp ['itemStyle']['normal']['color'] = "yellow";
			//$temp ['selected'] = true;
			
			
			if ($mids != "") {
				$id = ":" . $point ['id'] . ":";
				if (strstr ( $mids, $id ) === false) {
					$temp ['itemStyle']['normal']['color'] = "yellow";
					$temp ['value'] = 1;
				}else{
					$temp ['itemStyle']['normal']['color'] = "green";
					$temp ['value'] = 2;
				}
			}
			$return[] = $temp;
		}
		
// 		$arr = array ();
// 		$d1 = array (
// 				"name" => '广东',
// 				"selected" => true,
// 				"value" => 1 
// 		);
// 		$d2 = array (
// 				"name" => '青海',
// 				"selected" => true,
// 				"value" => 1 
// 		);
// 		$arr [] = $d1;
// 		$arr [] = $d2;
		echo json_encode($return);
	}
}