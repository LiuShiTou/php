<?php
namespace Home\Controller;
use Think\Controller;
class CartController extends Controller
{
	
	public function add()
	{
		if(IS_POST)
		{
			$cartModel = D('Cart');
			if($cartModel->create(I('post.'),1))
			{
				if($cartModel->add())
				{
					$this->success('成功添加到购物车!',U('lst'));
					exit;
				}
			}
			$this->error('添加到购物车失败!原因：' . $cartModel->getError());
		}
	}
	/**
	 * 购物车列表
	 */
	public function lst()
	{
		$cartModel = D('Cart');
		$data = $cartModel->cartList();
		// 设置页面中的信息
		$this->assign(array(
				'data'=>$data,
				'_page_title' => '添加后台用户表',
				'_page_btn_name' => '后台用户表列表',
				'_page_btn_link' => U('lst'),
		));
		$this->display();
	}
	
	public function ajaxCartList()
	{
		$cartModel = D('Cart');
		$data = $cartModel->cartList();
		echo json_encode($data);
	}
	
}













