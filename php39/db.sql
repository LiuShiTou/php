create database php39;
use php39;
set names utf8;

drop table if exists p39_goods;
create table p39_goods
(
	id mediumint unsigned not null auto_increment comment 'Id',
	goods_name varchar(150) not null comment '商品名称',
	market_price decimal(10,2) not null comment '市场价格',
	shop_price decimal(10,2) not null comment '本店价格',
	goods_desc longtext comment '商品描述',
	is_on_sale enum('是','否') not null default '是' comment '是否上架',
	is_delete enum('是','否') not null default '否' comment '是否放到回收站',
	addtime datetime not null comment '添加时间',
	logo varchar(150) not null default '' comment '原图',
	sm_logo varchar(150) not null default '' comment '小图',
	mid_logo varchar(150) not null default '' comment '中图',
	big_logo varchar(150) not null default '' comment '大图',
	mbig_logo varchar(150) not null default '' comment '更大图',
	brand_id mediumint unsigned not null default '0' comment '品牌id',
	cat_id mediumint unsigned not null default '0' comment '主分类Id',
    type_id mediumint unsigned not null default '0' comment '类型ID',
    
    promote_price decimal(10,2) not null default '0.00' comment '促销价格',
    promote_start_date datetime not null comment '促销开始时间', 
    promote_end_date datetime not null comment '促销结束时间',
    is_new enum('是','否') not null default '否' comment '是否新品',
    is_hot enum('是','否') not null default '否' comment '是否热卖',
    is_best enum('是','否') not null default '否' comment '是否精品',
    
    is_floor enum('是','否') not null default '否' comment '是否推荐楼层',
    sort_num tinyint unsigned not null default '100' comment '排序的数字',
    
	primary key (id),
	
	key promote_price(promote_price),
	key promote_start_date(promote_start_date),
	key promote_end_date(promote_end_date),
	key is_new(is_new),
	key is_hot(is_hot),
	key is_best(is_best),
	
	key sort_num(sort_num),
	
	key shop_price(shop_price),
	key addtime(addtime),
	key brand_id(brand_id),
	key cat_id(cat_id),
	key is_on_sale(is_on_sale)
)engine=InnoDB default charset=utf8 comment '商品';

drop table if exists p39_brand;
create table p39_brand
(
	id mediumint unsigned not null auto_increment comment 'Id',
	brand_name varchar(30) not null comment '品牌名称',
	site_url varchar(150) not null default '' comment '官方网址',
	logo varchar(150) not null default '' comment '品牌Logo图片',
	primary key (id)
)engine=InnoDB default charset=utf8 comment '品牌';

drop table if exists p39_member_level;
create table p39_member_level
(
	id mediumint unsigned not null auto_increment comment 'Id',
	level_name varchar(30) not null comment '级别名称',
	jifen_bottom mediumint unsigned not null comment '积分下限',
	jifen_top mediumint unsigned not null comment '积分上限',
	primary key (id)
)engine=InnoDB default charset=utf8 comment '会员级别';

drop table if exists p39_member_price;
create table p39_member_price
(
	price decimal(10,2) not null comment '会员价格',
	level_id mediumint unsigned not null comment '级别Id',
	goods_id mediumint unsigned not null comment '商品Id',
	key level_id(level_id),
	key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '会员价格';

drop table if exists p39_goods_pic;
create table p39_goods_pic
(
	id mediumint unsigned not null auto_increment comment 'Id',
	pic varchar(150) not null comment '原图',
	sm_pic varchar(150) not null comment '小图',
	mid_pic varchar(150) not null comment '中图',
	big_pic varchar(150) not null comment '大图',
	goods_id mediumint unsigned not null comment '商品Id',
	primary key (id),
	key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '商品相册';

drop table if exists p39_category;
create table p39_category
(
	id mediumint unsigned not null auto_increment comment 'Id',
	cat_name varchar(30) not null comment '分类名称',
	parent_id mediumint unsigned not null default '0' comment '上级分类的Id,0:顶级分类',
	is_floor enum('是','否') not null default '否' comment '是否推荐楼层',
	primary key (id)
)engine=InnoDB default charset=utf8 comment '分类';

drop table if exists p39_goods_cat;
create table p39_goods_cat
(
	cat_id mediumint unsigned not null comment '分类id',
	goods_id mediumint unsigned not null comment '商品Id',
	key goods_id(goods_id),
	key cat_id(cat_id)
)engine=InnoDB default charset=utf8 comment '商品扩展分类';
/******************* 属性相关 ******************/
drop table if exists p39_type;
create table if not exists p39_type(
    id mediumint unsigned not null auto_increment comment 'ID',
    type_name varchar(30) not null comment '类型名称',
    primary key (id)
)engine=InnoDB default charset=utf8 comment '属性类型';

drop table if exists p39_attribute;
create table if not exists p39_attribute(
    id mediumint unsigned not null auto_increment comment 'ID',
    attr_name varchar(30) not null comment '属性名称',
    attr_type enum('唯一','可选') not null comment '属性类型',
    attr_option_values varchar(300) not null default '' comment '属性可选值',
    type_id mediumint unsigned not null comment '所属类型id',
    primary key (id),
    key type_id(type_id)
)engine=InnoDB default charset=utf8 comment '属性表';

drop table if exists p39_goods_attr;
create table if not exists p39_goods_attr(
    id mediumint unsigned not null auto_increment comment 'ID',
    attr_value varchar(150) not null default '' comment '属性值',
    attr_id mediumint unsigned not null comment '属性ID',
    goods_id mediumint unsigned not null comment '商品ID',
    primary key (id),
    key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '商品属性表';

drop table if exists p39_goods_number;
create table if not exists p39_goods_number(
    goods_id mediumint unsigned not null comment '商品ID',
    goods_number mediumint unsigned not null default '0' comment '库存量',
    goods_attr_id varchar(150) not null comment '商品属性表的ID，如果有多个，就用程序拼成字符串存到这个字段中',
    key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '商品库存表';

///////////RBAC
drop table if exists p39_privilege;
create table if not exists p39_privilege(
	id mediumint unsigned not null auto_increment comment 'ID',
	pri_name varchar(150) not null comment '权限名称',
	action_name varchar(30) not null default '' comment '方法名称',
	controller_name varchar(30) not null default '' comment '控制器名称',
	module_name varchar(30) not null default '' comment '模块名称',
	parent_id mediumint unsigned not null default 0 comment '上级权限ID',
	primary key(id)
)engine=InnoDB default charset=utf8 comment '权限表';

drop table if exists p39_role_pri;
create table if not exists p39_role_pri(
	pri_id mediumint unsigned not null comment '权限ID',
	role_id mediumint unsigned not null comment '角色ID',
	key pri_id(pri_id),
	key role_id(role_id)
)engine=InnoDB default charset=utf8 comment '角色权限表';

drop table if exists p39_role;
create table if not exists p39_role(
	id mediumint unsigned not null auto_increment comment 'ID',
	role_name varchar(30) not null comment '角色名称',
	primary key(id)
)engine=InnoDB default charset=utf8 comment '角色表';

drop table if exists p39_admin_role;
create table if not exists p39_admin_role(
	admin_id mediumint unsigned not null comment '管理员ID',
	role_id mediumint unsigned not null comment '角色ID',
	key admin_id(admin_id),
	key role_id(role_id)
)engine=InnoDB default charset=utf8 comment '角色管理员表';

drop table if exists p39_admin;
create table if not exists p39_admin(
	id mediumint unsigned not null auto_increment comment 'ID',
	username varchar(30) not null comment '用户名',
	password varchar(32) not null comment '密码',
	primary key(id)
)engine=InnoDB default charset=utf8 comment '后台用户表';
INSERT INTO p39_admin(id,username,password) values(1,'root','63a9f0ea7bb98050796b649e85481845');

drop table if exists p39_member;
create table if not exists p39_member(
	id mediumint unsigned not null auto_increment comment 'ID',
	username varchar(30) not null comment '用户名',
	password varchar(32) not null comment '密码',
	face varchar(150) not null default '' comment '图像',
	jifen mediumint unsigned not null default '0' comment '积分',
	primary key(id)
)engine=InnoDB default charset=utf8 comment '后台用户表';

drop table if exists p39_cart;
create table if not exists p39_cart(
	id mediumint unsigned not null auto_increment comment 'ID',
	goods_id mediumint unsigned not null comment '商品ID',
	goods_attr_id varchar(150) not null default '' comment '商品属性ID',
	goods_number mediumint unsigned not null comment '购买的数量',
	member_id mediumint unsigned not null comment '会员ID',
	primary key (id),
	key member_id(member_id)
)engine=InnoDB default charset=utf8 comment '购物车表';

drop table if exists p39_order;
create table if not exists p39_order(
	id mediumint unsigned not null auto_increment comment 'ID',
	member_id mediumint unsigned not null comment '会员ID',
	addtime int unsigned not null comment '下单时间',
	pay_status enum('是','否') not null default '否' comment '支付状态',
	pay_time int not null default '0' comment '支付时间',
	total_price decimal(10,2) not null comment '订单总价',
	shr_name varchar(30) not null comment '收货人姓名',
	shr_tel varchar(30) not null comment '收货人电话',
	shr_province varchar(30) not null comment '收货人省',
	shr_city varchar(30) not null comment '收货人城市',
	shr_area varchar(30) not null comment '收货人地区',
	shr_address varchar(30) not null comment '收货人详细地址',
	post_status tinyiny unsigned not null default '0' comment '发货状态，0：未发货，1：已发货，2：已收货',
	post_number varchar(30) not null default '0' comment '快递号',
	primary key (id),
	key member_id(member_id),
	key addtime(addtime)
)engine=InnoDB default charset=utf8 comment '订单基本信息';

drop table if exists p39_order_goods;
create table if not exists p39_order_goods(
	id mediumint unsigned not null auto_increment comment 'ID',
	goods_id mediumint unsigned not null comment '商品ID',
	order_id mediumint unsigned not null comment '订单ID',
	goods_number mediumint unsigned not null comment '购买的数量',
	goods_attr_id varchar(150) not null default '' comment '商品属性ID',
	price decimal(10,2) not null comment '购买的价格',
	primary key (id),
	key order_id(order_id),
	key goods_id(goods_id)
)engine=InnoDB default charset=utf8 comment '订单商品表';














