<?php
class SsoUserModel extends Model {
	// 实际数据表名（包含表前缀）
	protected $trueTableName = 'sso.sso_user';	
	// 主键名称
	protected $pk               =   'userid';
	//自动完成
	protected $_auto = array (
			array('login_pwd','md5',1,'function'),	//新增时
			array('created','time',Model:: MODEL_BOTH,'function'), 	//新增时
	);
	//自动验证
	protected $_validate=array(
			array('login_name','require','用户名称必须！',1,'',3),
			array('login_name','','用户名称已经存在！',1,'unique',3), // 新增修改时候验证username字段是否唯一
	);
	
	//检测用户是否存在
	public function check_name($login_name,$userid=0){
		if($userid){   //编辑时查询
			$map['userid']  = array('neq',$userid);
			$map['login_name']  = array('eq',$login_name);
		}else{  // 新增是查询
			$map['login_name']  = array('eq',$login_name);
		}
		return $this->where($map)->find();
	}	
}