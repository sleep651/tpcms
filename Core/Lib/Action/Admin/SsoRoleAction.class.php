<?php

class SsoRoleAction extends AdminAction {
	public function _initialize() {
		parent::_initialize();  //RBAC 验证接口初始化
	}

	//列表
	public function index(){
/* 		$DB = D('SsoRole');
		$list = $DB->order('rank DESC')->select();
		$this->assign('list',$list);
		$this->display(); */

 		$Model = new Model();
		$list = $Model->Table('sso.sso_role r')->
				join('sso.sso_sys_role as sr on r.roleid=sr.roleid')->
				join('sso.sso_system as s on sr.sysid=s.sysid')->
				field('r.*,s.name as sysname')->
				order('s.rank desc,r.rank desc')->
				select(); 
		$this->assign('list',$list);
		$this->display();
	}

	// 添加
	public function add(){
		$DB = D("SsoRole");
		if(isset($_POST['dosubmit'])) {
			//根据表单提交的POST数据创建数据对象
			if($DB->create()){
				$roleid = create_guid();
				$DB->roleid = $roleid;
				$DB->add();
				$data['id'] = create_guid();
				$data['roleid'] = $roleid;
				$data['sysid'] = $_POST['sysid'];
				if (D("SsoSysRole")->data($data)->add()){
					$this->assign("jumpUrl",U('/Admin/SsoRole/index'));
					$this->success('添加成功！');
				}else{
					$this->error('用户添加成功,但角色对应关系添加失败!');
				}
			}else{
				$this->error($DB->getError());
			}
		}else{
			$ssoSystem = D('SsoSystem')->where('status=\'normal\'')->order('rank DESC')->select();
			$this->assign('ssoSystem',$ssoSystem);
			$this->assign('tpltitle','添加');
			$this->display();
		}
	}
	
	// 编辑
	public function edit(){
		$DB = D("SsoRole");
		if(isset($_POST['dosubmit'])) {
			//根据表单提交的POST数据创建数据对象
			if($DB->create()){
				$DB->save();
				//先删除原角色所属系统记录
				$id = I('roleid');
				D("SsoSysRole")->where(array('roleid' =>$id))->delete();
				$data['id'] = create_guid();
				$data['roleid'] = $_POST['roleid'];
				$data['sysid'] = $_POST['sysid'];	
				//插入新的角色所属系统记录
				if (D("SsoSysRole")->data($data)->add()){
					$this->assign("jumpUrl",U('/Admin/SsoRole/index'));
					$this->success('编辑成功！');
				}else{
					$this->error('编辑失败!');
				}
			}else{
				$this->error($DB->getError());
			}
		}else{
			$id = $this->_get('roleid');
			if(!$id)$this->error('参数错误!');
			$info = $DB->where(array('roleid'=>$id))->find();
			$ssoSystem = D('SsoSystem')->where(array('status' =>'normal'))->order('rank DESC')->select();
			$ssoSysRole = D('SsoSysRole')->field('sysid')->where(array('roleid'=>$id))->find();
			$this->assign('ssoSystem',$ssoSystem);			
			$this->assign('sysid',$ssoSysRole['sysid']);			
			$this->assign('tpltitle','编辑');
			$this->assign('info',$info);
			$this->display('add');
		}
	}
	
	//删除
	public function del(){
		$id = $this->_get('roleid');
		if(!$id)$this->error('参数错误!');
		$DB = D("SsoRole");
		if($DB->where(array('roleid' =>$id))->delete()){
			if(D("SsoSysRole")->where(array('roleid' =>$id))->delete()){
				$this->assign("jumpUrl",U('/Admin/SsoRole/index'));
				$this->success('删除成功！');
			}else{
				$this->error('删除失败!');
			}
		}else{
			
			$this->error('删除失败!');
		}
	}
	
	// 排序权重更新
	public function sort(){
		$sorts = $this->_POST('sort');
		if(!is_array($sorts))$this->error('参数错误!');
		foreach ($sorts as $id => $sort) {
			D('SsoRole')->save( array('roleid' =>$id , 'rank' =>intval($sort) ) );
		}
		$this->assign("jumpUrl",U('/Admin/SsoRole/index'));
		$this->success('更新完成！');
	}
}