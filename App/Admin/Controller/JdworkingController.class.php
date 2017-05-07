<?php
/* 基地准出-加工档案
 * Author: Rocky
 * Date: 2016-12-5
 *  */
namespace Admin\Controller;
use Think\Controller;

class JdworkingController extends BaseController {
    
    //采购记录管理
    public function index(){
        session_start();
        $where = array('status'=>1);
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['jgpc'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['cg_unit'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['farmer'] = array('like', "%{$key}%");
                    break;
                case $key_type == "4":
                    $where['tel'] = array('like', "%{$key}%");
                    break;
            }
        }
        
        $m = M('base_jgcgrecord');
        $page=isset($_GET['p'])?$_GET['p']:1;
        $qy = $_SESSION['company_id'];
        if (!empty($qy)){
            $where['company_id'] = $qy;
            //dump($where);
            $info = $m->where($where)->order('cid desc')->page($page.',10')->select();
            $company = M('sys_company')->field('name')->where(array('status'=>1,'company_id'=>$qy))->find();
            foreach($info as $k1=>$v1){
                $info[$k1]['company_name'] = $company['name'];
            }
        }else{
            $info = $m->where($where)->order('cid desc')->page($page.',10')->select();
            foreach($info as $k=>$v) {
                $company = M('sys_company')->field('name')->where(array('status' => 1,'company_id'=>$v['company_id']))->select();
                $info[$k]['company_name'] = $company[0]['name'];
            }
        }
        $this->assign('info',$info);
        $count      = $m->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);
        $this->assign('key',$key);
        $this->assign('page',$show);
        $this->assign("menu_id",Detection_menu());
        Log_add(101,'访问采购记录管理列表页面');
        $this->display();
    }
    
    //采购记录管理添加
    public function add_purchase(){
        
        if (IS_POST){
            $data = I('post.');
            $m = M('base_jgcgrecord');
            $data['status'] = 1;
            $data['addtime'] = date('Y-m-d H:i:s');
            if($_SESSION['company_id'] != 0) {
                $data['company_id'] = $_SESSION['company_id'];
            }
            if($_POST['cid']){
                $res = $m->where(array('cid'=>$_POST['cid']))->save($data);
                if($res){
                    Log_add(104,'修改采购记录',$_POST['cid']);
                    $this->success("修改成功！", U('Jdworking/index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请稍后再试！", U('Jdworking/index',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $tm = date('Y.m.d');
                $where1['jgpc'] = array('like','%'.$tm.'%');
                $pc = $m->where($where1)->where(array('status'=>1))->select();
                //var_dump($pc);die();
               // $num = count($pc);
               // $num++;
               // $data['jgpc'] =  $_SESSION['company_id'].'.'.date('Y.m.d.').$num;

                //update   2016-12-31 dfl   加工批次对应多个采收批次
                /*start*/
                $companyinfo=M('sys_company')->field('name')->where(array('company_id'=>$_SESSION['company_id']))->find();//获取采购单位-即当前登录账号所属公司名称
                $data['cg_unit']=$companyinfo['name'];
                /*end*/
                $res = $m->add($data);
                if($res){
                    Log_add(102,'新增采购记录',$res);
                    $this->success("新增成功！", U('Jdworking/index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请稍后再试！", U('Jdworking/index',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            $com = $_SESSION['company_id'];
            Log_add(102,'访问新增基地采购记录页');
            //dump($com);die();
            $this->assign('com',$com);
            $this->display();
        }
    }
    
    //采购记录编辑信息展示
    public function edit_purchase(){
        $model = M("base_jgcgrecord");
        if($_GET['cid']){
            $where = array();
            $where['cid'] = intval($_GET['cid']);
            $info = $model ->where($where)->find();
            $com = $_SESSION['company_id'];
            $this->assign('com',$com);
            $this->assign("info",$info);
            $this->assign("menu_id",Detection_menu());
            $this->display('add_purchase');
        }else{
            $this->error('非法请求');
        }
    }
    
    //采购记录详情页
    public function show_purchase(){
        if (!empty($_GET['cid'])){
            $where = array();
            $where['cid'] = intval($_GET['cid']);
            
        }
        $info = M('base_jgcgrecord')->where($where)->select();
        if (empty($info)){
            $this->error('未找到该信息');
        }else{
            $info = $info[0];
        }
        //dump($info);die();
        if($_SESSION['company_id'] != 0){
            $com = $_SESSION['company_id'];
            $company = M('sys_company')->field('name')->where(array('status' => 1, 'company_id' => $com))->find();
            $info['company_name'] = $company['name'];
        }else{
            $company_id3 = M('base_jgcgrecord')->where(array('cid'=>$where['cid']))->find();
            $company_id2 = $company_id3['company_id'];
            $company = M('sys_company')->field('name')->where(array('status' => 1, 'company_id' => $company_id2))->find();
            $info['company_name'] = $company['name'];
        }
        $this->assign('info',$info);
        $this->display();
    }
    
    //删除采购记录
    public function del_purchase(){
        $m = M('base_jgcgrecord');
        $where = array();
        $data['status'] = 0;
        if ($_GET['cid']){
            $where['cid'] = intval($_GET['cid']);
            
            if($m->where($where)->save($data)){
                Log_add(105,'删除采购记录',$where['cid']);
                $this->success('删除成功',U('index',array('menu_id'=>$_GET['menu_id'])));
            }
        }elseif($_POST['ids']){
            $where['cid']=array('in',$_POST['ids']);
            $data['status'] = 0;
            $res = $m ->where($where)->save($data);
            if($res){
                Log_add(105,'删除采购记录',$res);
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
    
    //加工环境管理-环境卫生检查记录列表
    public function healthList(){
        session_start();
        $where = array();
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
            
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['jgpc'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['cf_address'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['tools'] = array('like', "%{$key}%");
                    break;
                case $key_type == "4":
                    $where['base_status'] = array('like', "%{$key}%");
                    break;
                case $key_type == "5":
                    $where['date'] = array('like', "%{$key}%");
                    break;
            }
        }
        $m = M('base_jghealthcheck');
        $page=isset($_GET['p'])?$_GET['p']:1;
        

        $where['status'] = 1;
        $qy = $_SESSION['company_id'];
        if (!empty($qy)){
            $where['company_id'] = $qy;
        }
        $info = $m->where($where)->order('hid desc')->page($page.',10')->select();
        $this->assign('info',$info);
        $count      = $m->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);
        $this->assign('key',$key);
        $this->assign('page',$show);
        $this->assign('cz','health');
        $this->assign("menu_id",Detection_menu());
        Log_add(106,'访问加工环境-卫生检查记录列表页面');
        $this->display();
    }
    
    //环境信息管理添加
    public function add_health(){
        if (IS_POST){
            $data = I('post.');
            $m = M('base_jghealthcheck');
            $data['status'] = 1;
            $data['addtime'] = date('Y-m-d H:i:s');
            $data['company_id'] = $_SESSION['company_id'];
            if($_POST['hid']){
                $res = $m->where(array('hid'=>$_POST['hid']))->save($data);
                if($res){
                    Log_add(109,'修改采购记录',$_POST['hid']);
                    $this->success("修改成功！", U('healthList',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请稍后再试！", U('healthList',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{

                $res = $m->add($data);
                if($res){
                    Log_add(107,'新增环境信息记录',$res);
                    $this->success("新增成功！", U('healthList',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请稍后再试！", U('healthList',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(108,'访问新增环境检查信息页');
            $this->display();
        }
    }
    
    //环境卫生检查编辑信息展示
    public function edit_health(){
        $model = M("base_jghealthcheck");
        if($_GET['hid']){
            $where = array();
            $where['hid'] = intval($_GET['hid']);
            $info = $model ->where($where)->find();
            $this->assign("info",$info);
            $this->display('add_health');
        }else{
            $this->error('非法请求');
        }
    }
    
    //卫生环境检查详情页
    public function show_health(){
        if (!empty($_GET['hid'])){
            $where = array();
            $where['hid'] = intval($_GET['hid']);
    
        }
        $info = M('base_jghealthcheck')->where($where)->select();
        if (empty($info)){
            $this->error('未找到该信息');
        }else{
            $info = $info[0];
        }
        //dump($info);die();
        $this->assign('info',$info);
        $this->display();
    }
    
    //删除环境检查记录
    public function del_health(){
        $m = M('base_jghealthcheck');
        $where = array();
        $data['status'] = 0;
        if ($_GET['hid']){
            $where['hid'] = intval($_GET['hid']);
    
            if($m->where($where)->save($data)){
                Log_add(110,'删除环境检查记录',$where['hid']);
                $this->success('删除成功',U('healthList',array('menu_id'=>$_GET['menu_id'])));
            }
        }elseif(IS_POST){
            $where['hid']=array('in',$_POST['ids']);
            $res = $m ->where($where)->save($data);
            if($res){
                Log_add(110,'删除环境检查记录',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
    }
    
    //人员消毒记录
    public function toolList(){
        session_start();
        $where = array();
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
        
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['jgpc'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['number'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['project'] = array('like', "%{$key}%");
                    break;
                case $key_type == "4":
                    $where['checker'] = array('like', "%{$key}%");
                    break;
                case $key_type == "5":
                    $where['product_time'] = array('like', "%{$key}%");
                    break;
            }
        }
        $m = M('base_jgtoolcheck');
        $page=isset($_GET['p'])?$_GET['p']:1;
        
        //$where['company_id'] = $_SESSION['company_id'];
        $where['status'] = 1;
        $qy = $_SESSION['company_id'];
        if (!empty($qy)){
            $where['company_id'] = $qy;
        }
        $info = $m->where($where)->order('tid desc')->page($page.',10')->select();
        
        $this->assign('info',$info);
        $count      = $m->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);
        $this->assign('key',$key);
        $this->assign('page',$show);
        $this->assign('cz','tool');
        $this->assign("menu_id",Detection_menu());
        Log_add(111,'访问加工环境-人员工具消毒记录列表页面');
        $this->display();
    }
    
    //添加人员消毒记录
    public function add_tool(){
        if (IS_POST){
            $data = I('post.');
            $m = M('base_jgtoolcheck');
            $data['status'] = 1;
            $data['addtime'] = date('Y-m-d H:i:s');
            $data['company_id'] = $_SESSION['company_id'];
            if($_POST['tid']){
                $res = $m->where(array('tid'=>$_POST['tid']))->save($data);
                if($res){
                    Log_add(114,'修改人员工具消毒记录',$_POST['tid']);
                    $this->success("修改成功！", U('toolList',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请稍后再试！", U('toolList',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
    
                $res = $m->add($data);
                if($res){
                    Log_add(113,'新增人员工具消毒记录',$res);
                    $this->success("新增成功！", U('toolList',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请稍后再试！", U('toolList',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(112,'访问新增人员工具消毒记录信息页');
            $this->display();
        }
    }
    
    //人员工具消毒记录编辑信息展示
    public function edit_tool(){
        $model = M("base_jgtoolcheck");
        if($_GET['tid']){
            $where = array();
            $where['tid'] = intval($_GET['tid']);
            $info = $model ->where($where)->find();
            $this->assign("info",$info);
            $this->display('add_tool');
        }else{
            $this->error('非法请求');
        }
    }
    
    //人员工具消毒详情页
    public function show_tool(){
        if (!empty($_GET['tid'])){
            $where = array();
            $where['tid'] = intval($_GET['tid']);
    
        }
        $info = M('base_jgtoolcheck')->where($where)->select();
        if (empty($info)){
            $this->error('未找到该信息');
        }else{
            $info = $info[0];
        }
        //dump($info);die();
        $this->assign('info',$info);
        $this->display();
    }
    
    //删除人员工具消毒检查记录
    public function del_tool(){
        $m = M('base_jgtoolcheck');
        $where = array();
        $data['status'] = 0;
        if ($_GET['tid']){
            $where['tid'] = intval($_GET['tid']);
    
            if($m->where($where)->save($data)){
                Log_add(115,'删除人员工具消毒记录',$where['tid']);
                $this->success('删除成功',U('toolList',array('menu_id'=>$_GET['menu_id'])));
            }
        }elseif(IS_POST){
            $where['tid']=array('in',$_POST['ids']);
            $res = $m ->where($where)->save($data);
            if($res){
                Log_add(115,'删除人员工具消毒记录',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
    }
    
    //加工过程管理列表
    public function process(){
        session_start();
        $where = array();
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
    
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['pdate'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['pname'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['ptype'] = array('like', "%{$key}%");
                    break;
                case $key_type == "4":
                    $where['price'] = array('like', "%{$key}%");
                    break;
            }
        }
        $m = M('base_jgprocessing');
        $page=isset($_GET['p'])?$_GET['p']:1;
    
        //$where['company_id'] = $_SESSION['company_id'];
        $where['status'] = 1;
        $qy = $_SESSION['company_id'];
        if (!empty($qy)){
            $where['company_id'] = $qy;
        }
        $info = $m->where($where)->order('pid desc')->page($page.',10')->select();
    
        $this->assign('info',$info);
        $count      = $m->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);
        $this->assign('key',$key);
        $this->assign('page',$show);
        $this->assign("menu_id",Detection_menu());
        Log_add(116,'访问加工过程管理列表页面');
        $this->display();
    }
    
    //添加加工过程干燥记录
    public function add_process(){
        if (IS_POST){
            $data = I('post.');
            $m = M('base_jgprocessing');
            $data['status'] = 1;
            $data['addtime'] = date('Y-m-d H:i:s');
            $data['company_id'] = $_SESSION['company_id'];
            if($_POST['pid']){
                $res = $m->where(array('pid'=>$_POST['pid']))->save($data);
                if($res){
                    Log_add(119,'修改人员工具消毒记录',$_POST['pid']);
                    $this->success("修改成功！", U('process',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请稍后再试！", U('process',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
    
                $res = $m->add($data);
                if($res){
                    Log_add(117,'新增加工过程干燥记录',$res);
                    $this->success("新增成功！", U('process',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请稍后再试！", U('process',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(118,'访问新增加工过程干燥记录页');
            $this->display();
        }
    }
    
    //加工过程干燥记录编辑信息展示
    public function edit_process(){
        $model = M("base_jgprocessing");
        if($_GET['pid']){
            $where = array();
            $where['pid'] = intval($_GET['pid']);
            $info = $model ->where($where)->find();
            $this->assign("info",$info);
            $this->display('add_process');
        }else{
            $this->error('非法请求');
        }
    }
    
    //生产过程详情页
    public function show_process(){
        if (!empty($_GET['pid'])){
            $where = array();
            $where['pid'] = intval($_GET['pid']);
    
        }
        $info = M('base_jgprocessing')->where($where)->select();
        if (empty($info)){
            $this->error('未找到该信息');
        }else{
            $info = $info[0];
        }
        //dump($info);die();
        $this->assign('info',$info);
        $this->display();
    }
    
    //删除加工过程干燥记录
    public function del_process(){
        $m = M('base_jgprocessing');
        $where = array();
        $data['status'] = 0;
        if ($_GET['pid']){
            $where['pid'] = intval($_GET['pid']);
    
            if($m->where($where)->save($data)){
                Log_add(120,'删除加工过程干燥记录',$where['pid']);
                $this->success('删除成功',U('process',array('menu_id'=>$_GET['menu_id'])));
            }
        }elseif(IS_POST){
            $where['pid']=array('in',$_POST['ids']);
            $res = $m ->where($where)->save($data);
            if($res){
                Log_add(120,'删除加工过程干燥记录',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
    }

    
    //销售记录列表
    public function saleList(){
        session_start();
        $where = array();
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
    
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['ccpc'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['pro_name'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['sale_num'] = $key;
                    break;
            }
        }
        $m = M('base_jgfactoryrecord');
        $page=isset($_GET['p'])?$_GET['p']:1;
    
        $where['status'] = 1;
        $qy = $_SESSION['company_id'];
        if (!empty($qy)){
            //dump($where);
            $where['company_id'] = $qy;
            $info = $m->where($where)->order('sid desc')->page($page.',10')->select();
            $company = M('sys_company')->field('name')->where(array('status'=>1,'company_id'=>$qy))->find();
            foreach($info as $k1=>$v1){
                $info[$k1]['company_name'] = $company['name'];
            }
        }else{
            $info = $m->where($where)->order('sid desc')->page($page.',10')->select();
            foreach($info as $k=>$v) {
                $company = M('sys_company')->field('name')->where(array('status' => 1,'company_id'=>$v['company_id']))->select();
                $info[$k]['company_name'] = $company[0]['name'];
            }
        }

//        /*
//                   * by King
//                   * modification time 2016-12-09
//                   * */
//        foreach ($info as $k=>$v) {
//            if ($v['company_id']) {
//                $company_name = M('sys_company')->where(array('company_id' => $v['company_id']))->field('name')->find();
//                $info[$k]['company'] = $company_name['name'];
//            }
//        }
//        foreach($info as $key=>$val){
//            $com_info= M('sys_company')->field('name')->where(array('company_id'=>$val['company_id']))->find();
//            $info[$key]['company']=$com_info['name'];
//        }
        $this->assign('info',$info);
        $count      = $m->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);
        $this->assign('key',$key);
        $this->assign('page',$show);
        $this->assign("menu_id",Detection_menu());
        Log_add(121,'访问销售记录管理列表页面');
        $this->display();
    }
    
    //添加销售记录
    public function add_sale(){
        $base_base_model = M('base_base');
        if (IS_POST){
            $data = I('post.');
            $m = M('base_jgfactoryrecord');
            $data['status'] = 1;
            $data['addtime'] = date('Y-m-d H:i:s');
            if($data['sale_price'] == ''){
                $data['sale_price'] = 0;
            }
            if($_SESSION['company_id'] != 0) {
                $data['company_id'] = $_SESSION['company_id'];
            }
            if($_POST['sid']){
                $res = $m->where(array('sid'=>$_POST['sid']))->save($data);
                if($res){
                    Log_add(124,'修改销售记录',$_POST['sid']);
                    $this->success("修改成功！", U('saleList',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请稍后再试！", U('saleList',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $where_x = array();
                $where_x['ccpc'] = array('like',"%{$data['jgpc']}%");
                $ck = $m->where($where_x)->select();
                $num = count($ck);
                
                $num++;
                $data['ccpc'] = $data['jgpc'].'.'.$num;
                
 //               if($res){
                    //生成溯源码
                    $mes = array();
                    $mes['ccpc'] = $data['ccpc'];
                    $m1 = M('base_jgcgrecord');
                    $where_a = array('jgpc'=>$data['jgpc']);
                    $where_a['status'] = 1;
                    $cspc = $m1->where($where_a)->select();
                    //dump($cspc);exit;
                    if (!empty($cspc)){
                        $where_b = array();
                        $where_b['batch_id'] = $cspc[0]['cspc'];
                        $where_b['is_del'] = 0;
                        $mes['cspc'] = $where_b['batch_id'];
                    }else{
                        $this->error('未查到采购记录');
                    }
                    $m2 = M('base_prrecord');
                    $place_id = $m2->where($where_b)->select();
                    if (!empty($place_id)){
                        $mes['place_id'] = $place_id[0]['place_id'];
                    }else{
                        $this->error('未找到产地编码');
                    }
                    $mes['company_id'] = $_SESSION['company_id'];
                    $mes['status'] = 0;
                    //生成sid
                    $max_sid = $base_base_model->max('sid');
                    $mes['sid'] = $max_sid+1;
                    $mes['addtime'] = date('Y-m-d H:i:s');
//                    p($mes['sid']);
                    $sym = $base_base_model->add($mes);
                    $res = $m->add($data);
                    if(!($sym && $res)){
                        $this->error('生成溯源码失败');
                    }
                    
                    
                    
                    Log_add(123,'新增销售记录、溯源码',$res);
                    $this->success("新增成功！", U('saleList',array('menu_id'=>$_GET['menu_id'])));
                /* }else{
                    $this->error("新增失败，请稍后再试！", U('saleList',array('menu_id'=>$_GET['menu_id'])));
                } */
            }
        }else{
            $com = $_SESSION['company_id'];
            $this->assign('com',$com);
            Log_add(122,'访问新增销售记录页');
            $this->display();
        }
    }

    
    //加工过程干燥记录编辑信息展示
    public function edit_sale(){
        $model = M("base_jgfactoryrecord");
        if($_GET['sid']){
            $where = array();
            $where['sid'] = intval($_GET['sid']);
            $info = $model ->where($where)->find();
            $com = $_SESSION['company_id'];
            $this->assign('com',$com);
            $this->assign("info",$info);
            $this->display('add_sale');
        }else{
            $this->error('非法请求');
        }
    }
    
    //销售记录详情页
    public function show_sale(){
        if (!empty($_GET['sid'])){
            $where = array();
            $where['sid'] = intval($_GET['sid']);
    
        }
        $info = M('base_jgfactoryrecord')->where($where)->select();
        if (empty($info)){
            $this->error('未找到该信息');
        }else{
            $info = $info[0];
        }
        //dump($info);die();
        if($_SESSION['company_id'] != 0){
            $com = $_SESSION['company_id'];
            $company = M('sys_company')->field('name')->where(array('status' => 1, 'company_id' => $com))->find();
            $info['company_name'] = $company['name'];
        }else{
            $company_id3 = M('base_jgcgrecord')->where(array('sid'=>$where['sid']))->find();
            $company_id2 = $company_id3['company_id'];
            $company = M('sys_company')->field('name')->where(array('status' => 1, 'company_id' => $company_id2))->find();
            $info['company_name'] = $company['name'];
        }
        $this->assign('info',$info);
        $this->display();
    }
    
    //删除加工过程干燥记录
    public function del_sale(){
        $m = M('base_jgfactoryrecord');
        $where = array();
        $data['status'] = 0;
        if ($_GET['sid']){
            $where['sid'] = intval($_GET['sid']);
    
            if($m->where($where)->save($data)){
                Log_add(125,'删除销售记录',$where['sid']);
                $this->success('删除成功',U('saleList',array('menu_id'=>$_GET['menu_id'])));
            }
        }elseif(IS_POST){
            $where['sid']=array('in',$_POST['ids']);
            $res = $m ->where($where)->save($data);
            if($res){
                Log_add(125,'删除销售记录',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
    }
    
    /**
     * 环境导入数据
     */
    public function import_health(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
    
            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];
    
            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类
    
            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }
    
            }
            //需要保存的数据，
    
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){
    
                $data['jgpc']=$val['A'];    //加工批次
                $data['cf_address']=$val['B'];    //厂房地址
                $data['tools']=$val['C'];//工具
                $data['base_status']=$val['D'];   //环境情况
                $data['date']=$val['E'];//日期
                $data['addtime'] = date('Y-m-d H:i:s');
                $data['status'] = 1;
                $res = M("base_jghealthcheck")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $username =  $_SESSION['account'];
                $explain = '环境卫生监测';
                Log_add(126,'导入环境卫生监测表');
                import_log($username, $explain,$num_all,$num_suc);
                $this->success("导入成功！", U('healthList',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('healthList',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    //导出环境检查信息
    public function excelout_health(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_jghealthcheck");
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
            
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['jgpc'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['cf_address'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['tools'] = array('like', "%{$key}%");
                    break;
                case $key_type == "4":
                    $where['base_status'] = array('like', "%{$key}%");
                    break;
                case $key_type == "5":
                    $where['date'] = array('like', "%{$key}%");
                    break;
            }
        }
        $where['status']=1;
    
        $data= $model->where($where)->field('hid,status,addtime,company_id',true)->order('hid desc')->select();
    
        Log_add(127,'导出环境检查数据');
    
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="health";
        $headArr=array("加工批次","厂房地址","工具","环境信息","日期");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    
    /**
     * 人员工具消毒导入数据
     */
    public function import_tool(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
    
            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];
    
            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类
    
            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }
    
            }
            //需要保存的数据，
    
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){
    
                $data['jgpc']=$val['A'];    //加工批次
                $data['number']=$val['B'];    //人手
                $data['project']=$val['C'];//项目
                $data['before_work']=$val['D'];   //工作前
                $data['after_work']=$val['E'];//工作后
                $data['measure']=$val['F'];//措施
                $data['checker']=$val['G'];//检查人
                $data['audit']=$val['H'];//审核人
                $data['product_time']=$val['I'];//生产日期
                $data['check_time']=$val['J'];//审核日期
                $data['addtime'] = date('Y-m-d H:i:s');
                $data['status'] = 1;
                $res= M("base_jgtoolcheck")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $username =  $_SESSION['account'];
                $explain = '人员工具消毒';
                Log_add(128,'导入人员工具消毒记录');
                import_log($username, $explain,$num_all,$num_suc);
                $this->success("导入成功！", U('toolList',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('toolList',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    //导出人员工具消毒信息
    public function excelout_tool(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_jgtoolcheck");
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
        
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['jgpc'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['number'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['project'] = array('like', "%{$key}%");
                    break;
                case $key_type == "4":
                    $where['checker'] = array('like', "%{$key}%");
                    break;
                case $key_type == "5":
                    $where['product_time'] = array('like', "%{$key}%");
                    break;
            }
        }
        $where['status']=1;
    
        $data= $model->where($where)->field('tid,status,addtime,company_id',true)->order('tid desc')->select();
    
        Log_add(129,'导出人员工具消毒记录数据');
    
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="tool";
        $headArr=array("加工批次","人手","项目","工作前","工作后","措施","检查人","审核人","生产日期","检查日期");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    
    /**
     * 加工过程干燥记录导入数据
     */
    public function import_process(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
    
            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];
    
            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类
    
            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }
    
            }
            //需要保存的数据，
    
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){
    
                $data['pdate']=$val['A'];    //加工批次
                $data['pname']=$val['B'];    //人手
                $data['ptype']=$val['C'];//项目
                $data['price']=$val['D'];   //工作前
                $data['jgpc']=$val['E'];
                $data['addtime'] = date('Y-m-d H:i:s');
                $data['status'] = 1;
                $res= M("base_jgprocessing")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $username = $_SESSION['account'];
                $explain = '加工过程干燥记录';
                import_log($username, $explain,$num_all,$num_suc);
                Log_add(130,'导入加工过程干燥记录');
                $this->success("导入成功！", U('process',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('process',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    //导出加工过程干燥记录
    public function excelout_process(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_jgprocessing");
    if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
    
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['pdate'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['pname'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['ptype'] = array('like', "%{$key}%");
                    break;
                case $key_type == "4":
                    $where['price'] = array('like', "%{$key}%");
                    break;
            }
        }
        $where['status']=1;
    
        $data= $model->where($where)->field('pid,status,addtime,company_id',true)->order('pid desc')->select();
    
        Log_add(131,'导出加工过程干燥记录');
    
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="process";
        $headArr=array("日期","市场名称","产品类型","价格","加工批次");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    
    /**
     * 销售记录导入数据
     */
    public function import_sale(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
    
            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];
    
            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类
    
            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }
    
            }
            //需要保存的数据，
    
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){
    
                $data['jgpc']=$val['A'];    //加工批次
                $data['pro_name']=$val['B'];    //产品名称
                $data['sale_num']=$val['C'];//销售数量
                $data['price']=$val['D'];   //销售单价
                $data['sale_toarea']=$val['E'];   //销售地区
                $data['sale_date']=$val['F'];   //销售日期
                $data['addtime'] = date('Y-m-d H:i:s');
                $data['status'] = 1;
                $where_x = array();
                $where_x['ccpc'] = array('like',"%{$data['jgpc']}%");
                $ck = M('base_jgfactoryrecord')->where($where_x)->select();
                $num = count($ck);
                
                $num++;
                $data['ccpc'] = $data['jgpc'].'.'.$num;
                
                //生成溯源码
                $mes = array();
                $mes['ccpc'] = $data['ccpc'];
                $m1 = M('base_jgcgrecord');
                $where_a = array('jgpc'=>$data['jgpc']);
                $where_a['status'] = 1;
                $cspc = $m1->where($where_a)->select();
                if (!empty($cspc)){
                    $where_b = array();
                    $where_b['batch_id'] = $cspc[0]['cspc'];
                    $where_b['is_del'] = 0;
                    $mes['cspc'] = $where_b['batch_id'];
                }else{
                    $this->error('未查到采购记录');
                }
                $m2 = M('base_prrecord');
                $place_id = $m2->where($where_b)->select();
                if (!empty($place_id)){
                    $mes['place_id'] = $place_id[0]['place_id'];
                }else{
                    $this->error('未找到产地编码');
                }
                $mes['company_id'] = $_SESSION['company_id'];
                $mes['status'] = 1;
                $mes['addtime'] = date('Y-m-d H:i:s');
                $sym = M('base_base')->add($mes);
                if(!$sym){
                    $this->error('生成溯源码失败');
                }
                
                $res= M("base_jgfactoryrecord")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $username = $_SESSION['account'];
                $explain = '销售记录';
                import_log($username, $explain,$num_all,$num_suc);
                Log_add(132,'导入销售记录');
                $this->success("导入成功！", U('saleList',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('saleList',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    //导出销售记录
    public function excelout_sale(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_jgfactoryrecord");
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
    
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['ccpc'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['pro_name'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['sale_num'] = $key;
                    break;
            }
        }
        $where['status']=1;
    
        $data= $model->where($where)->field('sid,ccpc,status,addtime,company_id',true)->order('sid desc')->select();
    
        Log_add(133,'导出销售记录');
    
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="sale";
        $headArr=array("加工批次","产品名称","销售数量","销售单价","销售区域","销售日期");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
}