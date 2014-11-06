<?php 
class SsoSystemModel extends Model {
	// 实际数据表名（包含表前缀）
	protected $trueTableName = 'sso.sso_system';
	// 主键名称
	protected $pk               =   'sysid';
	//自动完成
	protected $_auto = array (
		array('created','time',Model:: MODEL_BOTH,'function'), 	//新增时
	);	
	//自动验证
	protected $_validate=array(
 		array('name','require','系统名称必须！',1,'',3),
		array('name','','系统名称已经存在！',1,'unique',3), 
		array('syscode','require','系统编码必须！',1,'',3),
		array('syscode','','系统编码已经存在！',1,'unique',3),
		array('status','require','系统状态必须！',1,'',3),
	);

	// 获取所有角色信息
	public function getAllSsoSystem($where = '' , $order = 'rank DESC' ,$field = '*') {
		return $this->field($field)->where($where)->order($order)->select();
	}

	// 获取单个角色信息
	public function getSsoSystem($where = '',$field = '*') {
		return $this->field($field)->where($where)->find();
	}

	// 删除角色
	public function delSsoSystem($where) {
		if($where){
			return $this->where($where)->delete();
		}else{
			return false;
		}
	}

	// 更新角色
	public function upSsoSystem($data) {
		if($data){
			return $this->save($data);
		}else{
			return false;
		}
	}

}