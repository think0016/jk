<?php

namespace Jk\Controller;

use Think\Controller;

class UsercenterController extends BaseController {

    function index() {
        if (IS_POST) {
            $_POST["id"] = $_SESSION["uid"];
            $model = D("User");
            if (false === $model->create()) {
                $this->error($model->getError());
            }
            $list = $model->save();
            if (false !== $list) {
                //成功提示
                $this->success('编辑成功!',U("Usercenter/index"));
            } else {
                //错误提示
                $this->error('编辑失败!');
            }
        } else {
            $map["id"] = $_SESSION["uid"];
            $userinfo = D("jk_user")->where($map)->find();
            $this->assign("vo", $userinfo);
            $this->display();
        }
    }
    function account() {
        $data=D("User")->join("jk_package on jk_user.level=jk_package.level")->where(array("jk_user.id"=>$_SESSION["uid"]))->find();
        $this->assign("vo", $data);
        $this->display();
    }
    function resetpwd() {
        if (IS_POST) {
            $_POST["id"] = $_SESSION["uid"];
            $model = D("User");
            if(I("post.password")=="")
            {
                $this->error('新密码不能为空!');
            }
            if(I("post.password")!=I("post.password"))
            {
                $this->error('两次新密码输入不一致!');
            }
            $map["password"]=md5(I("post.oldpassword").C('md5_salt'));
            $isoldpassok=$model->where($map)->find();
            if(!$isoldpassok)
                $this->error('原密码输入不正确!');
            $list = $model->save($data);
            if (false !== $list) {
                //成功提示
                $this->success('密码修改成功!',U("Usercenter/resetpwd"));
            } else {
                //错误提示
                $this->error('密码修改失败!');
            }
        } else {
            $map["id"] = $_SESSION["uid"];
            $userinfo = D("jk_user")->where($map)->find();
            $this->assign("vo", $userinfo);
            $this->display();
        }
    }

}
