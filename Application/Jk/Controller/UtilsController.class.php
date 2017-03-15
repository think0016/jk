<?php

namespace Jk\Controller;

class UtilsController extends BaseController {
	
	
	public function getmpdata_old() {//这个方法不再使用
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
	
	public function getmpdata() {
		$model = D ( "jk_monitorypoint" );
		
		$province_all = $model->field("province")->group('province')->select();
		$province_checked = array();
		
		//$mids = ":63:,:64:";
		$mids = I ( 'post.mids' );
		$mid_arr = explode ( ",", $mids );
		
		
		foreach ($mid_arr as $val){
			$mid = str_replace ( ":", "", $val );
			$point_temp = $this->getMonitoryPoint($mid,0);
			if(count($point_temp)>0){
				$province_checked[] = $point_temp['province'];
			}
		}
		
		foreach ($province_all as $val){
			$province = $val['province'];
			
			$color = "yellow";
			$temp ['name'] = $province;
			$temp ['value'] = 1;
			$temp ['itemStyle']['normal']['color'] = "yellow";
			//$temp ['selected'] = true;
				
				
			if (in_array($province, $province_checked)) {
// 				$id = ":" . $point ['id'] . ":";
// 				if (strstr ( $mids, $id ) === false) {
// 					$temp ['itemStyle']['normal']['color'] = "yellow";
// 					$temp ['value'] = 1;
// 				}else{
					$temp ['itemStyle']['normal']['color'] = "green";
					$temp ['value'] = 2;
// 				}
			}
			$return[] = $temp;
		}
		
		echo json_encode($return);
	}
}