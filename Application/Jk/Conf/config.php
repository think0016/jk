<?php
return array (
		// '配置项'=>'配置值'
		'rrd_dir' => '/var/www/ce/rrd/',
		'md5_salt' => '#_jiankong_#',
		'sitetitle' => 'JK监控',
		
		'TMPL_ACTION_SUCCESS' => 'Public:success',
		'TMPL_ACTION_ERROR' => 'Public:success',
		
		"logintitle" => "云监控",
		'smsstep' => 1000,
		'PAGE_LISTROWS' => 20,
		
		// 联通监控点配置
		"addTaskUrl_Unicom" => "http://211.94.164.50:10225/dataproxy/proxy/task/v2/add",
		"delTaskUrl_Unicom" => "http://211.94.164.50:10225/dataproxy/proxy/task/v2/delete",
		"source_id_Unicom" => 110201743,
		"task_type_ids_Unicom" => array (
				"1" => 3, // http
				"3" => 1, // ping
				"6" => 12, // ftp
				"8" => 6, // tcp
				"9" => 5, // udp
				"13" => 10 // dns
		) 
 
)
;