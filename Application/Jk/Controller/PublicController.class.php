<?php

namespace Jk\Controller;

use Think\Controller;

class PublicController extends Controller {
    function alarmlist()
    {
        $model=D("Alarmusers");
        $groupid=I("get.id");
        if($groupid<0)
            echo json_encode (array());
        $map["group_id"]=$groupid;
        $list=$model->field("name")->where($map)->select();
            echo json_encode($list);
    }
}

