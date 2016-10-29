<?php
namespace Home\Controller;
class SearchController extends NavController
{
	//	分类搜索
	public function cat_search()
	{


		//	设置页面信息
		$this->assign(array(
    		'_page_title'=>'分类搜索页',
    		'_page_keywords'=>'分类搜索页',
    		'_page_description'=>'分类搜索页',
    	));
    	$this->display();
	}

}

?>
