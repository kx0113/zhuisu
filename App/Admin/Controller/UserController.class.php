<?php
namespace Admin\Controller;
use Think\Controller;

class UserController extends BaseController {
    public function index(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        //搜索
        if($_POST['role_id']){
            $where['role_id'] = array("like","%".$_POST['role_id']."%");
        }
        if($_GET['status']){
            $where['status']=$_GET['status'];
        }else{
            $where['status']=array('in','1,2,5');
        }
        //所有分组
        $sys_user_role = M("sys_user_role")->field('id,name')->select();
        //用户来源
        $sys_user = M("sys_user")->where($where)->select();
        foreach($sys_user as $k=>$v){
            $user_role = M("sys_user_role")->where(array('id'=>$v['role_id']))->select();
            $sys_user[$k]['name']=$user_role['0']['name'];
            if($v['source'] == "qtzc"){
                $sys_user[$k]['sources']="前台注册";
            }else if($v['source'] == "admin"){
                $sys_user[$k]['sources']="admin";
            }else{
                $user_roless = M("sys_user")->where(array('user_id'=>$v['source']))->select();
                foreach($user_roless as $kk=>$vv){
                    $sys_user[$k]['sources']=$vv['account']."后台注册";
                }
            }
        }
        $this->assign("sys_user",$sys_user);
        $this->assign("sys_user_role",$sys_user_role);
        Log_add(6,'访问用户列表');
        $this->display();
    }
    public function toastr(){
        $this->display();
    }
    public function member(){
        $this->display();
    }
    public function audit(){
        $sys_user = M("sys_user")->where(array('user_id'=>$_GET['user_id']))->find();
        $this->assign("sys_user",$sys_user);
        $this->display();
    }
    public function group_look(){
        $model = M("sys_user_role");
        $govern_model = M("sys_user_role_govern");
        //导航菜单
        $menu = M("sys_menu")->where(array('status'=>1))->order("sort asc")->select();
        $this->assign("menus",$menu);
        if($_GET['id']){
            $sys_role = $model->where(array('id'=>$_GET['id']))->find();
            $role_govern = $govern_model->where(array('role_id'=>$_GET['id']))->field('menu_id,val')->select();
            $role_govern_count = count($role_govern);
            $this->assign("sys_role",$sys_role);
            $this->assign("role_govern",$role_govern);
            $this->assign("role_govern_count",$role_govern_count);
        }
            Log_add(8,'访问新建用户组页面');
            $this->display();

    }
    public function group_add(){
        $model = M("sys_user_role");
        $govern_model = M("sys_user_role_govern");
        //导航菜单
        $menu = M("sys_menu")->where(array('status'=>1))->order("sort asc")->select();
        $this->assign("menus",$menu);

        if($_GET['id']){
            $sys_role = $model->where(array('id'=>$_GET['id']))->find();
            $role_govern = $govern_model->where(array('role_id'=>$_GET['id']))->field('menu_id,val')->select();
            $role_govern_count = count($role_govern);
            $this->assign("sys_role",$sys_role);
            $this->assign("role_govern",$role_govern);
            $this->assign("role_govern_count",$role_govern_count);
        }
        if(IS_POST){
            $role_id = $_POST['id'];
            $data = I("post.");
            //如果没有选择权限值
            if(!$_POST['menu_id'] && !$_POST['val_']){
                $this->error("操作失败，请为用户组分配权限！");
            }
            //修改
            if($role_id){
                $res = $model->where(array('id'=>$role_id))->save($data);
                //权限分配 插入
                $this->Govern_menu($role_id,'edit');
                Log_add(7,'修改用户组成功',$data['name']);
            }else{
                $data['status'] = 1;
                $data['addtime'] = date("Y-m-d H:i:s",time());
                $res = $model->data($data)->add();
                //权限分配 插入
                if($res){
                    $this->Govern_menu($res,'add');
                }else{
                    $this->error("操作失败，请稍后再试！", U('User/group',array('menu_id'=>$_GET['menu_id'])));
                    exit;
                }
                Log_add(7,'新建用户组成功',$data['name']);
            }
        }else{
            Log_add(8,'访问新建用户组页面');
            $this->display();
        }
    }
    //权限分配 插入
    function Govern_menu($role_id,$type){
        //如果是edit 先删除后插入
        if($type == 'edit'){
            $del = M()->execute("DELETE FROM ims_sys_user_role_govern WHERE role_id = $role_id ");
        }
        //插入一级menu_id  并增加相应权限值
        if($_POST['menu_id']){
            foreach($_POST['menu_id'] as $val){
                $ress = M()->execute("INSERT INTO ims_sys_user_role_govern (role_id, menu_id,val,addtime) VALUES ($role_id, $val,0,DATE_FORMAT(NOW(),'%Y-%m-%d %H-%i-%s'))");
            }
        }
        //插入2，3级menu_id  并增加相应权限值
        if($_POST['val_']){
            foreach($_POST['val_'] as $value){
                $menu_id = substr($value,0,-2);
                $val = strrchr($value,"_");
                $val = substr($val,1);
                $res = M()->execute("INSERT INTO ims_sys_user_role_govern  (role_id,menu_id,val,addtime) VALUES ($role_id,$menu_id,$val,DATE_FORMAT(NOW(),'%Y-%m-%d %H-%i-%s'))");
            }
        }
        if($type == 'add'){
            if($role_id){
                $this->success("新建用户组成功！", U('User/group',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("新建用户组失败，请稍后再试！", U('User/group',array('menu_id'=>$_GET['menu_id'])));
            }
        }
        if($type == 'edit'){
            if($res || $ress){
                $this->success("修改成功！", U('User/group',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("修改失败，请稍后再试！", U('User/group',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    public function group(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
//        $menu_id = $_GET['menu_id'];
//        if($menu_id){
//            if($_SESSION['account'] == 'admin' || $_SESSION['account'] == 'King'){
//                $menu = 'admin';
//            }else{
//                //如果是分配权限用户
//                if($menu_id){
//                    $menu = M()->query("SELECT val FROM ims_sys_user_role_govern WHERE role_id = $_SESSION[role_id] AND menu_id = $menu_id");
//                    //调用转换字符串函数
//                    $menu = switch_($menu,'val');
//                    $menu = explode(',',$menu);
//                    $menu =array('val'=>$menu);
//                }
//            }
//            $this->assign("menu_id",$menu);
//        }
        Log_add(4,'访问用户组权限管理列表');
        $this->display();
    }
    public function login(){
        $this->display("login_login");
    }
    public function add_user(){
        //企业信息
        $this->assign("company_list",Company_list());
        $this->display();
    }
    public function edit_user(){
        //企业信息
        $this->assign("company_list",Company_list());
        $data = I("post.");
        $user_id = $_GET['user_id'];
        $checks = M("sys_user")->where(array('user_id'=>$user_id))->find();
        $this->assign("checks",$checks);
        if(IS_POST){
            $res = M("sys_user")->where(array('user_id'=>$user_id))->data($data)->save();
            if($res){
                $this->success("修改成功！", U('User/index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("修改失败，请稍后再试！",U('User/index',array('menu_id'=>$_GET['menu_id'])));
            }
        }else{
            $this->display();
        }
    }
    public function file(){
    }
    //检测是否存在该帐号
    public function checks(){
        $checks = M("sys_user")->where(array('account'=>I("post.account")))->find();
        if ($checks) {
            $json['result'] = 'yes';
        } else {
            $json['result'] = 'no';
        }
        $this->ajaxReturn($json);
    }
    //新建用户
    public function add_users(){
        $data = I("post.");
        $data['source']    = $_SESSION['user_id'];
        $data['password']   = md5($data['password']);
        $data['time']    = time();
        $res = M("sys_user")->data($data)->add();
        Log_add(5,'新建用户',$data['account']);
        $this->js_ajaxReturn($res);
    }
    //修改密码
    public function modifi_password(){
        $sys_user = M("sys_user");
        //原密码
        $pass = md5(trim($_POST['pass']));
        //新密码
        $password = md5(trim($_POST['password']));
        //查询原密码
        $sys_user_password = $sys_user->where(array('user_id'=>$_SESSION['user_id']))->getField('password');
        //如果旧密码与新密码一致返回
        if($pass == $password){
            $json['result'] = 'repetition';
            $this->ajaxReturn($json);
            exit;
        }

        if($pass == $sys_user_password){
            $data['password'] = $password;
            $res = $sys_user->where(array('user_id'=>$_SESSION['user_id']))->save($data);
            if ($res) {
                $json['result'] = 'success';
            } else {
                $json['result'] = 'error';
            }
            $this->ajaxReturn($json);
        }else{
            $json['result'] = 'no';
            $this->ajaxReturn($json);
        }
    }
    public function switchs(){
        switch ($_POST['tag']){

           //禁用账户
           case $_POST['tag'] == "save":
               $data['status'] = $_POST['status'];
               $res = M("sys_user")->where(array('user_id'=>$_POST['id']))->data($data)->save();
               $this->js_ajaxReturn($res);
               break;

            //删除帐户
           case $_POST['tag'] == "del":
               $data['status'] = 3;
               $res = M("sys_user")->where(array('user_id'=>$_POST['id']))->data($data)->save();
               if ($res) {
                   $json['res'] = 'delsuc';
               } else {
                   $json['res'] = 'delerr';
               }
               $this->ajaxReturn($json);
               break;

            //审核通过 注册用户
            case $_POST['tag'] == "pass":
                $data['status'] = 1;
                $data['role_id'] = $_POST['role_id'];
                $res = M("sys_user")->where(array('user_id'=>$_POST['id']))->data($data)->save();
                $this->js_ajaxReturn($res);
                break;

            //审核拒绝 注册用户
            case $_POST['tag'] == "refuse":
                $data['status'] = 3;
                $res = M("sys_user")->where(array('user_id'=>$_POST['id']))->data($data)->save();
                $this->js_ajaxReturn($res);
                break;

            //member会员中心
            case $_POST['tag'] == "member":
                $data['picture'] = $_POST['picture'];
                $data['phone'] = $_POST['id'];
                $res = M("sys_member")->data($data)->add();
                $this->js_ajaxReturn($res);
                break;
       }
    }
//ajax
    function js_ajaxReturn($res){
        if ($res) {
            $json['res'] = 'success';
        } else {
            $json['res'] = 'error';
        }
        $this->ajaxReturn($json);
    }
}