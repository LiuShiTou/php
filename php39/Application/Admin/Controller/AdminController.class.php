<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends BaseController 
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Admin');
    		if($model->create(I('post.'), 1))
    		{
    			if($id = $model->add())
    			{
    				$this->success('添加成功！', U('lst?p='.I('get.p')));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}

    	///
    	$roleModel = D('Role');
    	$roleData = $roleModel->select();
    	
		// 设置页面中的信息
		$this->assign(array(
			'roleData'=>$roleData,
			'_page_title' => '添加后台用户表',
			'_page_btn_name' => '后台用户表列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function edit()
    {
    	$id = I('get.id');
    	if(IS_POST)
    	{
    		$model = D('Admin');
    		if($model->create(I('post.'), 2))
    		{
    			if($model->save() !== FALSE)
    			{
    				$this->success('修改成功！', U('lst', array('p' => I('get.p', 1))));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	$model = M('Admin');
    	$data = $model->find($id);
    	$this->assign('data', $data);

    	///
    	$roleModel = D('Role');
    	$roleData = $roleModel->select();
    	
    	//	先取出已经拥有了的角色
    	$arModel = D('admin_role');
    	$roleId = $arModel->field('GROUP_CONCAT(role_id) role_id')->where(array(
    		'admin_id'=>array('eq',$id),
    	))->find();
    	
		// 设置页面中的信息
		$this->assign(array(
			'roleId'=>$roleId['role_id'],
			'roleData'=>$roleData,
			'_page_title' => '修改后台用户表',
			'_page_btn_name' => '后台用户表列表',
			'_page_btn_link' => U('lst'),
		));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Admin');
    	if($model->delete(I('get.id', 0)) !== FALSE)
    	{
    		$this->success('删除成功！', U('lst', array('p' => I('get.p', 1))));
    		exit;
    	}
    	else 
    	{
    		$this->error($model->getError());
    	}
    }
    public function lst()
    {
    	$model = D('Admin');
    	$data = $model->search();
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '后台用户表列表',
			'_page_btn_name' => '添加后台用户表',
			'_page_btn_link' => U('add'),
		));
    	$this->display();
    }
}