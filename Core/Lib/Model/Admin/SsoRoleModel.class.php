<?php 
class SsoRoleModel extends Model {
	// 实际数据表名（包含表前缀）
	protected $trueTableName = 'sso.sso_role';
	// 主键名称
	protected $pk               =   'roleid';
	//自动完成
	protected $_auto = array (
		array('created','time',Model:: MODEL_BOTH,'function'), 	//新增时
	);	
	//自动验证
	protected $_validate=array(
 		array('rname','require','角色名称必须！',1,'',3),
		array('lvl',array(1,100),'角色级别值的范围不正确，应该在1-100区间！',Model::MUST_VALIDATE,'between',Model::MODEL_BOTH), // 当值不为空的时候判断是否在一个范围内			
		array('status','require','角色状态必须！',1,'',3),
	);
}