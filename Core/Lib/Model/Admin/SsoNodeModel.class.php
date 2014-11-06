<?php 
class SsoNodeModel extends Model {
	// 实际数据表名（包含表前缀）
	protected $trueTableName = 'sso.sso_node';
	// 主键名称
	protected $pk               =   'nodeid';
	//自动验证
	protected $_validate=array(
 		array('node_name','require','菜单名称必须！',1,'',3),
		array('lvl',array(1,100),'菜单级别值的范围不正确，应该在1-100区间！',Model::MUST_VALIDATE,'between',Model::MODEL_BOTH), // 当值不为空的时候判断是否在一个范围内			
	);
}