<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        exit(404);
        if(IS_POST){
            $product = M("product")->where(array('id'=>$_POST['id']))->find();
           if($product){
               if($product['pro_name'] && $product['pro_type'] && $product['plant_area'] && $product['site_name']){
                   $this->redirect('Index/info',array('id'=>$_POST['id']));
               }else{
                   $this->error("数据不全，暂时无法展示！");
                   exit;
               }
           }else{
               Log_add(1,'溯源码输入错误',$_POST['id']);
               $this->error("未找到该溯源码相关数据，请核实！");
               exit;
           }
        }else{
            $this->display();
        }
    }
    //查询结果函数
    public function info(){
        exit(404);
        if (isset($_GET['id'])){
            $where['id'] = trim($_GET['id']);
            Log_add(1,'溯源码查询',$_GET['id']);
        }else{
            $this->error("参数错误！");
            exit;
        }
        /*
        * 基础信息
        * */
        $model_product = M("product");
        $model_tag = M("sys_tag");
        //主表信息
        $product = $model_product->where(array('id'=>$_GET['id']))->find();
        //产品类别
        $product_tag = $model_tag->where(array('tag_id'=>$product['pro_type']))->field('name as pro_type')->find();
        $this->assign('product_tag',$product_tag);
        //区域信息
        if($product['plant_area']){
            $plant_area = Tb_data('find_area',$product['plant_area']);
            $plant_areas['plant_area'] = $plant_area[0]['area_name'];
            $this->assign('plant_areas',$plant_areas);
        }
        //地块名称
        if($product['site_name']){
            $site_name = Tb_data('find_area',$product['site_name']);
            $site_names['site_name'] = $site_name[0]['area_name'];
            $this->assign('site_names',$site_names);
        }
        //产品类别
        $product_plant_year = $model_tag->where(array('tag_id'=>$product['plant_year']))->field('name as plant_year')->find();
        //承包人
        $contractor = M("contractor")->where(array('id'=>$product['contractor']))->field('name as contractor_name,mobile as contractor_mobile')->find();
        $this->assign('contractor',$contractor);


        /*
         * 产品简介
         * */
        //证书
        $product['certificate'] = explode(",", $product['certificate']);
//        $product = array_merge($product,$product_tag,$plant_areas,$site_names,$product_plant_year,$contractor);
        $this->assign('product',$product);

        /*
        * 种子信息
        * */
        //种子名称
        $seed = M("product_seed")->where(array("product_id"=>$product['product_id']))->find();
        //种子品牌
        $this->assign('brand',stride_mysql("brand",$seed['brand']));
        //生产商
        $this->assign('manufacturer',stride_mysql("manufacturer",$seed['manufacturer']));
        //销售商
        $this->assign('retailer',stride_mysql("retailer",$seed['retailer']));
        $this->assign('seed',$seed);
        /*
        * 植物生长图片
        * */
        $product_plant_list = M("product_plant")->where(array('product_id'=>$product['product_id']))->LIMIT(1)->select();
        foreach($product_plant_list as $k=>$v){
            //设备信息
            $Model = new \Think\Model();
            if($v['device_id']){
                $Tb_data_device = $Model->query("SELECT * FROM tb_device WHERE id = $v[device_id]");
                $area_id = $Tb_data_device[0]['area_id'];
            }
            //查询区域
            if($area_id){
                $Tb_data_area = $Model->query("SELECT area_name FROM tb_area WHERE id = $area_id");
                $product_plant_list[$k]['device_name'] = $Tb_data_device[0]['device_name'];
                $product_plant_list[$k]['device_num'] = $Tb_data_device[0]['device_code'];
                $product_plant_list[$k]['device_type'] = $Tb_data_device[0]['device_type'];
                $product_plant_list[$k]['area'] = $Tb_data_area[0]['area_name'];
            }
            //承包人
            $contractor = M("contractor")->where(array('id'=>$product['contractor']))->field('name')->find();
            $product_plant_list[$k]['contractor_name'] = $contractor['name'];
            //时期
            if($v['stage']){
                $stage = M("sys_stage")->where(array('stage_id'=>$v['stage']))->field('stage_name')->find();
            }
        }
        $product_plant_pic = M("product_plant")->where(array('product_id'=>$product['product_id'],'device_id'=>99999))->field('pic')->select();
        $this->assign("stage",$stage);
        $this->assign("product_plant_list",$product_plant_list);
        $this->assign("product_plant_pic",$product_plant_pic);

        /*
        * 肥料使用
        * */
        $product_manure_list = M("product_manure")->where(array('product_id'=>$product['product_id']))->select();
        foreach($product_manure_list as $k=>$v){
            $tag_brand = $model_tag->where(array('tag_id'=>$v['brand']))->field('name')->find();
            $product_manure_list[$k]['brand'] = $tag_brand['name'];
            $tag_kind = $model_tag->where(array('tag_id'=>$v['kind']))->field('name')->find();
            $product_manure_list[$k]['kind'] = $tag_kind['name'];
            $sys_stage = M("sys_stage")->where(array('stage_id'=>$v['period']))->field('stage_name')->find();
            $product_manure_list[$k]['period'] = $sys_stage['stage_name'];
            $tag_manufacturer = $model_tag->where(array('tag_id'=>$v['retailer']))->field('name')->find();
            $product_manure_list[$k]['retailer'] = $tag_manufacturer['name'];
        }
        $this->assign("product_manure_list",$product_manure_list);

        /*
        * 农药使用
        * */
        $product_pesticide_list = M("product_pesticide")->where(array('product_id'=>$product['product_id']))->select();
        foreach($product_pesticide_list as $k=>$v){
            $tag_brand = M("sys_tag")->where(array('tag_id'=>$v['brand']))->field('name')->find();
            $product_pesticide_list[$k]['brand'] = $tag_brand['name'];
            $sys_stage = M("sys_stage")->where(array('stage_id'=>$v['period']))->field('stage_name')->find();
            $product_pesticide_list[$k]['period'] = $sys_stage['stage_name'];
            $tag_manufacturer = $model_tag->where(array('tag_id'=>$v['retailer']))->field('name')->find();
            $product_pesticide_list[$k]['retailer'] = $tag_manufacturer['name'];
        }
        $this->assign("product_pesticide_list",$product_pesticide_list);

        /*
        * 生长环境数据
        * */
//        $info = M('product')->where($where)->select();
//        if (!empty($info)){
//            $info = $info[0];
//        }
//        $id = $info['product_id'];
//        $wherex = array('product_id'=>$id);
//        $statics_from = M('product_grow')->where($wherex)->select();
//        if (!empty($statics_from)){
//            $startt = $statics_from[0]['start_time'];
//            $endd = $statics_from[0]['end_time'];
//            $device = $statics_from[0]['device_id'];
//        }
//        if (empty($startt)){
//            $startt = date('Y-m-01',strtotime('-1 month'));
//        }
//        if (empty($endd)){
//            $firstday = date('Y-m-01');
//            $endd = date('Y-m-d',strtotime("$firstday +1 month -1 day"));
//        }
//        //dump($endd);die();
//        $m_statics = M();
//        $sheet = $m_statics->
//        query('SELECT param_id,SUBSTR(date,1,7) as shiqi,avg(avg) as pingjun,avg(max) as zuigao,avg(min) as zuidi FROM tb_statis WHERE device_id='.'100'." AND date>='".$startt.
//            "' AND date<='".$endd."' AND param_id IN (5,6,7,8,9,18) GROUP BY param_id,substr(date,1,7) ORDER BY date asc");
//        //dump($sheet);die();
//        $hjwd = array();
//        $hjsd = array();
//        $trwd1 = array();
//        $trwd2 = array();
//        $trsd1 = array();
//        $trsd2 = array();
//        foreach ($sheet as $key=>$item){
//            if ($item['param_id'] == 7){
//                $hjwd = array_merge_recursive($hjwd, $sheet[$key]);
//            }
//            if ($item['param_id'] == 18){
//                $hjsd = array_merge_recursive($hjsd, $sheet[$key]);
//            }
//            if ($item['param_id'] == 5){
//                $trwd1 = array_merge_recursive($trwd1, $sheet[$key]);
//            }
//            if ($item['param_id'] == 6){
//                $trwd2 = array_merge_recursive($trwd2, $sheet[$key]);
//            }
//            if ($item['param_id'] == 8){
//                $trsd1 = array_merge_recursive($trsd1, $sheet[$key]);
//            }
//            if ($item['param_id'] == 9){
//                $trsd2 = array_merge_recursive($trsd2, $sheet[$key]);
//            }
//        }
//        //dump($trsd);die();
//        $this->assign('hjwd',$hjwd);
//        $this->assign('hjsd',$hjsd);
//        $this->assign('trwd1',$trwd1);
//        $this->assign('trwd2',$trwd2);
//        $this->assign('trsd1',$trsd1);
//        $this->assign('trsd2',$trsd2);
//        $this->assign('id',$where['id']);
        $this->display(); 
    }
    //二维码模块
    public function ewm(){
        if (isset($_GET['id'])){
            $id = trim($_GET['id']);
        }
        $host = $_SERVER['HTTP_HOST'];
        $link = U('info');
        $ewpic = 'http://'.$host.$link.'?product_id='.$id;
        include 'Qrcode.class.php';
        \QRcode::png($ewpic);
    }
}