<?php
/**
 * 后台菜单(权限控制节点)模块
 * 
 */

class SsoNodeAction extends AdminAction {
	public function _initialize() {
		parent::_initialize();	//RBAC 验证接口初始化
		Vendor('Common.Tree');	//导入通用树型类
	}
	
	//菜单列表
	public function index(){
		
		$Node = M('sso.vw_sso_node',Null)->order('rank DESC')->select();
		$array = array();
		// 构建生成树中所需的数据
		foreach($Node as $k => $r) {
			$r['id']  = $r['id'];
			$r['pid']   = $r['pid'];
			$r['name']   = $r['name'];
			$r['lvl']    = $r['lvl'];
			$r['status']  = $r['status']=='normal' ? '<font color="red">√</font>' :'<font color="blue">×</font>';
			$r['submenu'] = $r['type']=='MT' ? '<font color="#cccccc">添加子菜单</font>' : "<a href='".U('/Admin/SsoNode/add/pid/'.$r['id'].'/sysid/'.$r['sysid'].'/type/'.$r['type'])."'>添加子菜单</a>";
			$r['edit']    = $r['type']=='SY' ? '<font color="#cccccc">修改</font>' : "<a href='".U('/Admin/SsoNode/edit/id/'.$r['id'].'/pid/'.$r[pid])."'>修改</a>";
			$r['del']     = $r['type']=='SY' ? '<font color="#cccccc">删除</font>' : "<a onClick='return confirmurl(\"".U('/Admin/SsoNode/del/id/'.$r['id'])."\",\"确定删除该菜单吗?\")' href='javascript:void(0)'>删除</a>";
			$r['input_disable']= $r['pid']=='0' ? 'disabled=\'true\'':'';//系统的排序，在这里不可修改
			$r['type']   = $r['type'];
			switch ($r['type']) {
				case 'AT':
					$r['type'] = '菜单';
					break;
				case 'MT':
					$r['type'] = '功能';
					break;
				case 'SY':
					$r['type'] = '系统';
					break;
			}
			$array[]      = $r;
		}
		$str  = "<tr class='tr'>
				    <td align='center'><input type='text' \$input_disable value='\$rank' size='3' name='sort[\$id]'></td>
				    <td align='center'>\$id</td> 
				    <td >\$spacer \$name </td> 
				    <td align='center'>\$type</td> 
				    <td align='center'>\$status</td> 
				    <td align='center'>\$lvl</td> 
					<td align='center'>
						\$submenu | \$edit | \$del
					</td>
				  </tr>";

  		$Tree = new Tree();
		$Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$Tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$Tree->init($array);
		$html_tree = $Tree->get_tree('0', $str);

		$this->assign('html_tree',$html_tree);
		$this->display();
	}

	//添加菜单
	public function add(){
		if(isset($_POST['dosubmit'])) {
			$NodeDB = D("SsoNode");
			//根据表单提交的POST数据创建数据对象
			if($NodeDB->create()){
				$id = create_guid();
				$NodeDB->nodeid = $id;
				if($NodeDB->add()){
					$this->assign("jumpUrl",U('/Admin/SsoNode/index'));
    				$this->success('添加成功！');
				}else{
					 $this->error('添加失败!');
				}
			}else{
				$this->error($NodeDB->getError());
			}
			
		}else{
			$Node = M('sso.vw_sso_node',Null)->order('rank DESC')->select();
			$pid = I('pid');	//系统各菜单视图的父节点
			
			$sysid = I('sysid');	
			$type = I('type');	
			switch ($type) {
				case 'AT':
					$info['super_id']=$pid;
					break;
				case 'SY':
					$info['super_id']=null;
					break;
			}
			$info['sysid']=$sysid;
			$this->assign('info', $info);
			
			$array = array();
			foreach($Node as $k => $r) {
				//$r['id']         = $r['id'].'-'.$r['sysid'].'-'.$r['super_id'];
				//$r['sysid']      = $r['sysid'];
				//$r['name']       = $r['name'];
				$r['disabled']   = $r['node_type']=='MT' ? 'disabled' : '';
				$array[$r['id']] = $r;
			}
			$str  = "<option value='\$id' sysid='\$sysid' type='\$type' \$selected \$disabled >\$spacer \$name</option>";
			$Tree = new Tree();
			$Tree->init($array);
			$select_categorys = $Tree->get_tree(0, $str, $pid);
			$this->assign('tpltitle','添加');
			$this->assign('select_categorys',$select_categorys);
			$this->display();
		}

	}

	//编辑菜单
	public function edit(){
		if(isset($_POST['dosubmit'])) {
			$NodeDB = D("SsoNode");
			//根据表单提交的POST数据创建数据对象
			if($NodeDB->create()){
				if($NodeDB->save()){
					$this->assign("jumpUrl",U('/Admin/SsoNode/index'));
    				$this->success('编辑成功！');
				}else{
					 $this->error('编辑失败!');
				}
			}else{
				$this->error($NodeDB->getError());
			}
			
		}else{
			$id = I('id');
			$pid = I('pid');
			if(!$id || !$pid)$this->error('参数错误!');
			$NodeDB = M('sso.vw_sso_node',Null);
			$allNode = $NodeDB->order('rank DESC')->select();
			$array = array();
			foreach($allNode as $k => $r) {
				//$r['id']         = $r['id'].'-'.$r['sysid'].'-'.$r['super_id'];
				//$r['sysid']      = $r['sysid'];
				//$r['name']       = $r['name'];
				$r['disabled']   = $r['node_type']=='MT' ? 'disabled' : '';
				$array[$r['id']] = $r;
			}
			$str  = "<option value='\$id' sysid='\$sysid' type='\$type' \$selected \$disabled >\$spacer \$name</option>";
			$Tree = new Tree();
			$Tree->init($array);
			$select_categorys = $Tree->get_tree(0, $str, $pid);
			
			$this->assign('tpltitle','编辑');
			$this->assign('select_categorys',$select_categorys);
			$this->assign('info', M('sso.sso_node',Null)->where(array('nodeid'=>$id))->find());
			$this->display('add');
		}

	}
	
	//删除菜单
	public function del(){
		$id = I('id');
		if(!$id)$this->error('参数错误!');
		$NodeDB = M('sso.sso_node',Null);
		$info = $NodeDB -> where(array('nodeid'=>$id))->find();
		if($NodeDB->where(array('super_id'=>$id))->select()){
			$this->error('存在子菜单，不可删除!');
		}
		if($NodeDB->where(array('nodeid' =>$id))->delete()){
			$this->assign("jumpUrl",U('/Admin/SsoNode/index'));
			$this->success('删除成功！');
		}else{
			$this->error('删除失败!');
		}
	}

	//菜单排序权重更新
	public function sort(){
		$sorts = $this->_POST('sort');
		if(!is_array($sorts))$this->error('参数错误!');
		foreach ($sorts as $id => $sort) {
			D('SsoNode')->save( array('nodeid' =>$id , 'rank' =>intval($sort) ) );
		}
		$this->assign("jumpUrl",U('/Admin/SsoNode/index'));
		$this->success('更新完成！');
	}

	
}