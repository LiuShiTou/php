<?php
namespace Admin\Model;
use Think\Model;
class CategoryModel extends Model 
{
	protected $insertFields = array('cat_name','parent_id','is_floor');
	protected $updateFields = array('id','cat_name','parent_id','is_floor');
	protected $_validate = array(
		array('cat_name', 'require', '分类名称不能为空！', 1, 'regex', 3),
	);
	
	// 找一个分类所有子分类的ID
	public function getChildren($catId)
	{
		// 取出所有的分类
		$data = $this->select();
		// 递归从所有的分类中挑出子分类的ID
		return $this->_getChildren($data, $catId, TRUE);
	}
	/**
	 * 递归从数据中找子分类
	 */
	private function _getChildren($data, $catId, $isClear = FALSE)
	{
		static $_ret = array();  // 保存找到的子分类的ID
		if($isClear)
			$_ret = array();
		// 循环所有的分类找子分类
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $catId)
			{
				$_ret[] = $v['id'];
				// 再找这个$v的子分类
				$this->_getChildren($data, $v['id']);
			}
		}
		return $_ret;
	}
	// 获取树形数据
	public function getTree()
	{
		$data = $this->select();
		return $this->_getTree($data);
	}
	private function _getTree($data, $parent_id=0, $level=0)
	{
		static $_ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$v['level'] = $level;  // 用来标记这个分类是第几级的
				$_ret[] = $v;
				// 找子分类
				$this->_getTree($data, $v['id'], $level+1);
			}
		}
		return $_ret;
	}
	
	protected function _before_delete(&$option)
	{
		/************** 修改原$option，把所有子分类的ID也加进来，这样TP会一起删除掉 *******/
		// 先找出所有子分类的ID
		$children = $this->getChildren($option['where']['id']);
		$children[] = $option['where']['id'];
		$option['where']['id'] = array(
			0 => 'IN',
			1 => implode(',', $children),
		);
	}
	/***********获取导航条上的数据***********/
	public function getNavData()
	{
		//	先从缓存中取数据
		$catData = S('catData');
		if(!$catData)
		{
			// 取出所有的分类
			$all = $this->select();
			$ret=array();
			// 循环所有的分类找出顶级分类
			foreach ($all as $k=>$v)
			{
				if($v['parent_id'] == 0)
				{
					// 循环所有的分类找出这个顶级分类的子分类
					foreach ($all as $k1=>$v1)
					{
						if($v1['parent_id'] == $v['id'])
						{
							// 循环所有的分类找出这个二级分类的子分类
							foreach ($all as $k2=>$v2)
							{
								if($v2['parent_id'] == $v1['id'])
								{
									$v1['children'][] = $v2;
								}
							}
							$v['children'][] = $v1;
						}
					}
					$ret[] = $v;
				}
			}
			// 循环所有的分类找出这个二级分类的子分类
			S('catData',$ret,86400);
			return $ret;
		}
		else 
			return $catData;// 循环所有的分类找出这个二级分类的子分类
	}
	/*********取出前台首页楼层推荐信息********/
	public function floorData()
	{
		$floorData = S('floorData');
		if($floorData)
			return $floorData;
		else 
		{
			$goodsModel = D('Admin/Goods');
			//	先取出推荐到楼层的顶级分类信息
			$ret = $this->where(array(
				'parent_id'=>array('eq',0),
				'is_floor'=>array('eq','是'),
			))->select();
			//	循环每个楼层，取出楼层中的数据
			foreach ($ret as $k=>$v)
			{
				/******* 这个楼层中的品牌数据  ********/
				///	先取出这个楼层下所有的商品ID
				$goodsId = $goodsModel->getGoodsIdByCatId($v['id']);
				///	再取出这些商品所用到的品牌
				$ret[$k]['brand'] = $goodsModel->alias('a')
				->join('LEFT JOIN __BRAND__ b ON a.brand_id=b.id')
				->field('DISTINCT brand_id,b.brand_name,b.logo')
				->where(array(
					'a.id'=>array('in',$goodsId),
					'a.brand_id'=>array('neq',0),
				))->limit(9)->select();
				/******* 未推荐的二级分类  ********/
				$ret[$k]['subCat'] = $this->where(array(
					'parent_id'=>array('eq',$v['id']),
					'is_floor'=>array('eq','否'),
				))->select();
				/******* 推荐的二级分类  ********/
				$ret[$k]['recSubCat'] = $this->where(array(
					'parent_id'=>array('eq',$v['id']),
					'is_floor'=>array('eq','是'),
				))->select();
				/*********循环每个推荐的二级分类，取出分类下的8件被推荐到楼层的商品*********/
				foreach ($ret[$k]['recSubCat'] as $k1=>&$v1)
				{
					//	取出这个分类下所有商品的ID并返回一维数组
					$gids = $goodsModel->getGoodsIdByCatId($v1['id']);
					//	再根据商品ID取出商品的详细信息
					$v1['goods'] = $goodsModel->field('id,goods_name,mid_logo,shop_price')->where(array(
						'is_on_sale'=>array('eq','是'),
						'is_floor'=>array('eq','是'),
						'id'=>array('in',$gids),
					))->order('sort_num ASC')->limit(8)->select();
				}
			}
			S('floorData',$ret,3600);
			return $ret;
		}
	}
	/****************通过分类ID获取父级分类ID****************/
	public function parentPath($catId)
	{
		$ret = array();
		$info = $this->field('id,cat_name,parent_id')->find($catId);
		$ret[] = $info;
		if($info['parent_id'] > 0)
			$this->parentPath($info['parent_id']);
		return $ret;
	}
}














