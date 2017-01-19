<?php

function getnetsize($num) {
    if ($num >= 1024 * 1024 * 1024 * 1024)
        return sprintf("%.2f", $num / 1024 / 1024 / 1024 / 1024) . "Tb";
    if ($num >= 1024 * 1024 * 1024)
        return sprintf("%.2f", $num / 1024 / 1024 / 1024) . "Tb";
    if ($num >= 1024 * 1024)
        return sprintf("%.2f", $num / 1024 / 1024) . "Gb";
    if ($num >= 1024)
        return sprintf("%.2f", $num / 1024) . "Mb";
    if ($num >= 0)
        return sprintf("%.2f", $num) . "Kb";
    return $num;
}

function getsize($num, $type = "",$withunit=1) {
        switch ($type) {
            case "T":
                $num= sprintf("%.2f", $num / 1024 / 1024 / 1024) . ($withunit?"Tb":"");
                break;
            case "G":
                $num= sprintf("%.2f", $num / 1024 / 1024) . ($withunit?"Gb":"");
                break;
            case "M":
                $num= sprintf("%.2f", $num / 1024) . ($withunit?"Mb":"");
                break;
            case "K":
                $num= sprintf("%.2f", $num) . ($withunit?"Kb":"");
                break;
            default :
                if ($num >= 1024 * 1024 * 1024)
                    return sprintf("%.2f", $num / 1024 / 1024 / 1024) . "Tb";
                if ($num >= 1024 * 1024)
                    return sprintf("%.2f", $num / 1024 / 1024) . "Gb";
                if ($num >= 1024)
                    return sprintf("%.2f", $num / 1024) . "Mb";
                if ($num >= 0)
                    return sprintf("%.2f", $num) . "Kb";
        }
    return $num;
}

function getlastinfo($str, $id) {
    $str = $str[0];
    $arr = explode(" ", $str);
    //return $arr;
    return $arr[$id];
}

function getavginfo($str) {
    $str = $str[0];
    return $str;
}

function getmaxinfo($str) {
    $str = $str[0];
    return $str;
}

function getpresent($num) {
    return (intval($num)) . "%";
}

function s2m($second) {
    return $second / 60;
}

function gettasktypeinfo($id, $field = "") {
    $model = D("jk_tasktype");
    $map["sid"] = $id;
    if (!$field) {
        $data = $model->where($map)->find();
    } else {
        $data = $model->where($map)->getfield($field);
    }
    //print_r($data);
    return $data;
}

function isornot($id) {
    $ids[] = "关闭";
    $ids[] = "开启";
    return $ids[$id];
}

function isok($id) {
    $ids[] = "已解决";
    $ids[] = "未解决";
    return $ids[$id];
}

function getstatusname($id) {
    $ids[] = "禁用";
    $ids[] = "启用";
    return $ids[$id];
}

function getbreakalarm($taskid, $type) {
    $model = D("jk_alarms_list");
    $list = $model->query("select type from (select type,task_id,trigger_id from jk_alarms_list where task_id=$taskid order by id desc) as a group by a.trigger_id");
    foreach ($list as $k => $v) {
        if ($v["type"] == $type)
            $count++;
    }
    //return $model->getLastSql();
    return $count ? $count : 0;
}

function getalarmcount($taskid) {
    $model = D("jk_alarms_list");
    $list = $model->query("select type from (select type,task_id,trigger_id from jk_alarms_list where task_id=$taskid order by id desc) as a group by a.trigger_id");
    return count($list);
}

function getalarmtext($triggerid) {
    //die($triggerid);
    $data = D("jk_trigger_ruls")->where(array("id" => $triggerid))->find();
    //print_r($data);
    $taskinfo = D("jk_task")->where(array("id" => $data["task_id"]))->find();
    //print_r($taskinfo);exit();
    if ($taskinfo["sid"] == 2) {
        $itemname = D("jk_rrd_detail")->where(array("id" => $data["rrd_id"]))->getfield("ssname");
        $itemname .=D("jk_taskitem_" . $taskinfo["sid"])->where(array("itemid" => $data["index_id"]))->getfield("comment");
    } else {
        $itemname = D("jk_taskitem_" . $taskinfo["sid"])->where(array("itemid" => $data["index_id"]))->getfield("comment");
    }
    $threshold = $data["threshold"];
    $iunit = $data["iunit"];
    $operator_type = $data["operator_type"];
    return getalarmcontent($threshold, $itemname, $iunit, $operator_type);
}

function getalarmcontent($threshold, $itemname, $iunit, $operator_type) {
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

function maketimestr($num) {
    if ($num / (365 * 24 * 60 * 60) >= 1) {
        $str = intval($num / 365 / 24 / 60 / 60) . "年";
        $num = $num % (365 * 24 * 60 * 60);
    }if ($num / (24 * 60 * 60) >= 1) {
        $str .=intval($num / 24 / 60 / 60) . "天";
        $num = $num % (24 * 60 * 60);
    }if ($num / (60 * 60) >= 1) {
        $str .=intval($num / 60 / 60) . "小时";
        $num = $num % (60 * 60);
    }if ($num / 60 >= 1) {
        $str .=intval($num / 60) . "分钟";
        $num = $num % 60;
    }
    if ($num >= 1)
        $str.=$num . "秒";
    return $str;
}

function getalarmtime($id) {
    $model = D("jk_alarms_list");
    $alarminfo = $model->where(array("id" => $id))->order("id desc")->find();
    if ($alarminfo["type"] == "1") {
        //return $alarminfo["times"];
        return maketimestr(time() - $alarminfo["times"]);
    } else {
        $alarminfo2 = $model->where(array("id" => $id))->limit(2, 1)->order("id desc")->select();
        //return $alarminfo["times"];
        return maketimestr($alarminfo["times"] - $alarminfo2[0]["times"]);
    }
}

function gettasktypebytaskid($taskid) {
    $model = D("jk_task");
    $sid = $model->where(array("id" => $taskid))->getfield("sid");
    return D("jk_tasktype")->where(array("sid" => $sid))->getfield("name");
}

function gettasktype($sid) {
    return D("jk_tasktype")->where(array("sid" => $sid))->getfield("name");
}

function gettaskname($taskid) {
    $model = D("jk_task");
    return $model->where(array("id" => $taskid))->getfield("title");
}

function gettasksystem($taskid) {
    $model = D("jk_taskdetails_2");
    return $model->where(array("task_id" => $taskid))->getfield("systemtype");
}

function gettaskprocess($taskid) {
    $model = D("jk_rrd_detail");
    $rrdname = $model->where(array("taskid" => $taskid, "itemid" => array("in", array(34, 50))))->getfield("rrdfilename");
    //return $rrdname;
    $taskinfo = D("jk_task")->where(array("id" => $taskid))->find();
    return getlastdata($rrdname, $taskinfo["frequency"], 1);
}

function gettaskmemuse($taskid) {
    $model = D("jk_rrd_detail");
    $rrdname = $model->where(array("taskid" => $taskid, "itemid" => array("in", array(20, 40))))->getfield("rrdfilename");
    //return $rrdname;
    $taskinfo = D("jk_task")->where(array("id" => $taskid))->find();
    return getlastdata($rrdname, $taskinfo["frequency"], 1);
}

function gettaskcpuuse($taskid) {
    $model = D("jk_rrd_detail");
    $rrdname = $model->where(array("taskid" => $taskid, "itemid" => array("in", array(9, 37))))->getfield("rrdfilename");
    //return $rrdname;
    $taskinfo = D("jk_task")->where(array("id" => $taskid))->find();
    return getlastdata($rrdname, $taskinfo["frequency"], 1);
}

function getlastdata($rrd, $step, $datatype) {
    $common = "/var/www/ce/cmd/get_last.sh   /var/www/ce/rrd/$rrd $step $datatype";
    $result = exec($common, $status);
    //return $status;
    //return print_r($status,1);
    return getlastinfo($status, 1);
}

function getwebstatus($taskid) {
    $taskinfo = D("jk_task")->where(array("id" => $taskid))->find();
    $itemid = D("jk_taskitem_" . $taskinfo["sid"])->where(array("name" => "status"))->getfield("itemid");
    $mids = $taskinfo["mids"];
    $mids = explode(",", str_replace(":", "", $mids));
    $result = 0;
    for ($i = 0; $i < count($mids); $i++) {
        $rrdname = "";
        $rrdname = $taskid . "_" . $taskinfo["uid"] . "_" . $mids[$i] . "_" . $taskinfo["sid"] . "_" . $taskinfo["ssid"] . "_" . $itemid . ".rrd";
        if ($rrdname)
            $result +=getlastdata($rrdname, $taskinfo["frequency"], 1);
        //return $rrdname;
    }
    //return $result;
    return $result / count($mids);
}

function gettaskalarm($taskid, $sid) {
    $taskinfo = D("jk_task")->join("jk_tasktype on jk_task.sid=jk_tasktype.sid")->where(array("id" => $taskid))->find();
    if ($taskinfo["stype"] != "2")
        return U(str_replace("sqlserver", "SqlServer", $taskinfo["name"]) . "View/index/tid/" . $taskinfo["id"]);
    if ($taskinfo["stype"] == "2")
        return U("TaskServer/showalarmlist/taskid/" . $taskinfo["id"]);
    return $taskid;
}

function makefirstup($str) {
    return strtoupper(substr($str, 0, 1)) . substr($str, 1);
}


?>