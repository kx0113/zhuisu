<?php
/* 基地准出-溯源打印管理
 * Author: Rocky
 * Date: 2016-12-5
 *  */
namespace Admin\Controller;
use Think\Controller;

class JdprintController extends BaseController {
    //打印管理-生产批次列表
   /*  public function index(){
        $where = array();
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
        
            switch ($key_type){
                case $key_type == "1":
                    $where['p_id'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['cf_address'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['tools'] = array('like', "%{$key}%");
                    break;
            }
        }
        $where['status'] = 1;
        $where['type'] = 1;
        $m = M('base_onprint');
        $page=isset($_GET['p'])?$_GET['p']:1;
        $info = $m->where($where)->order('did desc')->page($page.',10')->select();
        $this->assign('info',$info);
        $count      = $m->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);
        $this->assign('key',$key);
        $this->assign('page',$show);
        $this->assign('cz','cs');
        Log_add(136,'访问打印管理-生产批次列表页面');
        $this->display();
    } */
    
    public function jgpcList(){
        $where = array();
        if (!empty($_GET['key_type'])){
            $key_type = intval($_GET['key_type']);
            $key=I('get.key');
        
            switch ($key_type){
                case $key_type == "1":
                    $where['ccpc'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['pro_name'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['sale_num'] = array('like', "%{$key}%");
                    break;
            }
        }
        $where['status'] = 1;
//        $qy = $_SESSION['company_id'];
//        if (!empty($qy)){
//            $where['company_id'] = $qy;
//            $where_aaa = array();
//            $where_aaa['company_id'] = $qy;
//            $company_name = M('sys_company')->where($where_aaa)->select();
//        }
        
//        if (!empty($company_name)){
//            $com = $company_name[0]['name'];
//        }
        $m = M('base_jgfactoryrecord');
        $page=isset($_GET['p'])?$_GET['p']:1;
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
        $this->assign('info',$info);
        $count      = $m->where($where)->count();
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);
        $this->assign('key',$key);
        $this->assign('page',$show);
        Log_add(137,'访问打印管理-加工批次记录列表页面');
        $this->display();
    }
    //打印详情页
    public function getDetail(){
        if (!empty($_GET['sid'])){
            $where = array();
            $where['sid'] = intval($_GET['sid']);
        }
        $where['status'] = 1;
        //$where['company_id'] = $_SESSION['company_id'];
        $info = M('base_jgfactoryrecord')->field('sid,ccpc,jgpc,pro_name,company_id')->where($where)->select();
        if (empty($info)){
            $this->error('未查到该信息');
        }
        $mes = array();
        $mes['pro_name'] = $info[0]['pro_name'];
        $where_x = array('ccpc'=>$info[0]['ccpc']);
        $where_y = array('company_id'=>$info[0]['company_id']);
        $s_info = M('base_base')->where($where_x)->select();
        if (empty($s_info)){
            $this->error('未找到溯源码');
        }
        $mes['sid'] = $s_info[0]['sid'];
        $c_info = M('sys_company')->where($where_y)->select();
        if (empty($c_info)){
            $this->error('未找到该企业');
        }
        $mes['company_name'] = $c_info[0]['name'];
        $mes['mobile'] = $c_info[0]['mobile'];
        $this->assign('mes',$mes);
        $this->display();
    }
    
    public function makesj(){
        $info = M('base_base')->select();
        foreach ($info as $k => $v){
            if (!empty($info[$k]['jgpc'])){
                $where = array();
                $where['jgpc'] = $info[$k]['jgpc'];
                $res = M('base_jgcgrecord')->field('cspc')->where($where)->select();
                $data = array();
                if (!empty($res)){
                    $data['cspc'] = $res[0]['cspc'];
                    $where1 = array();
                    $where1['batch_id'] = $data['cspc'];
                    $result = M('base_prrecord')->field('place_id')->where($where1)->select();
                    if (!empty($result)){
                        $data['place_id'] = $result[0]['place_id'];
                    }
                }
                $mes = M('base_jgfactoryrecord')->field('ccpc')->where($where)->select();
                if (!empty($mes)){
                    $data['ccpc'] = $mes[0]['ccpc'];
                }
                
                M('base_base')->where($where)->save($data);
            }
        }
        echo 'yes';
    }
    
}