<?php

namespace Jk\Controller;

use Think\Controller;

class AlarmshowController extends BaseController {
    
    private function menu()
    {
        $webtype=D("jk_tasktype")->where(array("stype"=>array("neq",4)))->select();
        
        for($i=0;$i<count($webtype);$i++)
        {
            $type[$webtype[$i]["stype"]]["son"][]=$webtype[$i];
            $type[$webtype[$i]["stype"]]["sons"][]=$webtype[$i]["sid"];
            $type[$webtype[$i]["stype"]]["typeid"]=$webtype[$i]["stype"];
        }
        //print_r($type);
        $this->assign("menu",$type);
    }

    function index() {
        $this->menu();
        $map["uid"] = $_SESSION["uid"];
        if(I("get.typeid"))
        $map["sid"] = I("get.typeid");
        $map["status"] = 1;
        $model=D("jk_task");
        $count = $model->where ( $map )->count ( $model->getPk () );
		//echo $model->getlastsql();//exit();
		if ($count > 0) {
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = C('PAGE_LISTROWS');
			}
			//echo $listRows;
			$p = new \Think\Adminpage ( $count, $listRows );
			//分页查询数据
                        $order="id";
                        $sort=" desc ";
			$alarmlist = $model->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select ( );
                        $page = $p->show ();
			//列表排序显示
			$sortImg = $sort; //排序图标
			$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
			$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
			$this->assign ( "page", $page );
                }
                $this->assign ( 'totalCount', $count );
		$this->assign ( 'numPerPage', C('PAGE_LISTROWS') );
		$this->assign ( 'currentPage', !empty($_REQUEST[C('VAR_PAGE')])?$_REQUEST[C('VAR_PAGE')]:1);
        $this->assign("list", $alarmlist);
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
