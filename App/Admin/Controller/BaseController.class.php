<?php
namespace Admin\Controller;
use Think\Controller;

class BaseController extends Controller{
    function _initialize(){
        //检测SESSION
        $this->detection_session();
        //实时检测状态
        $this->detection_status();
        //输出用户信息
        $this->assign("user_find",user_find());
        //查询user_role所有数据
        $this->assign("user_role",user_role());
        //种植区域
        $this->assign("plant_area",Tb_data('area'));
        //读取左侧导航菜单权限
        $this->read_menu();
    }

    //检测SESSION
    function detection_session(){
        if(!isset($_SESSION['account']) || !$_SESSION['user_id']){
            $this->redirect("Login/index");
        }
        switch ($_SESSION){
            //admin账户显示的导航菜单
            case $_SESSION['account'] == 'admin' || $_SESSION['account'] == 'King':
                //0只面对开发人员  读取数据库
                $where['status'] = array('in','0,1');
                $where['type']   = 1;
                $menu = M("sys_menu")->where($where)->field('id,parent_id,path,icon,name')->order("sort asc")->select();
                $this->assign("menus",$menu);
                break;
            case !isset($_SESSION['last_access'])||(time()-$_SESSION['last_access'])>7200:
                session("account", null);
                session("user_id", null);
                session("last_access", null);
                unset($_SESSION);
                session_unset();
                session_destroy();
                $this->error('登录超时，请重新登录');
                $this->redirect("Login/login");
                break;
        }
    }
    //检测状态
    function detection_status(){
        $status = M("sys_user")->where(array('user_id'=>$_SESSION['user_id']))->getField("status");
        if($status != 1){
            $this->error('登录超时，请重新登录', U('login/login'));
            exit;
        }
    }
    //读取左侧导航菜单权限
    function read_menu(){
        switch ($_SESSION){
            //admin账户显示的导航菜单
            case $_SESSION['account'] == 'admin' || $_SESSION['account'] == 'King':
                //0只面对开发人员  读取数据库
                $where['status'] = array('in','0,1');
                $where['type']   = 1;
                $menu = M("sys_menu")->where($where)->field('id,parent_id,path,icon,name,val1,val2')->order("sort asc")->select();
                $this->assign("menus",$menu);
                break;
            default:
                /*
                 *******开发测试使用*********
                 * 从数据库读取左侧导航菜单权限
                 *
                 * */
                //查询该用户组下所有权限值
                if($_SESSION['role_id']){
                    $role_govern = M()->query("SELECT role_id,menu_id,val FROM ims_sys_user_role_govern WHERE role_id = $_SESSION[role_id]");
                    $menu_id = M("sys_user_role_govern")->where(array('role_id'=>$_SESSION['role_id']))->field('menu_id')->select();
                }else{
                    exit("参数错误0001");
                }

                //查询该用户组所有菜单
                if($menu_id){
                    //调用去重函数
                    $menu_id = a_array_unique($menu_id);
                    //调用转换字符串函数
                    $menu_id = switch_($menu_id,'menu_id');
                    $where['id'] = array('in',$menu_id);
                    $menu = M("sys_menu")->where($where)->field('id,parent_id,path,icon,name,val1,val2')->order('sort asc')->select();
                    $this->assign("menus",$menu);
                }else{
                    exit("该用户没有分配权限，参数错误0002");
                }
                /*
                 * 从$_SESSION读取左侧导航菜单权限
                 * 正式上线后启用
                 * */
//                $this->assign("menus",$_SESSION['menu_id']);
                break;
        }
    }
}

