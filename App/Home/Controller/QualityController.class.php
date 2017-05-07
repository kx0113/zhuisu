<?php
/**
 * 同江绿色食品管理平台
 * 手机端扫描控制器
 * Date: 2016-12-7
 */
namespace Home\Controller;
use Think\Controller;

class QualityController extends Controller {
    /*
     * 质量追溯手机扫描端首页-企业详情
     */
    public function index(){
        if (empty($_GET['sid'])){
            return false;
        }else{
            $where = array();
            $where['sid'] = trim($_GET['sid']);
            $this->assign('sid',$where['sid']);
        }
        
        $m = M('base_base');
        $where['status'] = 0;
        $info = $m->where($where)->select();
        if (empty($info)){
            return false;
        }else{
            $where_qy = array();
            $where_qy['company_id'] = $info[0]['company_id'];
            $this->assign('id',$info[0]['ccpc']);
        }
        
        $where_qy['status'] = 1;
        $m1 = M('sys_company');
        $com_info = $m1->where($where_qy)->select();
        if (!empty($com_info)){
            $com_info = $com_info[0];
        }else{
            $this->error('未找到您所查找的加工企业');
        }
        $this->assign('com_info',$com_info);
        $this->display();
    }

    /**
     * 检验检测
     */
     public function test_index(){
         $key=trim(I('sid'));//出厂批次-产品批次

         $base_info=M('base_base')->where(array('sid'=>$key))->find();

         $this->assign('baseinfo',$base_info);// 赋值数据集


         if(!empty($base_info['jgpc'])){//加工批次

             $m1 = M('base_jgcgrecord');
             $where_a = array('jgpc'=>$base_info['jgpc']);
             $where_a['status'] = 1;
             $cspc = $m1->where($where_a)->select();
             if (!empty($cspc)){
                 $cspc = $cspc[0]['cspc'];//获取一条采收批次
             }
         }

         if(!empty($base_info['cspc'])){//采收
             $cspc = $base_info['cspc'];//获取一条采收批次
         }

         if(!empty($cspc)){
             $where['product_batch'] = $cspc;
         }else{
             $where['product_batch'] = 'xxxxxxxx';
         }

         $where['is_del']=0;

         //定量检测
         $list_ration = M("test_ration")->where($where)->order('id desc')->select();
         $this->assign('list_ration',$list_ration);// 赋值数据集

         //农残速测
         $list_pesticide = M("test_pesticide")->where($where)->order('id desc')->select();
         $this->assign('list_pesticide',$list_pesticide);// 赋值数据集

        //PH原始测定记录数据
         $list_ph = M("test_ph")->where($where)->order('id desc')->select();
         $this->assign('list_ph',$list_ph);// 赋值数据集

         //滴定法测定原始记录管理
         $list_titration = M("test_titration")->where($where)->order('id desc')->select();
         $this->assign('list_titration',$list_titration);// 赋值数据集

        //光度法测定记录管理
         $list_luminosity = M("test_luminosity")->where($where)->order('id desc')->select();
         $this->assign('list_luminosity',$list_luminosity);// 赋值数据集

         Log_add(452,'访问质量追溯页面');
         $this->display();
     }
     /*
      *  加工过程追溯详情
      *  Rocky
      *  2016-12-7
      *  */
     public function process(){
         $where = array();
         if (!empty($_GET['sid'])){
             $sid = trim(I('get.sid'));
             $this->assign('sid',$sid);
         }
         //查询加工信息
         if (!empty($_GET['id'])){
             $where['ccpc'] = trim(I('get.id'));
         }else{
             $this->error('非法请求');
         }
         $m = M('base_jgfactoryrecord');
         $where['status'] = 1;
         $sale_info = $m->where($where)->select();
         if (!empty($sale_info)){
             $sale_info = $sale_info[0];
         }else{
             $this->error('您所查找的加工信息已不存在');
         }
         $this->assign('sale_info',$sale_info);
         //查询企业信息
         $where_qy = array();
         $where_qy['company_id'] = $sale_info['company_id'];
         $where_qy['status'] = 1;
         $m1 = M('sys_company');
         $com_info = $m1->where($where_qy)->select();
         if (!empty($com_info)){
             $com_info = $com_info[0];
         }else{
             $this->error('未找到您所查找的加工企业');
         }
         $this->assign('com_info',$com_info);
         //查询采购信息
         $where_cg = array();
         $where_cg['jgpc'] = $sale_info['jgpc'];
         $where_cg['status'] = 1;
         $m2 = M('base_jgcgrecord');
         $cg_info = $m2->where($where_cg)->select();
         if (!empty($cg_info)){
             $cg_info = $cg_info[0];
         }else{
             $this->error('未找到您所查找的采购信息');
         }
         $this->assign('cg_info',$cg_info);
         //查询环境卫生检查信息
         $where_hj = array();
         $where_hj['jgpc'] = $sale_info['jgpc'];
         $where_hj['status'] = 1;
         $m3 = M('base_jghealthcheck');
         $hj_info = $m3->where($where_hj)->select();
         /* if (empty($hj_info)){
          $this->error('未找到您所查找的环境检查信息');
         } */
         //dump($hj_info);die();
         $this->assign('hj_info',$hj_info);
         //查询人员工具消毒记录信息
         $where_tl = array();
         $where_tl['jgpc'] = $sale_info['jgpc'];
         $where_tl['status'] = 1;
         $m4 = M('base_jgtoolcheck');
         $tl_info = $m4->where($where_tl)->select();
         /* if (empty($tl_info)){
          $this->error('未找到您所查找的消毒记录信息');
         } */
         //dump($tl_info);die();
         $this->assign('tl_info',$tl_info);
         //查询加工干燥记录信息
         $where_pr = array();
         $where_pr['jgpc'] = $sale_info['jgpc'];
         $where_pr['status'] = 1;
         $m5 = M('base_jgprocessing');
         $pr_info = $m5->where($where_pr)->select();
         /* if (empty($tl_info)){
          $this->error('未找到您所查找的消毒记录信息');
         } */
         $this->assign('pr_info',$pr_info);
         Log_add(146,'访问质量追溯加工过程详情页面');
         $this->display();
     }
     
     
    public function environment(){
        $where = array();
        if (!empty($_GET['sid'])){
            $sid = trim(I('get.sid'));
            $this->assign('sid',$sid);
        }
        //查询环境信息
        $info = M('base_base')->where(array('sid'=>$sid))->find();
            if(!empty($info['jgpc'])){
                $jgpc = M('base_jgcgrecord')->field('cspc')->where(array('jgpc'=>$info['jgpc']))->order('cid asc')->select();
                foreach($jgpc as $k=>$v){
                    $cspc = '';
                    $cspc = $v['cspc'];
                }
            }
            if(!empty($info['cspc'])){
                $cspc = $info['cspc'];
            }
        $where['cspc'] = $cspc;
        if(empty($cspc)){
            $where['cspc'] = 'sdf1265asdf655asdf';
        }
        
        $place_id = M('base_prrecord')->field('place_id')->where(array('batch_id'=>$where['cspc']))->find();
        $where['place_id'] = $place_id['place_id'];
        $air = M('base_enair');
        $soil = M('base_ensoil');
        $residue = M('base_enresidue');
        $irrigation = M('base_enirrigation');
        $where['is_del'] = 0;
        unset($where['cspc']);

        $air_info = $air->where($where)->order('id desc')->limit(1)->select();
        $soil_info = $soil->where($where)->order('id desc')->limit(1)->select();
        $residue_info = $residue->where($where)->order('id desc')->limit(1)->select();
        $irrigation_info = $irrigation->where($where)->order('id desc')->limit(1)->select();
//        dump($air_info);
//        dump($soil_info);
//        dump($residue_info);
//        dump($irrigation_info);
//        if(empty($air_info)){
//            $this->error('产地空气质量数据为空');
//        }
//        if(empty($soil_info)){
//            $this->error('产地土壤质量数据为空');
//        }
//        if(empty($residue_info)){
//            $this->error('产地农药残留量数据为空');
//        }
//        if(empty($irrigation_info)){
//            $this->error('产地灌溉水质量数据为空');
//        }
        Log_add(373,'访问质量追溯环境信息详情手机页面');
        $this->assign('air_info',$air_info);
        $this->assign('soil_info',$soil_info);
        $this->assign('residue_info',$residue_info);
        $this->assign('irrigation_info',$irrigation_info);
        $this->display();
    }

    public function product(){
          $where = array();
        if (!empty($_GET['sid'])){
            $sid = trim(I('get.sid'));
            $this->assign('sid',$sid);
        }
        //查询环境信息
        $info = M('base_base')->where(array('sid'=>$sid))->find();
            if(!empty($info['jgpc'])){
                $jgpc = M('base_jgcgrecord')->field('cspc')->where(array('jgpc'=>$info['jgpc']))->order('cid asc')->select();
                foreach($jgpc as $k=>$v){
                    $cspc = '';
                    $cspc = $v['cspc'];
                }
            }
            if(!empty($info['cspc'])){
                $cspc = $info['cspc'];
            }
        $where['cspc'] = $cspc;
        if(empty($cspc)){
            $where['cspc'] = 'sdf1265asdf655asdf';
        }
        
        $place_id = M('base_prrecord')->field('place_id')->where(array('batch_id'=>$where['cspc']))->find();
        $where['place_id'] = $place_id['place_id'];
        $base = M('base_prbase');
        $data = M('base_prdata');
        $druguse = M('base_prdruguse');
        $farmwork = M('base_prfarmwork');
        $prfertilizer = M('base_prfertilizer');
        $record = M('base_prrecord');

        $where['is_del'] = 0;
        unset($where['cspc']);
        $base_info = $base->where($where)->order('id desc')->limit(1)->select();
        $data_info = $data->where($where)->order('id desc')->limit(1)->select();
        $druguse_info = $druguse->where($where)->order('id desc')->limit(1)->select();
        $fertilizer_info = $prfertilizer->where($where)->order('id desc')->limit(1)->select();
        $farmwork_info = $farmwork->where($where)->order('id desc')->limit(1)->select();
        $res = M('base_base')->field('cspc')->where(array('sid'=>$sid))->find();
        $cspc = $res['cspc'];
        $record_info = $record->where(array('batch_id'=>$cspc,'is_del'=>0))->order('id desc')->limit(1)->select();
        //dump($base_info);
        //dump($druguse_info);
        //dump($fertilizer_info);
        //dump($fertilizer_info);
//        dump($air_info);
//        dump($soil_info);
//        dump($residue_info);
//        dump($irrigation_info);
//        if(empty($base_info)){
//            $this->error('生产基地基本情况数据为空');
//        }
//        if(empty($data_info)){
//            $this->error('产地生产资料购买数据为空');
//        }
//        if(empty($druguse_info)){
//            $this->error('产地农药使用情况数据为空');
//        }
//        if(empty($fertilizer_info)){
//            $this->error('产地肥料使用情况数据为空');
//        }
//        if(empty($farmwork_info)){
//            $this->error('产地田间农事操作数据为空');
//        }
        Log_add(374,'访问质量生产过程信息详情手机页面');
        $this->assign('base_info',$base_info);
        $this->assign('data_info',$data_info);
        $this->assign('druguse_info',$druguse_info);
        $this->assign('fertilizer_info',$fertilizer_info);
        $this->assign('farmwork_info',$farmwork_info);
        $this->assign('record_info',$record_info);
        $this->display();
    }


     //二维码模块
     public function ewm(){
         if (isset($_GET['sid'])){
             $sid = trim($_GET['sid']);
         }
         $host = $_SERVER['HTTP_HOST'];
         $link = U('index');
         $ewpic = 'http://'.$host.$link.'?sid='.$sid;
         include 'Qrcode.class.php';
         \QRcode::png($ewpic);
     }
}