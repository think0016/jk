<?php

namespace Jk\Controller;

use Think\Controller;

class LoginController extends Controller {
    public function sms()
    {
        $msg="【云监控】您的验证码是123456";
        $result=sendsms("18638652996", $msg);
        print_r($result);
    }
	public function index() {
		$this->display ();
	}
        public function getsmswithfindpwd()
        {
            
            $mobile=I("post.param");
            if(!$mobile)
                $mobile=I("post.mobile");
            if(strlen($mobile)!=11)
                die(json_encode(array("status"=>0,"info"=>"手机号格式不正确")));
            $model=D("jk_user");
            $user=$model->where(array("mobile"))->order("id desc")->find();
            if(!$user)
                die(json_encode(array("status"=>0,"info"=>"手机号不存在")));
            else
            {
                $smsmodel=D("jk_smslog");
                $data["phone"]=$mobile;
                $data["sendtype"]="find";
                $data["ischeck"]="0";
                $data["_string"]=" sendtime>".(time()-C("smsstep"));
                $log=$smsmodel->where($data)->find();
                if($log)
                    die(json_encode(array("status"=>0,"info"=>"请".(time()-$log["sendtime"])."秒后重新发送")));
                $code=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
                unset($data["_string"]);
                    $data["code"]=$code;
                    
                $data["sendtime"]=time();
                $smsmodel->startTrans();
                $insertid=$smsmodel->add($data);
                if($insertid)
                {
                    $msg="【云监控】您的验证码是$code";
                    $result=sendsms($mobile, $msg);
                    if($result["code"]==0)
                    {
                        $smsmodel->commit();
                        $_SESSION["findphone"]=$mobile;
                        die(json_encode(array("status"=>1,"info"=>"验证码已发送")));
                    }else
                    {
                        $smsmodel->rollback();
                        die(json_encode(array("status"=>1,"info"=>"验证码发送失败"))); 
                    }
                }
                else
                {
                    
                    die(json_encode(array("status"=>1,"info"=>"验证码发送失败"))); 
                }
            }
        }
        public function checkmobile()
        {
            $mobile=I("post.param");
            if(!$mobile)
                $mobile=I("post.mobile");
            if(strlen($mobile)!=11)
                die(json_encode(array("status"=>0,"info"=>"手机号格式不正确")));
            $model=D("jk_user");
            $data=$model->where(array("mobile"=>$mobile))->find();
			
            if($data)
                die(json_encode(array("status"=>0,"info"=>"手机号已存在",$data)));
            else
            {
                $smsmodel=D("jk_smslog");
                $data["phone"]=$mobile;
                $data["sendtype"]="reg";
                $data["ischeck"]="0";
                $data["_string"]=" sendtime>".(time()-C("smsstep"));
                $log=$smsmodel->where($data)->order("id desc")->find();
                if($log)
                    die(json_encode(array("status"=>0,"info"=>"请".(time()-$log["sendtime"])."秒后重新发送")));
                $code=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
                unset($data["_string"]);
                    $data["code"]=$code;
                    
                $data["sendtime"]=time();
                $smsmodel->startTrans();
                $insertid=$smsmodel->add($data);
                if($insertid)
                {
                    $msg="【云监控】您的验证码是$code";
                    $result=sendsms($mobile, $msg);
                    if($result["code"]==0)
                    {
                        $smsmodel->commit();
                        $_SESSION["regphone"]=$mobile;
                        die(json_encode(array("status"=>1,"info"=>"验证码已发送")));
                    }else
                    {
                        $smsmodel->rollback();
                        die(json_encode(array("status"=>1,"info"=>"验证码发送失败"))); 
                    }
                }
                else
                {
                    
                    die(json_encode(array("status"=>1,"info"=>"验证码发送失败"))); 
                }
            }
                
        }
	public function resetpwd()
        {
            $vcode=I("post.vcode");
            $mobile=I("post.mobile");
            $password=I("post.password");
            /*if($mobile!=$_SESSION["findphone"])
            {
                $this->error("手机号异常");
            }*/
            $data["phone"]=$mobile;
            $data["code"]=$vcode;
            $smsmodel=D("jk_smslog");
            $result=$smsmodel->where($data)->find();
            $smsmodel->startTrans();
            if($result && $result["ischeck"]==0)
            {
                $userinfo=D("jk_user")->where(array("mobile"=>$mobile))->find();
                if(!$userinfo)
                {
                    $smsmodel->rollback();
                    $this->error("用户信息不存在");
                }
                else
                {
                    $savedata["password"]=md5(($password.C('md5_salt')));
                    D("jk_user")->where(array("mobile"=>$mobile))->save($savedata);
                    $smsmodel->where($result)->save(array("ischeck"=>1));
                    $smsmodel->commit();
                    $this->success("密码重置成功，请重新登录",U('Index/index'));
                }
            }else
            {
                $this->error("验证码错误");
            }
            
        }
	public function login(){
		$username = trim(I("post.username"));
		$password = trim(I("post.password"));
		
		if(!isset($username) || !isset($username)){
			$this->error("非法操作");
		}
		
		$userModel = D('jk_user');
		$map['username'] = $username;
		$map['password'] = md5(($password.C('md5_salt')));
		
		$user=$userModel->where($map)->find();
		if($user){
			if($user['status']==0){
				session("uid",$user['id']);
				session("username",$username);
				$this->success('登录成功', U('Index/index'));
			}else{
				$this->error("该账号已经被冻结");
			}
		}else{
			$this->error("账号或密码不正确");
		}
	}
	
	public function register() {
		$this->display ();
	}
	
	public function register2() {
            $vcode=I("post.vcode");
            $mobile=I("post.mobile");
            $password=I("post.password");
            $data["phone"]=$mobile;
            $data["code"]=$vcode;
            $smsmodel=D("jk_smslog");
            $result=$smsmodel->where($data)->find();
            unset($data);
            if(!$result)
                $this->error("验证码错误");
            $smsmodel->startTrans();
            
		$username = trim(I("post.username"));
		$password = trim(I("post.password"));
                if(trim(I("post.password"))!=trim(I("post.password2")))
                    $this->error("两次密码输入不一致");
                $phone=$_SESSION["regphone"];
		$email = I("post.email");
		
		if(!isset($username) || !isset($username) || !isset($email)){
			$this->error("非法操作");
		}
		
		$userModel = D('jk_user');
		
		//检查唯一性
		$map['username']= $username;
		$num=$userModel->where($map)->count();
		if($num==0){
			$data['username'] = $username;
			$data['password'] = md5(($password.C('md5_salt')));
			$data['email'] = $email;
			$data['mobile'] = $phone;
			
			$r=$userModel->add($data);
			if($r){
                            $smsmodel->where($result)->save(array("ischeck"=>1));
                            $smsmodel->commit();
				session("uid",$r);
				session("username",$username);
				$this->success('注册成功', U('Index/index'));
			}else{
				$this->error("注册失败");
			}
		}else{
                    $smsmodel->rollback();
			$this->error("用户名已存在");
		}
		
	}
	
	public function logout() {
		session(null);
		$this->success("退出登录成功",U('Login/index'));
	}
}