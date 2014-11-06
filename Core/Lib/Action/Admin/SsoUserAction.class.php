<?php
/**
 * 后台用户管理模块
 *
 */
class SsoUserAction extends AdminAction {
	public function _initialize() {
		parent::_initialize();  //RBAC 验证接口初始化
	}

	/* ========用户部分======== */
	public function index(){
		import('ORG.Util.Page');// 导入分页类
		$map = array();
		$UserDB = M('sso.User','sso_');
		$count = $UserDB->where($map)->count();
		$Page       = new Page($count);// 实例化分页类 传入总记录数
		// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
		$nowPage = isset($_GET['p'])?$_GET['p']:1;
		$show       = $Page->show();// 分页显示输出
		$list = $UserDB->where($map)->order('created DESC')->page($nowPage.','.C('web_admin_pagenum'))->select();
		$this->assign('list',$list);
		$this->assign('page',$show);// 赋值分页输出 
		$this->display();
	}
	
	// 添加用户
	public function add(){
		$UserDB = D("SsoUser");
		if(isset($_POST['dosubmit'])) {
			$login_pwd = $_POST['login_pwd'];
			$repassword = $_POST['repassword'];
			if(empty($login_pwd) || empty($repassword)){
				$this->error('密码必须！');
			}
			if($login_pwd != $repassword){
				$this->error('两次输入密码不一致！');
			}
			//根据表单提交的POST数据创建数据对象
			if($UserDB->create()){
				$userid = create_guid();
				$UserDB->userid = $userid;				
				$UserDB->add();
				$this->assign("jumpUrl",U('/Admin/SsoUser/index'));
				$this->success('添加成功！');
			}else{
				$this->error($UserDB->getError());
			}
		}else{
			$this->assign('tpltitle','添加');
			$this->display();
		}
	}

	// 编辑用户
	public function edit(){
		$UserDB = D("SsoUser");
		if(isset($_POST['dosubmit'])) {
			$login_pwd = $_POST['login_pwd'];
			$repassword = $_POST['repassword'];
			if(!empty($login_pwd) || !empty($repassword)){
				if($login_pwd != $repassword){
					$this->error('两次输入密码不一致！');
				}
				$_POST['login_pwd'] = md5($login_pwd);
			}
	
			if(empty($login_pwd) && empty($repassword)) unset($_POST['login_pwd']);   //不填写密码不修改
	
			//根据表单提交的POST数据创建数据对象
			if($UserDB->create()){
				if($UserDB->save()){
					$this->assign("jumpUrl",U('/Admin/SsoUser/index'));
					$this->success('编辑成功！');
				}else{
					$this->error('编辑失败!');
				}
			}else{
				$this->error($UserDB->getError());
			}
		}else{
			$userid = $this->_get('userid');
			if(!$userid)$this->error('参数错误!');
			$info = $UserDB->where(array('userid'=>$userid))->find();
			$this->assign('tpltitle','编辑');
			$this->assign('info',$info);
			$this->display('add');
		}
	}
	
	//删除
	public function del(){
		$id = $this->_get('userid');
		if(!$id)$this->error('参数错误!');
		$DB = D("SsoUser");
		if($DB->where('userid=\''.$id.'\'')->delete()){
			$this->assign("jumpUrl",U('/Admin/SsoUser/index'));
			$this->success('删除成功！');
		}else{
				
			$this->error('删除失败!');
		}
	}
	
	//ajax 验证用户名
	public function check_username(){
		$userid = $this->_get('userid');
		$login_name = $this->_get('login_name');
		if(D("SsoUser")->check_name($login_name,$userid)){
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/* ========角色设置部分======== */
	/**
	 *  检查指定用户是否有权限
	 * @param array $node node表中某记录数组
	 * @param int $userid 需要检查的用户ID
	 * @param int $access access表的所有数组记录
	 */
	public function is_checked($node,$userid,$access) {
		$nodetemp = array(
				'id' =>$node['id'],
				'pid' =>$node['pid'],
				'userid' =>$userid,
		);
		$info = in_array($nodetemp, $access);
		if($info){
			return true;
		} else {
			return false;
		}
	}	
	
	//角色浏览
	public function access(){
		$userid = $this->_get('userid');
		if(!$userid) $this->error('参数错误!');
		Vendor('Common.Tree');  //导入通用树型类
	
		$Tree = new Tree();
		$Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$Tree->nbsp = '&nbsp;&nbsp;&nbsp;';
	
		$NodeDB = M('sso.vw_sso_sys_role',Null);
		$node = $NodeDB->select();
		$AccessDB = M('sso.vw_sso_user_sys_role',Null);
		$access = $AccessDB->where('userid=\''.$userid.'\'')->select();
		//$access = $AccessDB->getAllAccess('','role_id,node_id,pid,level');
		foreach ($node as $n=>$t) {
			$node[$n]['checked'] = ($this->is_checked($t,$userid,$access))? ' checked' : '';
			//$node[$n]['depth'] = $AccessDB->get_level($t['id'],$node);
			$node[$n]['pid_node'] = ($t['pid'])? ' class="tr lt child-of-node-'.$t['pid'].'"' : '';
		}
		$str  = "<tr id='node-\$id' \$pid_node>
                    <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='nodeid[]' value='\$id' class='radio' \$checked > \$name (\$code)</td>
                </tr>";
	
		$Tree->init($node);
		$html_tree = $Tree->get_tree(null, $str);
		$this->assign('html_tree',$html_tree);
	
		$this->display();
	}

	//权限编辑
	public function access_edit(){
		$userid = I('userid');
		$nodeid = $this->_post('nodeid');//选中的节点(包含系统和角色，系统的pid=null)
		if(!$userid) $this->error('参数错误!');
		$UserRoleDB = M('sso.sso_user_role',Null);//用户和角色关系表
		$UserSystemDB = M('sso.sso_user_sys_role',Null);//用户和系统关系表

		if (is_array($nodeid) && count($nodeid) > 0) {  //提交得有数据，则修改原权限配置
			 //先删除原用户组的权限配置
			$UserRoleDB->where(array('userid'=>$userid))->delete();
			$UserSystemDB->where(array('userid'=>$userid))->delete();

			$NodeDB = M('sso.vw_sso_sys_role',Null);
			$node = $NodeDB->select();
	
			foreach ($node as $_v) $node[$_v[id]] = $_v;
			foreach($nodeid as $k => $node_id){
				$pid=$node[$node_id]['pid']; 
				//pid=null选中节点是系统，否则是角色
				if($pid){
					$r['id'] = create_guid();
					$r['roleid'] = $node[$node_id]['id'];
					$r['userid'] = $userid;
					$userRoleData[]=$r;
				}else{
					$r['id'] = create_guid();
					$r['sysid'] = $node[$node_id]['id'];
					$r['userid'] = $userid;
					$userSystemData[]=$r;
				}
			}
			// 重新创建角色的权限配置
			$r1 = $UserRoleDB->addAll($userRoleData);   
			$r2 = $UserSystemDB->addAll($userSystemData); 
		} else {    //提交的数据为空，则删除权限配置
			$UserRoleDB->where(array('userid'=>$userid))->delete();
			$UserSystemDB->where(array('userid'=>$userid))->delete();			
		}
		$this->assign("jumpUrl",U('/Admin/SsoUser/access',array('userid'=>$userid)));
		$this->success('设置成功！');
	}	
}