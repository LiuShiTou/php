<?php
namespace Home\Model;
use Think\Model;
class CartModel extends Model
{
	//	加入购物车时允许接收的字段
	protected $insertFields = array('goods_id','goods_attr_id','goods_number');
	//	加入购物车时的标单验证规则
	protected $_validate = array(
		array('goods_id','require','必须选择商品!',1),
		array('goods_number','chkGoodsNumber','该商品库存量不足!',1,'callback'),
	);
	//	检查库存量
	public function chkGoodsNumber($goodsNumber)
	{
		//	选择商品属性ID
		$gaid = I('post.goods_attr_id');
		sort($gaid,SORT_NUMERIC);//	按数字升序排列
		$gaid = (string)implode(',',$gaid);
		
		$gnModel = D('goods_number');
		$gn = $gnModel->field('goods_number')->where(array(
			'goods_id'=>I('post.goods_id'),
			'goods_attr_id'=>$gaid,
		))->find();
		
		//	返回库存是否够量
		return ($gn['goods_number'] >= $goodsNumber);
	}
	//	重写父类add方法：判断，如果没有登录就存COOKIE，登录了就存到数据库
	public function add()
	{
		$memberId = I('m_id');
		sort($this->goods_attr_id,SORT_NUMERIC);
		$this->goods_attr_id = implode(',',$this->goods_attr_id);
		if($memberId)
		{
			$goodsNumber = $this->goods_number;//	先把表单中的数据存到变量中,否则find之后表单中的数据就被数据库中的数据覆盖掉了，就没有goods_number了
			$has = $this->field('id')->where(array(
				'goods_id'=>$this->goods_id,
				'goods_attr_id'=>$this->goods_attr_id,
				'member_id'=>$memberId,
			))->find();
			//	如果购物车中已经有该商品，则在原数量上增加即可
			if($has)
				$this->where(array(
					'id'=>array('eq',$has),
				))-setInc('goods_number',$goodsNumber);
			else
				parent::add(array(
					'goods_id'=>$this->goods_id,
					'goods_attr_id'=>$this->goods_attr_id,
					'goods_number'=>$goodsNumber,
					'member_id'=>$memberId,
				));
		}
		else 
		{
			//	从COOLIE中取出购物车的一维数组
			$cart = isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):array();
			//	拼接下标
			$key = $this->goods_id . '-' . $this->goods_attr_id;
			//	如果COOLIE中已经存在该商品，则在原数量上增加即可
			if(isset($cart[$key]))
				$cart[$key] += $this->goods_number;
			else 
				$cart[$key] = $this->goods_number;
			//	把商品加入到COOLIE中
			setcookie('cart',serialize($cart),time()+30*86400,'/');
		}
		return true;
	}
	/**
	 * 	把COOKIE中的数据移到数据库中
	 */
	public function moveDataToDb()
	{
		$memberId = session('m_id');
		if($memberId)
		{
			$cart = isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):array();
			//	循环购物车中的每件商品
			foreach ($cart as $k=>$v)
			{
				$_k = explode('-',$cart);
				$has = $this->field('id')->where(array(
					'goods_id'=>$_k[0],
					'goods_attr_id'=>$_k[1],
					'member_id'=>$memberId,
				))->find();
				//	如果购物车中已经有该商品，则在原数量上增加即可
				if($has)
					$this->where(array(
							'id'=>array('eq',$has),
					))-setInc('goods_number',$v);
				else
					parent::add(array(
						'goods_id'=>$_k[0],
						'goods_attr_id'=>$_k[1],
						'goods_number'=>$v,
						'member_id'=>$memberId,
					));
			}
			//	清空COOKIE
			setcookie('cart','',time()-1,'/');
		}
	}
	/**
	 * 	获取购物车中商品的详细信息
	 */
	public function cartList()
	{
		///		先从购物车中取出商品ID
		$memberId = session('m_id');
		if($memberId)
		{
			//	从数据库中取
			$data = $this->where(array(
				'member_id'=>array('eq',$memberId),
			))->select();
		}
		else 
		{
			$_data = isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):array();
			//	把一维转成和上面一样的二维
			$data = array();
			foreach ($_data as $k=>$v) {
				//	从下标中取出商品ID和商品属性ID
				$_k = explode('-',$k);
				$data[] = array(
					'goods_id'=>$_k[0],
					'goods_attr_id'=>$_k[1],
					'goods_number'=>$v,
				);
			}
		}
		///		再根据商品ID取出商品的详细信息
		$gModel = D('Admin/Goods');
		$gaModel = D('Admin/goods_attr');
		//	循环取出每件商品的详细信息
		foreach ($data as $k => &$v) {
			//	取出商品的logo和名称
			$info = $gModel->field('goods_name,mid_logo')->find($v['goods_id']);
			//	再存回到这个二维数组
			$v['goods_name'] = $info['goods_name'];//	$v[$k]['goods_name'] = $info['goods_name'];
			$v['mid_logo'] = $info['mid_logo'];
			//	计算实际的购买价格
			$v['price'] = $gModel->getMemberPrice($v['goods_id']);
			//	根据商品属性ID计算出商品属性名称和属性值,属性名称:属性值
			if($v['goods_attr_id'])
			{
				$gaData = $gaModel->alias('a')
				->field('a.attr_value,b.attr_name')
				->join('__ATTRIBUTE__ b ON a.attr_id=b.id')
				->where(array(
						'a.id'=>array('in',$v['goods_attr_id']),
				))->select();
			}
			
		}
		return $data;
		
	}
	//	清空购物车
	public function clear()
	{
		$this->where(array(
			'member_id'=>array('eq',session('m_id')),
		))->delete();
	}
	
	
}











