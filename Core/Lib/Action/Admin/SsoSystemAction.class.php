<?php

class SsoSystemAction extends AdminAction {
	public function _initialize() {
		parent::_initialize();  //RBAC 验证接口初始化
	}

	//列表
	public function index(){
		$SsoSystemDB = D('SsoSystem');
		$list = $SsoSystemDB->getAllSsoSystem();
		$this->assign('list',$list);
		$this->display();
	}

	// 添加
	public function add(){
		$SsoSystemDB = D("SsoSystem");
		if(isset($_POST['dosubmit'])) {
			//根据表单提交的POST数据创建数据对象
			if($SsoSystemDB->create()){
				$SsoSystemDB->sysid = create_guid();
				if($SsoSystemDB->add()){
					$this->assign("jumpUrl",U('/Admin/SsoSystem/index'));
					$this->success('添加成功！');
				}else{
					$this->error('添加失败!');
				}
			}else{
				$this->error($SsoSystemDB->getError());
			}
		}else{
			$this->assign('tpltitle','添加');
			$this->display();
		}
	}
	
	// 编辑
	public function edit(){
		$SsoSystemDB = D("SsoSystem");
		if(isset($_POST['dosubmit'])) {
			//根据表单提交的POST数据创建数据对象
			if($SsoSystemDB->create()){
				if($SsoSystemDB->save()){
					$this->assign("jumpUrl",U('/Admin/SsoSystem/index'));
					$this->success('编辑成功！');
				}else{
					$this->error('编辑失败!');
				}
			}else{
				$this->error($SsoSystemDB->getError());
			}
		}else{
			$id = $this->_get('sysid');
			if(!$id)$this->error('参数错误!');
			$info = $SsoSystemDB->getSsoSystem(array('sysid'=>$id));
			$this->assign('tpltitle','编辑');
			$this->assign('info',$info);
			$this->display('add');
		}
	}
	
	//删除
	public function del(){
		$id = $this->_get('sysid');
		if(!$id)$this->error('参数错误!');
		$SsoSystemDB = D("SsoSystem");
		if($SsoSystemDB->delSsoSystem('sysid=\''.$id.'\'')){
			$this->assign("jumpUrl",U('/Admin/SsoSystem/index'));
			$this->success('删除成功！');
		}else{
			
			$this->error('删除失败!');
		}
	}
	
	// 排序权重更新
	public function sort(){
		$sorts = $this->_POST('sort');
		if(!is_array($sorts))$this->error('参数错误!');
		foreach ($sorts as $id => $sort) {
			D('SsoSystem')->upSsoSystem( array('sysid' =>$id , 'rank' =>intval($sort) ) );
		}
		$this->assign("jumpUrl",U('/Admin/SsoSystem/index'));
		$this->success('更新完成！');
	}
}