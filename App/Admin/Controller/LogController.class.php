<?php
namespace Admin\Controller;
use Think\Controller;

class LogController extends BaseController {
    public function index(){
        $log_model = M("sys_log");
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());

        $page=isset($_GET['p'])?$_GET['p']:1;
        $log_list =$log_model->page($page.',15')->order('log_id desc')->select();
        $this->assign('log_list',$log_list);// 赋值数据集
        $count      = $log_model->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $this -> assign('count',$count);
        $this->assign('key_type',20);// 赋值分页输出*/
        $this->assign('key',1);// 赋值分页输出*/
        $this->assign('page',$show);// 赋值分页输出*/
        Log_add(40,'访问行为跟踪列表页面');
        $this->display();
    }

 
}