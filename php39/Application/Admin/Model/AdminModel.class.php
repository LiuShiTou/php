<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model 
{
	protected $insertFields = array('username','password','cpassword','chkcode');
	protected $updateFields = array('id','username','password','cpassword');
	/************ 添加和修改管理员所使用的标单验证规则  *************/
	protected $_validate = array(
		array('username', 'require', '用户名不能为空！', 1, 'regex', 3),
		array('username', '', '用户名已经存在！', 1, 'unique', 3),
		array('username', '1,30', '用户名的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
		array('cpassword', 'password', '两次密码输入不一致！', 1, 'confirm', 3),
	);
	/************ 登录所使用的标单验证规则  *************/
	public $_login_validate = array(
		array('username','require','用户名不能为空！',1),
		array('password','require','密码不能为空！',1),
		array('chkcode','require','验证码不能为空！',1),
		array('chkcode','check_verify','验证码错误！',1,'callback'),
	);
	/************ 验证验证码是否正确  *************/
	public function check_verify($code,$id='')
	{
		$verify = new \Think\Verify();
		return $verify->check($code,$id);
	}
	public function login()
	{
		//从模型中获取用户名和密码
		$username = $this->username;
		$password = $this->password;
		//	先查这个用户是否存在
		$user = $this->where(array(
			'username'=>array('eq',$username),
		))->find();
		if($user)
		{
			//验证密码
			if($user['password'] == md5($password))
			{
				//登录成功，保存信息到session
				session('id',$user['id']);
				session('username',$user['username']);
				return TRUE;
			}
			else {
				$this->error = '密码不正确!';
				return FALSE;
			}
		}
		else {
			$this->error = '用户名不存在!';
		}
	}
	public function logout()
	{
		session(null);
	}
	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		if($username = I('get.username'))
			$where['username'] = array('like', "%$username%");
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('a')
		->field('a.*,GROUP_CONCAT(c.role_name) role_name')
		->join('LEFT JOIN __ADMIN_ROLE__ b ON a.id=b.admin_id 
				LEFT JOIN __ROLE__ c ON b.role_id=c.id')
		->where($where)
		->group('a.id')
		->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	protected function _after_insert(&$data, $options)
	{
		$roleId = I('post.role_id');
		$arModel = D('admin_role');
		foreach ($roleId as $v)
		{
			$arModel->add(array(
				'role_id'=>$v,
				'admin_id'=>$data['id'],
			));
		}
	}
	// 添加前
	protected function _before_insert(&$data, $option)
	{
		$data['password'] = md5($data['password']);
	}
	// 修改前
	protected function _before_update(&$data, $option)
	{
		$roleId = I('post.role_id');
		$arModel = D('admin_role');
		$arModel->where(array(
			'admin_id'=>$option['where']['id'],
		))->delete();
		foreach ($roleId as $v)
		{
			$arModel->add(array(
					'role_id'=>$v,
					'admin_id'=>$option['where']['id'],
			));
		}
		
		/******* 如果有密码就md5加密，没有就不做修改 *******/
		if($data['password'])
			$data['password'] = md5($data['password']);
		else 
			unset($data['password']);
	}
	// 删除前
	protected function _before_delete($option)
	{
		if($option['where']['id'] == 1)
		{
			$this->error = '超级管理员无法删除！';
			return FALSE;
		}
		
		$arModel =D('admin_role');
		$arModel->where(array(
				'admin_id'=>array('eq',$option['where']['id']),
		))->delete();
		
		if(is_array($option['where']['id']))
		{
			$this->error = '不支持批量删除';
			return FALSE;
		}
	}
	/************************************ 其他方法 ********************************************/
}