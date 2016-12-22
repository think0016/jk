<?php

namespace Jk\Controller;

use Think\Controller;

class AlarmnoticesetController extends BaseController {

    function index() {
        $map["jk_alarm_group.uid"] = $_SESSION["uid"];
        $grouplist = D("jk_alarm_users")->field("jk_alarm_users.id,jk_alarm_users.name,jk_alarm_users.tel,jk_alarm_users.email")->join("jk_alarm_group on jk_alarm_users.group_id=jk_alarm_group.id")->where($map)->select();
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
            $model = D("Alarmusers");
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
            $map["uid"] = $_SESSION["uid"];
            $grouplist = D("jk_alarm_group")->where($map)->select();
            foreach ($grouplist as $v) {
                $groups[] = $v["id"];
            }
            $model = D("Alarmusers");
            $userinfo = $model->where(array("id" => I("get.uid"), "group_id" => array("in", $groups)))->find();
            $this->assign("vo", $userinfo);
            $this->assign("list", $grouplist);
            $this->display();
        } else {
            $model = D("Alarmusers");
            $map["uid"] = $_SESSION["uid"];
            $map["id"] = I("post.group_id");
            $grouplist = D("jk_alarm_group")->where($map)->select();
            if (!$grouplist)
                $this->error('您不能选择该用户组!');
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

    function del() {
        $model = D("Alarmusers");
        $userinfo = $model->where(array("id" => I("get.uid")))->find();
        if (!$userinfo)
            $this->error('通知用户不存在!');
        $map["uid"] = $_SESSION["uid"];
        $map["id"] = $userinfo["group_id"];
        $grouplist = D("jk_alarm_group")->where($map)->select();
        if (!$grouplist)
            $this->error('您不能删除该用户!');
        $model->where(array("id" => I("get.uid")))->delete();
        $this->success('用户删除成功!');
    }

}
