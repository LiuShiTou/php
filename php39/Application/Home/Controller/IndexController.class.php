<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends NavController {
	/************ 首页  *************/
    public function index(){
    	//	获取促销商品信息
    	$goodsModel = D('Admin/Goods');
    	$goods1 = $goodsModel->getPromoteGoods();
    	$goods2 = $goodsModel->getRecGoods('is_hot');
    	$goods3 = $goodsModel->getRecGoods('is_new');
    	$goods4 = $goodsModel->getRecGoods('is_best');
    	$this->assign(array(
    		'goods1'=>$goods1,
    		'goods2'=>$goods2,
    		'goods3'=>$goods3,
    		'goods4'=>$goods4,
    	));
    	
    	$catModel = D('Admin/Category');
    	$floorData = $catModel->floorData();
    	$this->assign(array(
    		'floorData'=>$floorData,
    	));
    	
    	$this->assign(array(
    		'_page_title'=>'首页',
    		'_page_keywords'=>'首页',
    		'_page_description'=>'首页',
    	));
    	$this->display();
    }
    /************ 商品详情页  *************/
    public function goods()
    {
    	//	接收这些商品的ID
    	$id = I('get.id');
    	//	根据商品ID取出商品的详细信息
    	$gModel = D('Admin/Goods');
    	$info = $gModel->find($id);
    	//	再根据主分类ID找出这个分类所有上级分类制作导航
    	$catModel = D('Admin/Category');
    	$catPath = $catModel->parentPath($info['cat_id']);
    	
    	//	取出商品的相册
    	$gpModel = D('goods_pic');
    	$gpData = $gpModel->where(array(
    		'goods_id'=>array('eq',$id),
    	))->select();
    	//	取出这件商品所有的属性
    	$gaModel = D('goods_attr');
    	$gaData = $gaModel->alias('a')
    	->field('a.*,b.attr_name,b.attr_type')
    	->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr_id=b.id')
    	->where(array(
    		'goods_id'=>array('eq',$id),
    	))->select();
    	
    	//	整理所有的商品属性，把可选、唯一的属性分开放
    	$uniAttr = array();//	唯一
    	$mulAttr =array();//	可选
    	foreach ($gaData as $k=>$v)
    	{
    		if($v['attr_type'] == '唯一')
    		{
    			$uniAttr[] = $v;
    		}
    		else
    		{
    			//	把同一个属性放到一起，二维数组转三维数组
    			$mulAttr[$v['attr_name']][] = $v;
    		}
    	}
    	
    	$viewPath = C('IMAGE_CONFIG');
    	
    	//	取出这件商品的所有会员价格
    	$mpModel = D('member_price');
    	$mpData = $mpModel->alias('a')
    	->field('a.price,b.level_name')
    	->join('LEFT JOIN __MEMBER_LEVEL__ b ON a.level_id=b.id')
    	->where(array(
    			'goods_id'=>array('eq',$id),
    	))->select();
    	
    	$this->assign(array(
    			'info'=>$info,
    			'catPath'=>$catPath,
    			'viewPath'=>$viewPath['viewPath'],
    			'uniAttr'=>$uniAttr,
    			'mulAttr'=>$mulAttr,
    			'gaData'=>$gaData,
    			'gpData'=>$gpData,
    			'mpData'=>$mpData,
    	));
    	
    	$this->assign(array(
    		'_page_title'=>'商品详情页',
    		'_page_keywords'=>'商品详情页',
    		'_page_description'=>'商品详情页',
    		'_show_nav'=>1,
    	));
    	$this->display();
    }
    /*************** 处理浏览历史	****************/
    public function displayHistory()
    {
    	///
    	$id = I('get.id');
    	///		先从COOKIE中取出浏览历史的ID数组
    	$data = isset($_COOKIE['display_history'])?unserialize($_COOKIE['display_history']):array();
    	///		把最新浏览的这件商品放到数组中的第1个位置
    	array_unshift($data,$id);
    	///		去重
    	$data = array_unique($data);
    	///		只取数组前6个
    	if(count($data) > 6)
    		array_slice($data,0,6);
    	///		存入COOKIE
    	setcookie('display_history',serialize($data),time()+30*86400,'/');
    	///		再根据商品的ID取出商品的详细信息
    	$goodsModel = D('Admin/Goods');
    	$data=implode(',',$data);
    	$gData = $goodsModel->field('id,mid_logo,goods_name')
    	->where(array(
    		'id'=>array('in',$data),
    		'is_on_sale'=>array('eq','是'),
    	))->order("FIELD(id,$data)")
    	->select();
    	echo json_encode($gData);
    }
    public function ajaxGetMemberPrice()
    {
    	$goodsId = I('get.goods_id');
    	$gModel = D('Admin/Goods');
    	echo $gModel->getMemberPrice($goodsId);
    }
}















