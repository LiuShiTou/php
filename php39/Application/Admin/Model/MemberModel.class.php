<?php
namespace Admin\Model;
use Think\Model;
class MemberModel extends Model 
{
	protected $insertFields = array('username','password','cpassword','chkcode','must_click');
	protected $updateFields = array('id','username','password','cpassword');
	/************ 添加和修改管理员所使用的标单验证规则  *************/
	protected $_validate = array(
		array('must_click', 'require', '必须要同意注册协议！', 1, 'regex', 3),
		array('username', 'require', '用户名不能为空！', 1, 'regex', 3),
		array('username', '', '用户名已经存在！', 1, 'unique', 3),
		array('username', '1,30', '用户名的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', '6,20', '用户名的值最长不能超过6-20 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
		array('cpassword', 'password', '两次密码输入不一致！', 1, 'confirm', 3),
		array('chkcode','require','验证码不能为空！',1),
		array('chkcode','check_verify','验证码错误！',1,'callback'),
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
	/****************	登录		****************/
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
				session('m_id',$user['id']);
				session('m_username',$user['username']);
				
				//计算当前会员级别ID,并存session
				$mlModel = D('member_level');
				$levelId = $mlModel->field('id')->where(array(
					'jifen_bottom'=>array('elt',$user['jifen']),
					'jifen_top'=>array('egt',$user['jifen']),
				))->find();
				session('level_id',$levelId['id']);
				
				//	将COOKIE中的数据移到数据库中
				$cartModel = D('Home/Cart');
				$cartModel->moveDataToDb();
				
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
	
	public function _before_insert(&$data, $options)
	{
		$data['password'] = md5($data['password']);
	}
	
	/****************	注销		****************/
	public function logout()
	{
		session(null);
	}
	
	/************************************ 其他方法 ********************************************/
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}