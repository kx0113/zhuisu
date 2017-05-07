<?php
namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller{
    public function index(){
        $url = M("sys_menu")->where(array('type'=>2))->order("id asc")->select();
        $this->assign("url",$url);
        $this->display();
    }
    public function Login_Url(){
        $result = M("sys_menu")->where(array('type'=>2))->field('id,name,path')->order("id asc")->select();
        output_data($result);
    }
    public function  login(){
        $this->assign("title","User Login");
        $this->display();
    }
    public function  Register(){
        $this->assign("title","Register");
        $this->display();
    }
    public function loginOut(){
        session_unset();
        session_destroy();
        $this->redirect('index');
        $this->display();
    }
    public function loginCheck(){
        $user_name = I("post.username");
        $password   = I("post.password");

        $where['account']   = $user_name;
        $where['password']  = md5($password);

        $user = M('sys_user')
                ->field('user_id,account,real_name,role_id,company_id,password,status')
                ->where($where)
                ->find();
        if($user['status'] == 1){
            $data['result'] = "status1";
            session("account",$user["account"]);
            session("user_id",$user["user_id"]);
            session("role_id",$user["role_id"]);
            session("last_access",time());
            //写入企业信息company_id
            session("company_id",$user["company_id"]);
            //写入左侧导航菜单权限
            $this->add_menu($user);
            //写入行为跟踪记录
            Log_add(2,'后台登录成功',$user_name);
        }elseif($user['status'] == 2){
            $data['result'] = "status2";
        }elseif($user['status'] == 3){
            $data['result'] = "status3";
        }elseif($user['status'] == 4){
            $data['result'] = "status4";
        }elseif($user['status'] == 5){
            $data['result'] = "status5";
        }else{
            Log_add(2,'后台登录验证错误',$user_name,$password);
            $data['result'] = "error";
        }
        $this->ajaxReturn($data);
    }
    //写入左侧导航菜单权限
    function add_menu($user){
        $role_id = $user['role_id'];
        switch ($user){
            //admin账户显示的导航菜单
            case $user['account'] == 'admin' || $user['account'] == 'King':
                //0只面对开发人员
                break;
            default:

                //保存该用户组下所有权限值  至session
                if($role_id){
                    $role_govern = M()->query("SELECT role_id,menu_id,val FROM ims_sys_user_role_govern WHERE role_id = $role_id");
                    session("role_govern",$role_govern);
                    $menu_id = M("sys_user_role_govern")->where(array('role_id'=>$role_id))->field('menu_id')->select();
                }else{
                    $data['result'] = "status6";
                    $this->ajaxReturn($data);
                    exit("该用户组下没有权限值！");
                }

                //保存该用户组所有菜单  至session
                if($menu_id){
                    //调用去重函数
                    $menu_id = a_array_unique($menu_id);
                    //调用转换字符串函数
                    $menu_id = switch_($menu_id,'menu_id');
                    $where['id'] = array('in',$menu_id);
                    $menu = M("sys_menu")->where($where)->field('id,parent_id,path,icon,name,val1,val2')->order('sort asc')->select();
                    session("menu_id",$menu);
                }else{
                    $data['result'] = "status6";
                    $this->ajaxReturn($data);
                    exit("该用户组下没有添加导航菜单！");
                }
                break;
        }
    }
    public function registerCheck(){
        $account = I("post.account");
        $real_name = I("post.real_name");
        $password   = I("post.password");
        $data['account']   = $account;
        $data['real_name'] = $real_name;
        $data['password']  = md5($password);
        $data['role_id'] = 0;
        $data['status']  = 4;
        $data['source']  = "qtzc";
        $data['time']  = time();

        $user = M("sys_user")->data($data)->add();
        if($user){
            $data['result'] = "success";
        }else{
            $data['result'] = "error";
        }
        $this->ajaxReturn($data);
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
}