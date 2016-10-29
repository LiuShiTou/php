<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends Controller {
	/************ 验证登录状态  *************/
	public function ajaxChkLogin()
	{
		if(session('m_id'))
		{
			echo json_encode(array(
				'login'=>1,
				'username'=>session('m_username'),
			));
		}
		else 
			echo json_encode(array(
					'login'=>0,
			));
	}
	/************ 验证码  *************/
	public function chkcode()
	{
		$Verify = new \Think\Verify(array(
				'fontSize'=>30,
				'length'=>2,
				'useNoise'=>true,
		));
		$Verify->entry();
	}
	/************ 登录  *************/
    public function login(){
    	if(IS_POST)
    	{
    		$model = D('Admin/Member');
    		if($model->validate($model->_login_validate)->create())
    		{
    			if($model->login())
    			{
                    $returnUrl = U('/');//  ，默认地址
                    $su = session('returnUrl');
                    if($su)
                    {
                        session('returnUrl',null);
                        $returnUrl = $su;
                    }
    				$this->success('登录成功!',$returnUrl);
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	
    	$this->assign(array(
    		'_page_title'=>'登录',
    		'_page_keywords'=>'登录',
    		'_page_description'=>'登录',
    	));
    	$this->display();
    }
    /************ 前台会员注册  *************/
    public function regist()
    {
    	if(IS_POST)
    	{
    		$model = D('Admin/Member');
    		if($model->create(I('post.'),1))
    		{
    			if($model->add())
    			{
    				$this->success('注册成功!',U('login'));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	
    	$this->assign(array(
    			'_page_title'=>'注册',
    			'_page_keywords'=>'注册',
    			'_page_description'=>'注册',
    	));
    	$this->display();
    }
    /************ 前台会员退出  *************/
    public function logout()
    {
    	$model = D('Admin/Member');
    	$model->logout();
    	redirect('/');
    }
}















