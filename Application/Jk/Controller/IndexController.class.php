<?php
namespace Jk\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index()
    {
    	$this->assign("sitetitle",C('sitetitle'));
        $model=D("jk_task");
        $typecount=$model->field("stype,count(jk_task.id) as count")->join("jk_tasktype on jk_task.sid=jk_tasktype.sid")->where(array("jk_task.uid"=>$_SESSION["uid"]))->group("jk_tasktype.stype")->select();
        $typelist=$model->field("stype,jk_task.id as id")->join("jk_tasktype on jk_task.sid=jk_tasktype.sid")->select();
        $task=$model->query("select type,task_id from (select type,task_id,trigger_id from jk_alarms_list where task_id in(select id from jk_task where uid=".$_SESSION["uid"].") order by id desc) as a where a.type=0 group by a.task_id ");
        //print_r($task);exit();
        foreach($task as $k=>$v)
        {
            $item[]=$v["task_id"];
        }
        //echo "<br>";
        foreach($typelist as $vv)
        {
            if(in_array($vv["id"],$item))
            {
                $breaktmp[$vv["stype"]]++;
            }
        }
        
        foreach($breaktmp as $vvv)
            $break[]=$vvv;
        $ok[]=$typecount[0]["count"]-$break[0];
        $ok[]=$typecount[1]["count"]-$break[1];
        $ok[]=$typecount[2]["count"]-$break[2];
        $this->assign("break",  json_encode($break));
        $this->assign("ok",  json_encode($ok));
        
        $model=D("jk_alarms_list");
        $alarmlist=$model->query("select * from (select type,task_id,trigger_id,times,id from jk_alarms_list where task_id in(select id from jk_task where uid=".$_SESSION["uid"].") order by id desc) as a group by a.trigger_id order by a.type desc");
        //print_r($alarmlist);exit();
        $this->assign("alarmlist",  $alarmlist);
        
        $webtask=D("jk_task")->where(array("uid"=>$_SESSION["uid"],"status"=>1,"_string"=>" sid in(select sid from jk_tasktype where stype=1)"))->limit(5)->select();
        for($i=0;$i<count($webtask);$i++)
        {
            $breakwebtasknum=0;
            $breakwebtasknum=getbreakalarm($webtask[$i]["id"],0);
            $webtask[$i]["state"]=$break>0?"正常":"异常";
            $webtask[$i]["status"]=getpresent(getwebstatus($webtask[$i]["id"])*100);
        }
        $this->assign("webtask",  $webtask);
        
        $servertask=D("jk_task")->where(array("uid"=>$_SESSION["uid"],"status"=>1,"_string"=>" sid in(select sid from jk_tasktype where stype=2)"))->limit(5)->select();
        for($i=0;$i<count($servertask);$i++)
        {
            $breakservertasknum=0;
            $breakservertasknum=getbreakalarm($servertask[$i]["id"],0);
            $servertask[$i]["process"]=intval(gettaskprocess($servertask[$i]["id"]));
            $servertask[$i]["memuse"]=gettaskmemuse($servertask[$i]["id"])."%";
            $servertask[$i]["cpuuse"]=gettaskcpuuse($servertask[$i]["id"])."%";
            $servertask[$i]["state"]=$break>0?"正常":"异常";
        }
        $this->assign("servertask",  $servertask);
        
        $servicetask=D("jk_task")->where(array("uid"=>$_SESSION["uid"],"status"=>1,"_string"=>" sid in(select sid from jk_tasktype where stype=3)"))->limit(5)->select();
        for($i=0;$i<count($servicetask);$i++)
        {
            $breakservicetasknum=0;
            $breakservicetasknum=getbreakalarm($servicetask[$i]["id"],0);
            $servicetask[$i]["state"]=$break>0?"正常":"异常";
        }
        $this->assign("servicetask",  $servicetask);
        
        $this->display();
        
    }
    
    public function test() {
    	redirect(U('/Index/testx'), 0, '页面跳转中...');
    }
    
    public function testx() {
    	echo "aaa";
    }
    
}