<?php

namespace Jk\Controller;

use Think\Controller;

class LablesController extends BaseController {

    function index() {
        $map["uid"] = $_SESSION["uid"];
        $lableslist = D("Lables")->where($map)->select();
        $this->assign("list", $lableslist);
        $this->display();
    }

    function add() {
        if (!IS_POST) {
            $this->display();
        } else {
            $model = D("Lables");
            if(!I("post.state"))
                $_POST["state"]=0;
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
            $model = D("Lables");
            $map["uid"] = $_SESSION["uid"];
            $map["id"] = I("get.uid");
            $groupinfo = $model->where($map)->find();
            //print_r($model->getLastSql());exit();
            $this->assign("vo", $groupinfo);
            $this->display();
        } else {
            $model = D("Lables");
            $map["uid"] = $_SESSION["uid"];
            $map["id"] = I("post.id");
            if(!I("post.state"))
                $_POST["state"]=0;
            $groupinfo = $model->where($map)->find();
            //die($model->getLastSql());
            if(!$groupinfo)
                $this->error('您不能修改该标签!');
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
