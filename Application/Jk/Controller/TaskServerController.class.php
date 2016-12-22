<?php

namespace Jk\Controller;

class TaskServerController extends BaseController {

    public function alarmindex() {
$taskid = I ( 'get.tid' );
		$aid = I ( 'get.aid' );
		$stime = I ( 'get.sdate' );
		$etime = I ( 'get.edate' );
		$limit = I ( 'get.limit' );
		$stime=strtotime($stime);
		$etime=strtotime($etime)+3600*24;
		if ($limit == "" || $limit == 0) {
			$limit = 1000;
		}
		switch ($aid)
                {
                    case "cpu":
                        $sid="21,28,22";
                        break;
                    case "mem":
                        $sid="23,29";
                        break;
                    case "net":
                        $sid="24,30";
                        break;
                    case "disk":
                        $sid="25,31";
                        break;
                    case "process":
                        $sid="27,32";
                        break;
                    case "io":
                        $sid="26";
                        break;
                }
		
		$taskModel = D ( 'jk_task' );
                 $taskinfo=$taskModel->where(array("id"=>$taskid,"status"=>1))->find();
		$taskdetailsModel = D ( 'jk_taskdetails_' . $taskinfo["sid"] );
		// $taskdetailsAdvModel = D ( 'jk_taskdetails_adv_' . $this->sid );
		$triggerModel = D ( 'jk_trigger_ruls' );
		
		$task = $taskModel->where ( array (
				"id" => $taskid,
				"is_del" => 0 
		) )->find ();
		
		if (! $task) {
			$this->error ( "参数错误2" );
		}
		$taskdetails = $taskdetailsModel->where ( array (
				"taskid" => $taskid 
		) )->find ();
		$triggerlist = array ();
		if ($aid == 0 || $aid == "") { // 全部告警信息
			$triggerlist = $triggerModel->join ( "jk_taskitem_" . $taskinfo["sid"] . " ON jk_trigger_ruls.index_id=" . "jk_taskitem_" .$taskinfo["sid"] . ".itemid" )->where ( array (
					"jk_trigger_ruls.task_id" => $taskid 
			) )->limit ( $limit )->select ();
		} else {
			$triggerlist = $triggerModel->join ( "jk_taskitem_" . $taskinfo["sid"] . " ON jk_trigger_ruls.index_id=" . "jk_taskitem_" . $taskinfo["sid"] . ".itemid" )->where ( array (
					"jk_trigger_ruls.task_id" => $taskid,
					"_string" => "jk_trigger_ruls.index_id in(select itemid from jk_taskitem_2 where sid in($sid) )"
			) )->limit ( $limit )->select ();
		}
		for($i = 0; $i < count ( $triggerlist ); $i ++) {
			$triggerlist [$i] ["alarmcomment"] = $this->get_alarm_comment ( $triggerlist [$i] ["threshold"], $triggerlist [$i] ["comment"], $triggerlist [$i] ["iunit"], $triggerlist [$i] ["operator_type"] );
		}
		
		$result = array ();
		
		foreach ( $triggerlist as $val ) {
			$map = array ();
			$map ['jk_alarms_list.task_id'] = $taskid;
			$map ['jk_alarms_list.times'] = array (
					array (
							"GT",
							$stime
					),
					array (
							"LT",
							$etime
					) 
			);
			$map ['jk_alarms_list.trigger_id'] = $val ['id'];
			
			$alarmsModel = D ( "jk_alarms_list" );
			$alarmslist = $alarmsModel->where ( $map )->order ( 'jk_alarms_list.times desc' )->select ();
			
			//echo $alarmsModel->getLastSql();
			
			for($i = 0; $i < count ( $alarmslist ); $i ++) {
				if ($alarmslist [$i] ['type'] == 0) {
					$letime = time ();
					if ($i > 0) {
						$i2 = $i - 1;
						$letime = $alarmslist [$i2] ['times'];
					}
					$alarmslist [$i] ['ltime'] = changeTimeType ( abs ( $letime - $alarmslist [$i] ['times'] ) );
				} else {
					$alarmslist [$i] ['ltime'] = '';
				}
				$alarmslist [$i] ['alarmcomment'] = $val ['alarmcomment'];
				$alarmslist [$i] ['atime'] = date ( "Y年m月d日 H:i:s", $alarmslist [$i] ['times'] );
				// $return [$alarmslist [$i] ['id']] = $alarmslist [$i];
			}
			
			$result = array_merge ( $result, $alarmslist );
		}
		usort ( $result, array (
				"HttpViewController",
				"alarm_sort" 
		) );
		
		$return = array ();
		
		for($i = 0; $i < count ( $result ); $i ++) {
			$temp = array ();
			$temp [] = $i + 1;
			$temp [] = $result [$i] ['atime']; // 监控时间
			$temp [] = $task ['title']; // 监控任务名
			$temp [] = $taskdetails ['target']; // 监控对象
			$temp [] = gettasktype($taskinfo["sid"]); // 监控类型
			$temp [] = $result [$i] ['alarmcomment']; // 事件信息
			$temp [] = $result [$i] ['ltime']; // 持续时间
			if ($result [$i] ['type'] == 1) {
				$temp [] = '恢复'; // 持续时间
			} else {
				$temp [] = '故障'; // 持续时间
			}
			$return [] = $temp;
		}
		$return = array_slice ( $return, 0, $limit );
		echo json_encode ( $return );
    }

    public function publiccontent() {
        $taskid = I("get.taskid");
        $taskinfo = D("jk_taskdetails_2")->join("jk_task on jk_taskdetails_2.taskid=jk_task.id")->where(array("taskid" => $taskid))->find();
        $this->assign("vo", $taskinfo);
        $this->assign("taskid", $taskid);
        $this->assign("begintime", date("Y-m-d"));
        $this->assign("endtime", date("Y-m-d"));
    }

    public function showalarmlist() {
        $taskid = I("get.taskid");
        $taskinfo = D("jk_taskdetails_2")->join("jk_task on jk_taskdetails_2.taskid=jk_task.id")->where(array("taskid" => $taskid))->find();
        $this->assign("vo", $taskinfo);
        $this->assign("taskid", $taskid);
        $this->assign("begintime", date("Y-m-d"));
        $this->assign("endtime", date("Y-m-d"));



        $stime = date("Y-m-d");
        $etime = date("Y-m-d");
        if ($taskid == "") {
            $this->error("参数错误1");
        }


        $taskModel = D('jk_task');
        $triggerModel = D('jk_trigger_ruls');
        $task = $taskModel->where(array(
                    "id" => $taskid,
                    "is_del" => 0
                ))->find();

        $triggerlist = $triggerModel->join("jk_taskitem_" . $taskinfo["sid"] . " ON jk_trigger_ruls.index_id=" . "jk_taskitem_2" . ".itemid")->where(array(
                    "jk_trigger_ruls.task_id" => $taskid
                ))->select();

        for ($i = 0; $i < count($triggerlist); $i ++) {
            $triggerlist [$i] ["alarmcomment"] = $this->get_alarm_comment($triggerlist [$i] ["threshold"], $triggerlist [$i] ["comment"], $triggerlist [$i] ["iunit"], $triggerlist [$i] ["operator_type"]);
        }

        $this->assignbase();
        $this->assign("sdate", $stime);
        $this->assign("edate", $etime);
        $this->assign("task", $task);
        $this->assign("triggerlist", $triggerlist);

        $this->display();
    }

    public function get_alarm_comment($threshold, $itemname, $iunit, $operator_type) {
        $comment = "";
        switch ($operator_type) {
            case 'gt' :
                $comment = $itemname . "大于" . $threshold . $iunit;
                break;
            case 'lt' :
                $comment = $itemname . "小于" . $threshold . $iunit;
                break;
            default :
                $comment = $itemname . "等于" . $threshold . $iunit;
        }
        return $comment;
    }

    public function showcpu() {
        $this->publiccontent();
        $taskid = I("get.taskid");
        $rrdinfo = D("jk_rrd_detail")->where(array("taskid" => $taskid))->select();
        foreach ($rrdinfo as $v) {
            $rrdlist[$v["itemname"]][$v["ssname"]] = $v["rrdfilename"];
        }
        //print_r($this->loaddata("last", $rrdlist["user_percent"][""],"","",60,1));
        $nowdata["user_percent"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["user_percent"][""], "", "", 60, 1), 1));
        $nowdata["system_percent"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["system_percent"][""], "", "", 60, 1), 1));
        $nowdata["iowait_percent"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["iowait_percent"][""], "", "", 60, 1), 1));
        $nowdata["idle_percent"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["idle_percent"][""], "", "", 60, 1), 1));
        $nowdata["cpu1"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["cpu1"][""], "", "", 60, 1), 1));
        $nowdata["cpu5"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["cpu5"][""], "", "", 60, 1), 1));
        $nowdata["cpu15"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["cpu15"][""], "", "", 60, 1), 1));
        $nowdata["cpunow"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["idle_percent"][""], "", "", 60, 1), 1));
        $nowdata["cpumax"] = getpresent(getmaxinfo($this->loaddata("max", $rrdlist["idle_percent"][""], strtotime(date("Y-m-d")), time(), 60, 1), 1));
        $nowdata["cpuavg"] = getpresent(getavginfo($this->loaddata("avg", $rrdlist["idle_percent"][""], strtotime(date("Y-m-d")), time(), 60, 1), 1));
        $this->assign("nowdata", $nowdata);
        $this->display();
    }

    public function showmem() {
        $this->publiccontent();
        $this->display();
    }

    public function showprogress() {
        $this->publiccontent();
        $taskid = I("get.taskid");
        $rrdinfo = D("jk_rrd_detail")->where(array("taskid" => $taskid))->select();
        foreach ($rrdinfo as $v) {
            $rrdlist[$v["itemname"]][$v["ssname"]] = $v["rrdfilename"];
        }
        //print_r($this->loaddata("last", $rrdlist["user_percent"][""],"","",60,1));
        $nowdata["process_num"] = intval(getlastinfo($this->loaddata("last", $rrdlist["process_num"][""], "", "", 60, 1), 1));
        $this->assign("nowdata", $nowdata);
        $this->display();
    }

    public function showio() {
        $taskid = I("get.taskid");
        $taskinfo = D("jk_taskdetails_2")->join("jk_task on jk_taskdetails_2.taskid=jk_task.id")->where(array("taskid" => $taskid))->find();
        $this->assign("vo", $taskinfo);
        $this->assign("taskid", $taskid);
        $this->assign("begintime", date("Y-m-d"));
        $this->assign("endtime", date("Y-m-d"));
        $diskpercent = D("jk_rrd_detail")->where(array("sid" => "26"))->select();
        foreach ($diskpercent as $v) {
            unset($tem);
            $disk[$v["ssname"]]["id"] = $v["ssid"];
            $disk[$v["ssname"]]["name"] = $v["ssname"];
            $disk[$v["ssname"]]["filename"] = $v["rrdfilename"];
            $disk[$v["ssname"]][$v["itemname"] . "avg"] = getavginfo($this->loaddata("avg", $v["rrdfilename"], strtotime(date("Y-m-d")), time(), 60, 1), 1);
            $disk[$v["ssname"]][$v["itemname"] . "max"] = getmaxinfo($this->loaddata("max", $v["rrdfilename"], strtotime(date("Y-m-d")), time(), 60, 1), 1);
        }
        //print_r($disk);exit();
        $this->assign("disk", $disk);
        $this->display();
    }

    public function showdiskuse() {
        $taskid = I("get.taskid");
        $taskinfo = D("jk_taskdetails_2")->join("jk_task on jk_taskdetails_2.taskid=jk_task.id")->where(array("taskid" => $taskid))->find();
        $this->assign("vo", $taskinfo);
        $this->assign("taskid", $taskid);
        $this->assign("begintime", date("Y-m-d"));
        $this->assign("endtime", date("Y-m-d"));
        $ttid = 31;
        if ($taskinfo["systemtype"] == "linux") {
            $ttid = 25;
        }
        $diskpercent = D("jk_rrd_detail")->where(array("sid" => "$ttid"))->select();
        foreach ($diskpercent as $v) {
            //print_r($v);echo "<br>";
            unset($tem);
            $disk[$v["ssname"]]["id"] = $v["ssid"];
            $disk[$v["ssname"]]["name"] = $v["ssname"];
            $disk[$v["ssname"]]["filename"] = $v["rrdfilename"];
            $disk[$v["ssname"]][$v["itemname"]] = getlastinfo($this->loaddata("last", $v["rrdfilename"], strtotime(date("Y-m-d")), time(), 60, 1), 1);
            $disk[$v["ssname"]][$v["itemname"]] = getlastinfo($this->loaddata("last", $v["rrdfilename"], strtotime(date("Y-m-d")), time(), 60, 1), 1);
            $disk[$v["ssname"]][$v["itemname"]] = getlastinfo($this->loaddata("last", $v["rrdfilename"], strtotime(date("Y-m-d")), time(), 60, 1), 1);
        }
        //print_r($disk);exit();
        $this->assign("disk", $disk);
        $this->display();
    }

    public function shownet() {
        $taskid = I("get.taskid");
        $taskinfo = D("jk_taskdetails_2")->join("jk_task on jk_taskdetails_2.taskid=jk_task.id")->where(array("taskid" => $taskid))->find();
        $this->assign("vo", $taskinfo);
        $this->assign("taskid", $taskid);
        $this->assign("begintime", date("Y-m-d"));
        $this->assign("endtime", date("Y-m-d"));
        $ttid = 30;
        if ($taskinfo["systemtype"] == "linux") {
            $ttid = 24;
        }
        $diskpercent = D("jk_rrd_detail")->where(array("sid" => "$ttid"))->select();
        foreach ($diskpercent as $v) {
            //print_r($v);
            //echo "<br>";
            unset($tem);
            $disk[$v["ssname"]]["id"] = $v["ssid"];
            $disk[$v["ssname"]]["name"] = $v["ssname"];
            $disk[$v["ssname"]]["filename"] = $v["rrdfilename"];
            $disk[$v["ssname"]][$v["itemname"] . "avg"] = getavginfo($this->loaddata("avg", $v["rrdfilename"], strtotime(date("Y-m-d")), time(), 60, 1), 1);
            $disk[$v["ssname"]][$v["itemname"] . "max"] = getmaxinfo($this->loaddata("max", $v["rrdfilename"], strtotime(date("Y-m-d")), time(), 60, 1), 1);
        }
        //print_r($disk);exit();
        $this->assign("disk", $disk);
        $this->display();
    }

    public function show() {
        $taskid = I("get.taskid");
        $taskinfo = D("jk_taskdetails_2")->join("jk_task on jk_taskdetails_2.taskid=jk_task.id")->where(array("taskid" => $taskid))->find();
        $rrdinfo = D("jk_rrd_detail")->where(array("taskid" => $taskid))->select();
        foreach ($rrdinfo as $v) {
            $rrdlist[$v["itemname"]][$v["ssname"]] = $v["rrdfilename"];
        }
        $nowdata["cpunow"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["user_percent"][""], "", "", 60, 1), 1));
        $nowdata["cpumax"] = getpresent(getavginfo($this->loaddata("max", $rrdlist["user_percent"][""], strtotime(date("Y-m-d")), time(), 60, 0)));
        $nowdata["cpuavg"] = getpresent(getmaxinfo($this->loaddata("avg", $rrdlist["user_percent"][""], strtotime(date("Y-m-d")), time(), 60, 0)));
        $nowdata["memnow"] = getpresent(getlastinfo($this->loaddata("last", $rrdlist["user_percent"][""], "", "", 60, 1), 1));
        $nowdata["memmax"] = getpresent(getavginfo($this->loaddata("max", $rrdlist["mem_used_percent"][""], strtotime(date("Y-m-d")), time(), 60, 0)));
        $nowdata["memavg"] = getpresent(getmaxinfo($this->loaddata("avg", $rrdlist["mem_used_percent"][""], strtotime(date("Y-m-d")), time(), 60, 0)));
        $nowdata["processnow"] = intval(getlastinfo($this->loaddata("last", $rrdlist["process_num"][""], "", "", 60, 1), 1));
        $nowdata["processmax"] = intval(getavginfo($this->loaddata("max", $rrdlist["process_num"][""], strtotime(date("Y-m-d")), time(), 60, 0)));
        $nowdata["processavg"] = intval(getmaxinfo($this->loaddata("avg", $rrdlist["process_num"][""], strtotime(date("Y-m-d")), time(), 60, 0)));

        //mem_used_percent
        //print_r($nowdata);exit();
        $systeminfo = unserialize($taskinfo["systeminfo"]);
        $ttid1 = 31;
        $ttid2 = 30;
        if ($taskinfo["systemtype"] == "linux") {
            $ttid1 = 25;
            $ttid2 = 24;
        }
        $diskpercent = D("jk_rrd_detail")->where(array("sid" => "$ttid1"))->select();
        foreach ($diskpercent as $v) {
            unset($tem);
            //print_r($v);
            $disk[$v["ssname"]]["name"] = $v["ssname"];
            $tem = $this->loaddata("last", $v["rrdfilename"], "", "", 60, 0);
            $disk[$v["ssname"]][$v["itemname"]] = getlastinfo($tem, 1);
        }
        $this->assign("disk", $disk);
        $netinfo = D("jk_rrd_detail")->where(array("sid" => "$ttid2"))->select();
        foreach ($netinfo as $v) {
            unset($tem);
            unset($tem2);
            //print_r($v);
            $net[$v["ssname"]]["name"] = $v["ssname"];
            $tem = $this->loaddata("last", $v["rrdfilename"], "", "", 60, 1);
            //$tem = explode(" ", $tem[0]);
            $net[$v["ssname"]][$v["itemname"]]["now"] = getlastinfo($tem, 1);


            $tem2 = $this->loaddata("avg", $v["rrdfilename"], strtotime(date("Y-m-d")), time(), $taskinfo["frequency"] * 12, D("jk_taskitem_2")->where(array("itemid" => $v["itemid"]))->getfield("datatype"));
            $net[$v["ssname"]][$v["itemname"]]["today"] = $tem2[0];
        }
        $this->assign("net", $net);
        if ($taskinfo["systemtype"] == "linux") {
            $ioinfo = D("jk_rrd_detail")->where(array("sid" => "26"))->select();
            foreach ($ioinfo as $v) {
                unset($tem);
                unset($tem2);
                //print_r($v);
                $io[$v["ssname"]]["name"] = $v["ssname"];
                $tem = $this->loaddata("last", $v["rrdfilename"], "", "", 60, 1);
                $tem = explode(" ", $tem[0]);
                $io[$v["ssname"]][$v["itemname"]]["now"] = $tem[1];


                $tem2 = $this->loaddata("avg", $v["rrdfilename"], strtotime(date("Y-m-d")), time(), $taskinfo["frequency"] * 12, D("jk_taskitem_2")->where(array("itemid" => $v["itemid"]))->getfield("datatype"));
                //print_r($tem2);
                $io[$v["ssname"]][$v["itemname"]]["today"] = $tem2[0];
            }
            //print_r($io); 
            $this->assign("io", $io);
        }

        $partinfo = D("jk_rrd_detail")->where(array("taskid" => $taskid))->select();
        $this->assign("systeminfo", $systeminfo);
        $this->assign("taskinfo", $taskinfo);
        $this->assign("nowdata", $nowdata);
        $this->assign("taskid", $taskid);
        $this->assign("begintime", date("Y-m-d"));
        $this->assign("endtime", date("Y-m-d"));
        $this->display();
    }

    private function loaddata($type, $rrd, $begintime = "", $endtime = "", $step = "60", $datatype = "1") {
        //$common = "/var/www/ce/cmd/get_list_data.sh   /var/www/ce/rrd/107_60002_0_22_0_14.rrd   -43200  -20  60  1";
        if ($type == "list")
            $common = "/var/www/ce/cmd/get_list_data.sh   /var/www/ce/rrd/$rrd   $begintime  $endtime  $step  $datatype";
        if ($type == "max")
            $common = "/var/www/ce/cmd/rrd_max.sh   /var/www/ce/rrd/$rrd   $begintime  $endtime  $step  $datatype";
        if ($type == "last") {
            $common = "/var/www/ce/cmd/get_last.sh   /var/www/ce/rrd/$rrd $step $datatype";
            //return $common;
        }
        if ($type == "avg")
            $common = "/var/www/ce/cmd/rrd_avg.sh   /var/www/ce/rrd/$rrd   $begintime  $endtime  $step  $datatype";
        //return $common;
        $result = exec($common, $status);
        return $status;
        //return $status;
    }

    public function showdatapie() {
        $type = I("get.type");
        $taskid = intval(I("get.taskid"));
        $begintime = strtotime(I("get.begintime"));
        $endtime = strtotime(I("get.endtime") . " 23:59:59");
        if ($endtime > time())
            $endtime = $time - 5 * 60;
        $taskinfo = D("jk_taskdetails_2")->join("jk_task on jk_taskdetails_2.taskid=jk_task.id")->where(array("taskid" => $taskid))->find();
        switch ($type) {
            case "cpuuse":
                if ($taskinfo["systemtype"] = "linux") {
                    $iteminfo = D("jk_taskitem_2")->where(array("sid" => "21"))->select();
                    $tooltip["trigger"] = "item";
                    //$tooltip["formatter"]="{a} <br/>{b} : {c} ({d}%)";
                    $data["tooltip"] = $tooltip;
                    $series["name"] = "CPU使用比例";
                    $series["type"] = 'pie';
                    $series["radius"] = '55%';
                    $series["center"] = array('50%', '60%');
                    $emphasis["shadowBlur"] = 10;
                    $emphasis["shadowOffsetX"] = 0;
                    $emphasis["shadowColor"] = 'rgba(0, 0, 0, 0.5)';
                    $itemStyle["emphasis"] = $emphasis;
                    $series["itemStyle"] = $itemStyle;
                    foreach ($iteminfo as $v) {
                        unset($item);
                        unset($temdata);
                        unset($xtemp);
                        $item["name"] = $v["comment"];
                        $rrd = D("jk_rrd_detail")->where(array("taskid" => $taskid, "itemid" => $v["itemid"]))->getfield("rrdfilename");
                        $temdata = $this->loaddata("last", $rrd, $begintime, $endtime, $taskinfo["frequency"] * 12, $v["datatype"]);
                        $item["value"] = getlastinfo($temdata[0], 1);
                        $items[] = $item;
                    }

                    $series["data"] = $items;
                    $data["tooltip"] = $tooltip; // array("1分钟平均负载", "5分钟平均负载", "15分钟平均负载");
                    $data["series"] = $series;
                    echo json_encode($data);
                } else {
                    
                }
                break;
        }
    }

    public function showdata() {
        $type = I("get.type");
        $taskid = intval(I("get.taskid"));
        $begintime = strtotime(I("get.begintime"));
        $endtime = strtotime(I("get.endtime") . " 23:59:59");
        if ($endtime > time())
            $endtime = $time - 5 * 60;
        $taskinfo = D("jk_taskdetails_2")->join("jk_task on jk_taskdetails_2.taskid=jk_task.id")->where(array("taskid" => $taskid))->find();
        //print_r($taskinfo);
        $areaStyle["normal"] = array();
        switch ($type) {
            case "cpuload":
                if ($taskinfo["systemtype"] == "linux") {
                    $data["tooltip"]["trigger"] = 'axis';
                    $iteminfo = D("jk_taskitem_2")->where(array("sid" => "22"))->select();
                    $yAxisitem["type"] = "value";
                    $yAxisitems[] = $yAxisitem;
                    $data["yAxis"] = $yAxisitems;
                    foreach ($iteminfo as $v) {
                        unset($item);
                        unset($temp);
                        $legend[] = $v["comment"];
                        $item["name"] = $v["comment"];
                        $item["type"] = 'line';
                        $item["stack"] = '总量';
                        $item["areaStyle"] = $areaStyle;
                        $rrd = "";
                        $rrd = D("jk_rrd_detail")->where(array("taskid" => $taskid, "itemid" => $v["itemid"]))->getfield("rrdfilename");
                        $temdata = $this->loaddata("list", $rrd, $begintime, $endtime, $taskinfo["frequency"] * 12, $v["datatype"]);
                        unset($xtemp);
                        //print_r($temdata);
                        for ($i = 0; $i < count($temdata); $i++) {
                            $kk = explode(" ", $temdata[$i]);
                            $xtemp[] = date("Y-m-d H:i:s", $kk[0]);
                            $temp[] = $kk[1] ? floatval($kk[1]) : 0;
                            //echo $kk[1]."<br>";
                        }
                        $item["data"] = $temp;
                        $items[] = $item;
                    }

                    $xAxisitem["boundaryGap"] = false;
                    $xAxisitem["data"] = $xtemp; //array('周一', '周二', '周三', '周四', '周五', '周六', '周日');
                    $xAxisitem["type"] = 'category';
                    $xAxisitems[] = $xAxisitem;
                    $data["xAxis"] = $xAxisitems;
                    $data["legend"]["data"] = $legend; // array("1分钟平均负载", "5分钟平均负载", "15分钟平均负载");
                    $data["series"] = $items;
                    echo json_encode($data);
                } else {
                    
                }
                break;
            case "cpuuse":
                $ttid = 28;
                if ($taskinfo["systemtype"] == "linux") {
                    $ttid = 21;
                }
                $data["tooltip"]["trigger"] = 'axis';
                $iteminfo = D("jk_taskitem_2")->where(array("sid" => "$ttid"))->select();
                $yAxisitem["type"] = "value";
                $yAxisitems[] = $yAxisitem;
                $data["yAxis"] = $yAxisitems;
                foreach ($iteminfo as $v) {
                    unset($item);
                    unset($temp);
                    $legend[] = $v["comment"];
                    $item["name"] = $v["comment"];
                    $item["type"] = 'line';
                    $item["stack"] = '总量';
                    $item["areaStyle"] = $areaStyle;
                    $rrd = "";
                    $rrd = D("jk_rrd_detail")->where(array("taskid" => $taskid, "itemid" => $v["itemid"]))->getfield("rrdfilename");
                    $temdata = $this->loaddata("list", $rrd, $begintime, $endtime, $taskinfo["frequency"] * 12, $v["datatype"]);
                    unset($xtemp);
                    //print_r($temdata);
                    for ($i = 0; $i < count($temdata); $i++) {
                        $kk = explode(" ", $temdata[$i]);
                        $xtemp[] = date("Y-m-d H:i:s", $kk[0]);
                        $temp[] = $kk[1] ? floatval($kk[1]) : 0;
                        //echo $kk[1]."<br>";
                    }
                    $item["data"] = $temp;
                    $items[] = $item;
                }

                $xAxisitem["boundaryGap"] = false;
                $xAxisitem["data"] = $xtemp; //array('周一', '周二', '周三', '周四', '周五', '周六', '周日');
                $xAxisitem["type"] = 'category';
                $xAxisitems[] = $xAxisitem;
                $data["xAxis"] = $xAxisitems;
                $data["legend"]["data"] = $legend; // array("1分钟平均负载", "5分钟平均负载", "15分钟平均负载");
                $data["series"] = $items;
                echo json_encode($data);
                break;
            case "mem":
                $ttid = 29;
                if ($taskinfo["systemtype"] == "linux") {
                    if (!$sontype)
                        $sontype = "mem";
                    $ttid = 23;
                }
                $data["tooltip"]["trigger"] = 'axis';
                $iteminfo = D("jk_taskitem_2")->where(array("sid" => "$ttid"))->select();
                $yAxisitem["type"] = "value";
                $yAxisitems[] = $yAxisitem;
                $data["yAxis"] = $yAxisitems;
                $sontype = I("get.sontype");
                foreach ($iteminfo as $v) {
                    unset($item);
                    unset($temp);
                    if ($sontype != "swap" && strstr($v["name"], "swap"))
                        continue;
                    if ($sontype != "mem" && strstr($v["name"], "mem"))
                        continue;
                    $legend[] = $v["comment"];
                    $item["name"] = $v["comment"];
                    $item["type"] = 'line';
                    $item["stack"] = '总量';
                    $item["areaStyle"] = $areaStyle;
                    $rrd = "";
                    $rrd = D("jk_rrd_detail")->where(array("taskid" => $taskid, "itemid" => $v["itemid"]))->getfield("rrdfilename");
                    $temdata = $this->loaddata("list", $rrd, $begintime, $endtime, $taskinfo["frequency"] * 12, $v["datatype"]);
                    unset($xtemp);
                    //print_r($temdata);
                    for ($i = 0; $i < count($temdata); $i++) {

                        $kk = explode(" ", $temdata[$i]);
                        $xtemp[] = date("Y-m-d H:i:s", $kk[0]);
                        $temp[] = $kk[1] ? floatval($kk[1]) : 0;
                        //echo $kk[1]."<br>";
                    }
                    $item["data"] = $temp;
                    $items[] = $item;
                }

                $xAxisitem["boundaryGap"] = false;
                $xAxisitem["data"] = $xtemp; //array('周一', '周二', '周三', '周四', '周五', '周六', '周日');
                $xAxisitem["type"] = 'category';
                $xAxisitems[] = $xAxisitem;
                $data["xAxis"] = $xAxisitems;
                $data["legend"]["data"] = $legend; // array("1分钟平均负载", "5分钟平均负载", "15分钟平均负载");
                $data["series"] = $items;
                echo json_encode($data);
                break;
            case "progress":
                $ttid = 32;
                if ($taskinfo["systemtype"] == "linux") {

                    $ttid = 27;
                }
                $data["tooltip"]["trigger"] = 'axis';
                $iteminfo = D("jk_taskitem_2")->where(array("sid" => "$ttid"))->select();
                $yAxisitem["type"] = "value";
                $yAxisitems[] = $yAxisitem;
                $data["yAxis"] = $yAxisitems;
                $model = D("jk_rrd_detail");
                foreach ($iteminfo as $v) {
                    unset($item);
                    unset($temp);
                    $legend[] = $v["comment"];
                    $item["name"] = $v["comment"];
                    $item["type"] = 'line';
                    $item["stack"] = '总量';
                    $item["areaStyle"] = $areaStyle;
                    $rrd = "";
                    $rrd = $model->where(array("taskid" => $taskid, "itemid" => $v["itemid"]))->getfield("rrdfilename");
                    //die($model->getlastsql());
                    $temdata = $this->loaddata("list", $rrd, $begintime, $endtime, $taskinfo["frequency"] * 12, $v["datatype"]);
                    unset($xtemp);
                    //print_r($temdata);
                    for ($i = 0; $i < count($temdata); $i++) {
                        $kk = explode(" ", $temdata[$i]);
                        $xtemp[] = date("Y-m-d H:i:s", $kk[0]);
                        $temp[] = $kk[1] ? floatval($kk[1]) : 0;
                        //echo $kk[1]."<br>";
                    }
                    $item["data"] = $temp;
                    $items[] = $item;
                }

                $xAxisitem["boundaryGap"] = false;
                $xAxisitem["data"] = $xtemp; //array('周一', '周二', '周三', '周四', '周五', '周六', '周日');
                $xAxisitem["type"] = 'category';
                $xAxisitems[] = $xAxisitem;
                $data["xAxis"] = $xAxisitems;
                $data["legend"]["data"] = $legend; // array("1分钟平均负载", "5分钟平均负载", "15分钟平均负载");
                $data["series"] = $items;
                echo json_encode($data);
                break;
            case "io":
                if ($taskinfo["systemtype"] = "linux") {
                    unset($data);
                    $model = D("jk_rrd_detail");
                    $diskpercent = $model->where(array("sid" => "26", "taskid" => $taskid))->group("ssname")->select();
                    //查询所有磁盘
                    foreach ($diskpercent as $v) {
                        unset($tem);
                        //print_r($v);
                        $disk[$v["ssname"]]["ssid"] = $v["ssid"];
                        $disk[$v["ssname"]]["name"] = $v["ssname"];
                    }
                    //print_r($disk);exit();
                    foreach ($disk as $kk => $vv) {
                        //循环磁盘的两种类型
                        $son = $model->where(array("sid" => "26", "taskid" => $taskid, "ssid" => $vv["ssid"]))->group("substr(itemname,1,2)")->select();
                        for ($i = 0; $i < count($son); $i++) {
                            unset($items);
                            unset($legend);
                            unset($yAxisitems);
                            $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["tooltip"]["trigger"] = 'axis';
                            $iteminfo = D("jk_taskitem_2")->where(array("sid" => "26", "substr(name,1,2)" => substr($son[$i]["itemname"], 0, 2)))->select();

                            $yAxisitems[] = array("type" => "value");
                            $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["yAxis"] = $yAxisitems;
                            //print_r($iteminfo);exit();
                            foreach ($iteminfo as $v) {
                                unset($item);
                                unset($temp);
                                $legend[] = $v["comment"];
                                $item["name"] = $v["comment"];
                                $item["type"] = 'line';
                                $item["stack"] = '总量';
                                $item["areaStyle"] = $areaStyle;
                                $rrd = "";
                                //$rrd = D("jk_rrd_detail")->where(array("taskid" => $taskid, "itemid" => $v["itemid"]))->getfield("rrdfilename");
                                $rrd = $model->where(array("taskid" => $taskid, "ssid" => $vv["ssid"], "itemid" => $v["itemid"]))->getfield("rrdfilename");
                                //die($rrd);
                                //die($model->getLastSql());
                                $temdata = $this->loaddata("list", $rrd, $begintime, $endtime, $taskinfo["frequency"] * 12, $v["datatype"]);
                                unset($xtemp);
                                for ($j = 0; $j < count($temdata); $j++) {
                                    $kk = explode(" ", $temdata[$j]);
                                    $xtemp[] = date("Y-m-d H:i:s", $kk[0]);
                                    $temp[] = $kk[1] ? floatval($kk[1]) : 0;
                                }
                                $item["data"] = $temp;
                                $items[] = $item;
                            }

                            //print_r($son[$i]);
                            unset($xAxisitems);
                            $xAxisitem["boundaryGap"] = false;
                            $xAxisitem["data"] = $xtemp;
                            $xAxisitem["type"] = 'category';
                            $xAxisitems[] = $xAxisitem;
                            //die("aaa");
                            //die(substr($son[$i]["itemname"],0,2));
                            $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["xAxis"] = $xAxisitems;
                            $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["legend"]["data"] = $legend;
                            $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["series"] = $items;

                            //$datas[$vv["id"]]=$data;
                            //unset($data);
                            //echo json_encode($data);
                            //exit();
                        }
                    }
                    echo json_encode($data);
                } else {
                    
                }
                break;
            case "diskuse":
                $ttid = 31;
                $itemids = "46,47";
                if ($taskinfo["systemtype"] == "linux") {
                    $itemids = "30,31";
                    $ttid = 25;
                }
                unset($data);
                $model = D("jk_rrd_detail");
                $diskpercent = $model->where(array("sid" => "$ttid", "taskid" => $taskid))->group("ssname")->select();
                //查询所有磁盘
                foreach ($diskpercent as $v) {
                    unset($tem);
                    $disk[$v["ssname"]]["ssid"] = $v["ssid"];
                    $disk[$v["ssname"]]["name"] = $v["ssname"];
                }
                foreach ($disk as $kk => $vv) {
                    unset($items);
                    unset($legend);
                    unset($yAxisitems);
                    $data[$vv["ssid"] . "part_use"]["tooltip"]["trigger"] = 'axis';
                    $modelitem = D("jk_taskitem_2");
                    $iteminfo = $modelitem->where(array("sid" => "$ttid", "itemid" => array("in", "$itemids")))->select();
                    //die($modelitem->getlastsql());

                    $yAxisitems[] = array("type" => "value");
                    $data[$vv["ssid"] . "part_use"]["yAxis"] = $yAxisitems;
                    //print_r($iteminfo);exit();
                    foreach ($iteminfo as $v) {
                        unset($item);
                        unset($temp);
                        $legend[] = $v["comment"];
                        $item["name"] = $v["comment"];
                        $item["type"] = 'line';
                        $item["stack"] = '总量';
                        $item["areaStyle"] = $areaStyle;
                        $rrd = "";
                        //$rrd = D("jk_rrd_detail")->where(array("taskid" => $taskid, "itemid" => $v["itemid"]))->getfield("rrdfilename");
                        $rrd = $model->where(array("taskid" => $taskid, "ssid" => $vv["ssid"], "itemid" => $v["itemid"]))->getfield("rrdfilename");
                        //die($rrd);
                        //die($model->getLastSql());
                        $temdata = $this->loaddata("list", $rrd, $begintime, $endtime, $taskinfo["frequency"] * 12, $v["datatype"]);
                        unset($xtemp);
                        for ($j = 0; $j < count($temdata); $j++) {
                            $kk = explode(" ", $temdata[$j]);
                            $xtemp[] = date("Y-m-d H:i:s", $kk[0]);
                            $temp[] = $kk[1] ? floatval($kk[1]) : 0;
                        }
                        $item["data"] = $temp;
                        $items[] = $item;

                        unset($xAxisitems);
                        $xAxisitem["boundaryGap"] = false;
                        $xAxisitem["data"] = $xtemp;
                        $xAxisitem["type"] = 'category';
                        $xAxisitems[] = $xAxisitem;
                        $data[$vv["ssid"] . "part_use"]["xAxis"] = $xAxisitems;
                        $data[$vv["ssid"] . "part_use"]["legend"]["data"] = $legend;
                        $data[$vv["ssid"] . "part_use"]["series"] = $items;

                        //$datas[$vv["id"]]=$data;
                        //unset($data);
                        //echo json_encode($data);
                        //exit();
                    }
                }
                echo json_encode($data);
                break;
            case "net":
                $ttid = 30;
                if ($taskinfo["systemtype"] == "linux") {

                    $ttid = 24;
                }
                unset($data);
                $model = D("jk_rrd_detail");
                $diskpercent = $model->where(array("sid" => "$ttid", "taskid" => $taskid))->group("ssname")->select();
                //查询所有磁盘
                foreach ($diskpercent as $v) {
                    unset($tem);
                    //print_r($v);
                    $disk[$v["ssname"]]["ssid"] = $v["ssid"];
                    $disk[$v["ssname"]]["name"] = $v["ssname"];
                }
                //print_r($disk);exit();
                foreach ($disk as $kk => $vv) {
                    //循环磁盘的两种类型
                    $son = $model->where(array("sid" => "$ttid", "taskid" => $taskid, "ssid" => $vv["ssid"]))->group("substr(itemname,1,2)")->select();
                    //print_r($son);exit();
                    for ($i = 0; $i < count($son); $i++) {
                        unset($items);
                        unset($legend);
                        unset($yAxisitems);
                        $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["tooltip"]["trigger"] = 'axis';
                        $iteminfo = D("jk_taskitem_2")->where(array("sid" => "$ttid", "substr(name,1,2)" => substr($son[$i]["itemname"], 0, 2)))->select();

                        $yAxisitems[] = array("type" => "value");
                        $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["yAxis"] = $yAxisitems;
                        //print_r($iteminfo);exit();
                        foreach ($iteminfo as $v) {
                            unset($item);
                            unset($temp);
                            $legend[] = $v["comment"];
                            $item["name"] = $v["comment"];
                            $item["type"] = 'line';
                            $item["stack"] = '总量';
                            $item["areaStyle"] = $areaStyle;
                            $rrd = "";
                            //$rrd = D("jk_rrd_detail")->where(array("taskid" => $taskid, "itemid" => $v["itemid"]))->getfield("rrdfilename");
                            $rrd = $model->where(array("taskid" => $taskid, "ssid" => $vv["ssid"], "itemid" => $v["itemid"]))->getfield("rrdfilename");
                            //die($rrd);
                            //die($model->getLastSql());
                            $temdata = $this->loaddata("list", $rrd, $begintime, $endtime, $taskinfo["frequency"] * 12, $v["datatype"]);
                            unset($xtemp);
                            for ($j = 0; $j < count($temdata); $j++) {
                                $kk = explode(" ", $temdata[$j]);
                                $xtemp[] = date("Y-m-d H:i:s", $kk[0]);
                                $temp[] = $kk[1] ? floatval($kk[1]) : 0;
                            }
                            $item["data"] = $temp;
                            $items[] = $item;
                        }

                        //print_r($son[$i]);
                        unset($xAxisitems);
                        $xAxisitem["boundaryGap"] = false;
                        $xAxisitem["data"] = $xtemp;
                        $xAxisitem["type"] = 'category';
                        $xAxisitems[] = $xAxisitem;
                        //die("aaa");
                        //die(substr($son[$i]["itemname"],0,2));
                        $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["xAxis"] = $xAxisitems;
                        $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["legend"]["data"] = $legend;
                        $data[$vv["ssid"] . substr($son[$i]["itemname"], 0, 2)]["series"] = $items;

                        //$datas[$vv["id"]]=$data;
                        //unset($data);
                        //echo json_encode($data);
                        //exit();
                    }
                }
                echo json_encode($data);
                break;
        }
    }

    public function getdata() {
        
    }

    public function index() {

        $model = D("jk_task");
        $map ['uid'] = session("uid");
        $map ['is_del'] = 0;
        $map ['jk_task.sid'] = 2;
        $count = $model->where($map)->count("1");
        //echo $model->getlastsql();//exit();
        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = C('PAGE_LISTROWS');
            }
            //echo $listRows;
            $p = new \Think\Adminpage($count, $listRows);
            //分页查询数据
            $order = "id";
            $sort = " desc ";
            $tasklist = $model->where($map)->join('jk_tasktype ON jk_task.sid = jk_tasktype.sid')->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            $page = $p->show();
            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式
            //模板赋值显示
            $this->assign("page", $page);
        }
        $this->assign('totalCount', $count);
        $this->assign('numPerPage', C('PAGE_LISTROWS'));
        $this->assign('currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
        //echo $taskModel->getLastSql();
        $this->assign("sitetitle", C('sitetitle'));
        $this->assign("tasklist", $tasklist);
        $this->display();
    }

    public function taskadd() {
        $now = date("Y-m-d H:i:s");
        $sid = 2; // 服务器任务 类型
        $taskModel = D('Task');
        $_POST["labels"] = ":" . implode(":,:", $_POST["labels"]) . ":";
        $_POST["sid"] = 2;
        $_POST["mids"] = ":0:";
        $_POST["addtime"] = date("Y-m-d H:i:s");
        $_POST["frequency"] = intval($_POST["frequency"] * 60);
        $_POST["lasttime"] = serialize(array("0" => time()));
        $_POST["uid"] = session("uid");
        $servertask = $_POST["servertask"];
        foreach ($servertask as $v) {
            $_POST[$v] = 1;
        }
        if (false === $taskModel->create()) {
            $this->error($taskModel->getError());
        }
        $taskModel->startTrans();
        $insertid = $taskModel->add();
        if ($insertid !== false) { //保存成功
            $taskDetailsModel = D('Taskdetails2');
            $_POST["taskid"] = $insertid;
            if (false === $taskDetailsModel->create()) {
                $taskModel->rollback();
                $this->error($taskDetailsModel->getError());
            }
            $insertid = $taskDetailsModel->add();
            if ($insertid !== false) { //保存成功
                $taskModel->commit();
                $this->success('新增成功!');
            } else {
                $taskModel->rollback();
                $this->error('新增失败!');
            }
        } else {
            //失败提示
            $this->error('新增失败!');
        }
        exit();
    }

    public function create() {
        $this->assign("sitetitle", C('sitetitle'));
        $tasktype_name = I("get.ttype");
        $tasktype = $this->getTaskType("server", 1);
        if (count($tasktype) == 0) {
            $this->error("请求错误");
        }
        // 监控项
        $alarmitems = D(("jk_taskitem_" . $tasktype ['sid']))->where(array(
                    "is_alarm" => 1,
                    "is_sonitem" => 1,
                    "isuse" => 1
                ))->select();

        $this->assign("lables", D("jk_lables")->field("id,title")->where(array("uid" => $_SESSION["uid"]))->select());
        $this->assign("alarmitems", $alarmitems);

        $this->assign("grouplist", D("Alarmgroup")->where(array("uid" => $_SESSION["uid"]))->select());
        $this->display('server');
    }

    public function delete() {
        $r = $this->is_login(0);

        if (!$r) {
            exit("请登录后操作");
        }
        $taskid = I("post.tid");

        if ($taskid == "") {
            // $this->error("请求错误");
            exit("请求错误");
        }

        $taskModel = D("jk_task");
        $data ["is_del"] = 1;
        $data ["status"] = 2;
        $n = $taskModel->where(array(
                    "id" => $taskid
                ))->save($data);
        if ($n) {
            // $this->success("删除成功",U("Task/tasklist"));
            echo "1";
        } else {
            echo "2";
        }
    }

    public function updatealarm() {
        $aid = I("post.aid");
        $sid = 2;
        if ($aid == "" || $sid == "") {
            // $this->error("请求错误");
            exit("请求错误");
        }

        // print_r($_POST);
        // exit();
        $taskModel = D("jk_trigger_ruls");
        $alarm = array();
        //if($sid == '1'){

        $taskitemtable = "jk_taskitem_" . $sid;
        $alarm = $taskModel->where(array(
                    "jk_trigger_ruls.id" => $aid
                ))->join($taskitemtable . ' ON jk_trigger_ruls.index_id = ' . $taskitemtable . '.itemid')->find();
        //}

        $return = array();
        $return['status'] = 1;
        $return['data'] = $alarm;
        echo json_encode($return);
    }

    public function delalarm() {
        $aid = I("post.aid");

        if ($aid == "") {
            // $this->error("请求错误");
            exit("请求错误");
        }

        $taskModel = D("jk_trigger_ruls");
        // $data ["is_del"] = 1;
        // $data ["status"] = 2;
        // $n = $taskModel->where ( array (
        // "id" => $taskid
        // ) )->save ( $data );
        $n = $taskModel->delete($aid);
        if ($n) {
            // $this->success("删除成功",U("Task/tasklist"));
            echo "1";
        } else {
            echo "2";
        }
    }

    public function update() {

        $taskid = I("get.tid");
        if ($taskid == "") {
            $this->error("请求错误");
            // exit("请求错误");
        }

        $taskModel = D("jk_task");
        $map = array();
        $map ['id'] = $taskid;
        $map ['is_del'] = 0;
        $task = $taskModel->where($map)->find();

        if (!$task) {
            $this->error("任务不存在！");
        }

        $sid = $task ['sid'];
        $task ['frequency'] = ($task ['frequency'] / 60);

        // 预置监控报警项
        // 获取details
        $taskdetailsModel = D('jk_taskdetails_' . $sid);
        $taskdetailsAdvModel = D('jk_taskdetails_adv_' . $sid);
        $triggerModel = D('jk_trigger_ruls');

        $map = array();
        $map ['taskid'] = $taskid;
        $taskdetail = $taskdetailsModel->where($map)->find();

        // 获取高级选项
        $taskdetailsAdv = $taskdetailsAdvModel->where($map)->find();

        // 获取告警项
        $map = array();
        $map ['task_id'] = $taskid;
        $triggers = $triggerModel->where($map)->join("jk_taskitem_" . $sid . " on jk_trigger_ruls.index_id = jk_taskitem_" . $sid . ".itemid")->select();
        $mitem = D("jk_taskitem_2");
        $alarmitems = $mitem->field("jk_rrd_detail.id,jk_taskitem_2.comment,jk_rrd_detail.ssname,jk_rrd_detail.sid")->join("right join jk_rrd_detail on jk_rrd_detail.itemid=jk_taskitem_2.itemid")->where(array("jk_rrd_detail.taskid" => $taskid, "jk_taskitem_2.is_alarm" => 1))->order("jk_taskitem_2.itemid asc")->select();
        //echo $mitem->getlastsql();
        //print_r($alarmitems);exit();
// 监控点
        $mps = $this->getMonitoryPoint();
        for ($i = 0; $i < count($mps); $i ++) {
            $mid = ":" . $mps [$i] ['id'] . ":";
            $r = strstr($task ['mids'], $mid);
            if ($r === FALSE) {
                $mps [$i] ['isdefault'] = 0;
            } else {
                $mps [$i] ['isdefault'] = 1;
            }
        }


        $this->assignbase();
        $this->assign("lables", D("jk_lables")->field("id,title")->where(array("uid" => $_SESSION["uid"]))->select());
        $this->assign("update", 1);
        //echo $task["lables"]."|";
        $task["labels"] = explode(":", $task["labels"]);
        $this->assign("task", $task);
        $this->assign("taskdetail", $taskdetail);
        //echo $task["lables"];
        //print_r($task);
        $this->assign("taskadvdata", $taskdetailsAdv);
        $this->assign("triggers", $triggers);
        $this->assign("alarmitems", $alarmitems);
        $this->assign("mps", $mps);
        $this->assign("grouplist", D("Alarmgroup")->where(array("uid" => $_SESSION["uid"]))->select());

        $this->assign("cbserver", $cbserver);
        $this->assign("cbip", $cbip);
        $this->display('update');
    }

    public function taskupdate() {
        $alarm_num = I('post.alarm_num');
        //die();
        $taskModel = D('Task');
        $_POST["labels"] = ":" . implode(":,:", $_POST["labels"]) . ":";
        $servertask = $_POST["servertask"];
        foreach ($servertask as $v) {
            $_POST[$v] = 1;
        }
        if (false === $taskModel->create()) {
            $this->error($taskModel->getError());
        }
        $taskModel->startTrans();
        $insertid = $taskModel->save();
        $taskid = I("post.id");
        if ($insertid !== false) { //保存成功
            $taskDetailsModel = D('Taskdetails2');
            unset($_POST["id"]);
            if (false === $taskDetailsModel->create()) {
                $taskModel->rollback();
                $this->error($taskDetailsModel->getError());
            }
            $insertid = $taskDetailsModel->where(array("taskid" => $taskid))->save();
            if ($insertid !== false) { //保存成功
                if ($alarm_num > 0) {
                    $flag = 0;
                    $triggerModel = D('jk_trigger_ruls');
                    for ($i = 0; $i < $alarm_num; $i ++) {
                        $key = "post.a" . $i;
                        $alarm = I($key);
                        if ($alarm == "del") {
                            continue;
                        }
                        $alist = explode(",", $alarm);
                        list ( $a_itemid, $a_operator, $threshold, $unit, $calc, $atimes ) = $alist;
                        $triggerid = $alist [9];
                        $rrdinfo = D("jk_rrd_detail")->where(array("id" => $a_itemid))->find();
                        if (!$rrdinfo) {
                            $taskModel->rollback();
                            $this->error('更新失败2!');
                        }
                        $data = array(
                            "task_id" => $taskid,
                            "data_calc_func" => "avg",
                            "operator_type" => $a_operator,
                            "threshold" => $threshold,
                            "data_times" => $atimes,
                            "index_id" => $rrdinfo["itemid"],
                            "sonitem_id" => ($rrdinfo["ssid"] < 1) ? "" : $rrdinfo["ssid"],
                            "monitor_id" => "0",
                            "rrd_name" => $rrdinfo["rrdfilename"],
                            "rrd_id" => $rrdinfo["id"],
                            "is_monitor_avg" => 1
                        );
                        if ($triggerid > 0) {
                            $flag = $triggerModel->where(array(
                                        "id" => $triggerid
                                    ))->save($data);
                        } else {
                            $flag = $triggerModel->add($data);
                        }
                    }
                    if ($flag == 0) {
                        $this->error("ERROR4");
                    }
                }
                $taskModel->commit();


                $this->success('更新成功!');
            } else {
                $taskModel->rollback();
                $this->error('更新失败!');
            }
        } else {
            //失败提示
            $this->error('更新失败!');
        }
        exit();
    }

}
