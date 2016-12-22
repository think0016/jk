<?php

namespace Jk\Controller;

use Think\Controller;

class AlarmgroupController extends BaseController {

    function index() {
        $map["jk_alarm_group.uid"] = $_SESSION["uid"];
        $grouplist = D("jk_alarm_group")->where($map)->select();
        $this->assign("list", $grouplist);
        $this->display();
    }

    function add() {
        if (!IS_POST) {
            $map["uid"] = $_SESSION["uid"];
            $grouplist = D("jk_alarm_group")->where($map)->select();
            $this->assign("list", $grouplist);
            $this->display();
        } else {
            $model = D("jk_alarm_group");
            
            if(!I("post.email"))
                $_POST["email"]=0;
            if(!I("post.message"))
                $_POST["message"]=0;
            $_POST["uid"]=$_SESSION["uid"];
            if (false === $model->create()) {
                $this->error($model->getError());
            }
            $list = $model->add();
            if ($list !== false) { //保存成功
                if ($pid)
                    $this->success('新增成功!', U(CONTROLLER_NAME));
                else
                    $this->success('新增成功!');
            } else {
                //失败提示
                $this->error('新增失败!');
            }
        }
    }

    function edit() {
        if (!IS_POST) {
            $model = D("jk_alarm_group");
            $map["uid"] = $_SESSION["uid"];
            $map["id"] = I("get.uid");
            $groupinfo = $model->where($map)->find();
            //print_r($model->getLastSql());exit();
            $this->assign("vo", $groupinfo);
            $this->display();
        } else {
            $model = D("jk_alarm_group");
            $map["uid"] = $_SESSION["uid"];
            $map["id"] = I("post.id");
            if(!I("post.email"))
                $_POST["email"]=0;
            if(!I("post.message"))
                $_POST["message"]=0;
            $groupinfo = $model->where($map)->find();
            //die($model->getLastSql());
            if(!$groupinfo)
                $this->error('您不能修改该报警组!');
            //print_r($_POST);exit();
            if (false === $model->create()) {
                $this->error($model->getError());
            }
            $list = $model->save();
            if ($list !== false) { //保存成功
                if ($pid)
                    $this->success('修改成功!', U(CONTROLLER_NAME));
                else
                    $this->success('修改成功!');
            } else {
                //失败提示
                $this->error('修改失败!');
            }
        }
    }

}
