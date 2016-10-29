<?php
namespace Admin\Model;
use Think\Model;
class OrderModel extends Model 
{
	//	下单时允许接收的字段
	protected $insertFields = array('shr_name','shr_tel','shr_province','shr_city','shr_area','shr_address');
	//	下单时表单验证规则
	protected $_validate = array(
		array('shr_name', 'require', '收货人名称不能为空！', 1, 'regex', 3),
		array('shr_tel', 'require', '收货人电话不能为空！', 1, 'regex', 3),
		array('shr_province', 'require', '收货人省份不能为空！', 1, 'regex', 3),
		array('shr_city', 'require', '收货人城市不能为空！', 1, 'regex', 3),
		array('shr_area', 'require', '收货人地区不能为空！', 1, 'regex', 3),
		array('shr_address', 'require', '收货人详细地址不能为空！', 1, 'regex', 3)
	);
	
	protected function _before_insert(&$data, &$option)
	{
		$memberId = session('m_id');
		/************	下单前的检查	*************/
		//	是否登录
		if(!$memberId)
		{
			$this->error = '必须先登录！';
			return FALSE;
		}
		//	购物车中是否有商品
		$cartModel = D('Cart');
		$option['goods'] = $goods = $cartModel->cartList();//	获取购物车中的商品，并保存到option变量中，这个option会被传到_after_insert中
		if(!$goods)
		{
			$this->error = '购物车中没有商品,无法下单！';
			return FALSE;
		}


		//	读库存之前加锁,注意:把锁赋给这个模型,这样这个锁可以一直保存到下单结束,否则这个锁在_before_insert函数执行完之后就释放了.
		$this->fp = fopen('./order.lock');
		flock($this->fp, LOCK_EX);


		//	循环购物车中的商品检查库存量并且计算商品总价
		$gnModel = D('goods_number');
		$total_price = 0;//	总价
		foreach ($goods as $k => $v) {
			$gnNumber = $gnModel->field('goods_number')->where(array(
				'goods_id'=>$v['goods_id'];
				'goods_attr_id'=>$v['goods_attr_id']
			))->find();
			if($gnNumber['goods_number']<$v['goods_number'])
			{
				$this->error = '下单失败，原因：商品：<strong>'.$v['goods_name'].'</strong>库存量不足！';
				return FALSE;
			}
			$total_price += $v['goods_number']*$v['price'];//	统计总价
		}

		//	把其他信息补到订单中
		$data['total_price'] = $total_price;
		$data['member_id'] = $memberId;
		$data['addtime'] = time();


		//	开启事务
		//	为了确定三张表的操作都能成功：订单基本信息表，订单商品标，库存量表
		$this->startTrans();

	}

	//	订单基本信息生成之后，$data['id']就是新生成的订单的id
	public function _after_insert(&$data,$option)
	{
		$ogModel = D('order_goods');
		$gnModel = D('goods_number');
		//	把购物车中的商品插入到订单表中,并且减少库存
		foreach ($option['goods'] as $k => $v) {
			$ret = $ogModel->add(array(
				'goods_id'=>$v['goods_id'],
				'goods_attr_id'=>$v['goods_attr_id'],
				'goods_number'=>$v['goods_number'],
				'order_id'=>$v['order_id'],
				'price'=>$v['price'],
			));

			if(!$ret)
			{
				$this->rollback();
				return FALSE;
			}


			//	减库存
			$ret = $gnModel->where(array(
				'goods_id'=>$v['goods_id'],
				'goods_attr_id'=>$v['goods_attr_id'],
			))->setDec('goods_number',$v['goods_number']);
			if(FALSE === $ret)
			{
				$this->rollback();
				return FALSE;
			}

			//	所有操作都成功
			$this->commit();//	提交事务



			//	释放锁
			flock($this->fp, LOCK_UN);
			fclose($this->fp);

		}

	}
	
	/*********************** 其他方法 **********************/
}