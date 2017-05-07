<?php
/* 基地准出-导入记录
 * Author: Rocky
 * Date: 2016-12-5
 *  */
namespace Admin\Controller;
use Think\Controller;

class JdstaticlogController extends BaseController {
    //导入数据记录列表
    public function index(){
        $where = array();
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['name'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['addtime'] = array('like', "%{$key}%");
                    break;
            }
        }
        $m = M('base_inlog');
        $where['status'] = 1;
        $page=isset($_GET['p'])?$_GET['p']:1;
        $info = $m->where($where)->order('lid desc')->page($page.',10')->select();
        $this->assign('info',$info);
        $count      = $m->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);
        $this->assign('key',$key);
        $this->assign('page',$show);
        $this->assign("menu_id",Detection_menu());
        Log_add(134,'访问导入数据记录页面');
        $this->display();
    }
    
    //删除导入数据记录
    public function del_log(){
        $m = M('base_inlog');
        $where = array();
        $data['status'] = 0;
        if ($_GET['lid']){
            $where['lid'] = intval($_GET['lid']);
    
            if($m->where($where)->save($data)){
                Log_add(135,'删除导入数据记录',$where['lid']);
                $this->success('删除成功',U('index',array('menu_id'=>$_GET['menu_id'])));
            }
        }elseif(IS_POST){
            $where['lid']=array('in',$_POST['ids']);
            $res = $m ->where($where)->save($data);
            if($res){
                Log_add(135,'删除导入数据记录',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
    }
    
    //AJAX
    function js_ajaxReturn($res){
        if ($res) {
            $json['res'] = 'success';
        } else {
            $json['res'] = 'error';
        }
        $this->ajaxReturn($json);
    }
}