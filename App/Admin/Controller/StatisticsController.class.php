<?php
namespace Admin\Controller;
use Think\Controller;

class StatisticsController extends BaseController {
    /*
     * 农药使用情况统计报表
     * @plant_area 种植区域
     * @pro_type 产品种类
     * @plant_year 种植年限
     * 2016-11-17
     *   */
    public function index(){
        $model = M('product');
        $this->assign('pro_type',Tag_list(6));
        //地区
        $this->assign('areas',Tb_data('area'));
        //$year = M('product')->field('plant_year')->group('plant_year')->select();
        $this->assign('year',Tag_list(16));
        if (IS_POST){
            if (isset($_POST['plant_area'])){
                $where1 = array();
                $where1['plant_area'] = intval($_POST['plant_area']); 
            }
            if (isset($_POST['pro_type'])){
                $where2 = array();
                $where2['pro_type'] = intval($_POST['pro_type']);
            }
            if (isset($_POST['plant_year'])){
                $where3 = array();
                $where3['plant_year'] = intval($_POST['plant_year']);
            }
            //根据提交的表单找到对应的所有产品
            $info = $model->where($where1)->where($where2)->where($where3)->select();
            if (!empty($info)){
                $ids = array();
                foreach ($info as $k=>$v){
                    $ids[$k] = $v['product_id'];
                }
            }else{
                $this->error('暂无数据');
            }
            $where['product_id'] = array('in',$ids);
            //查询农药表对应以上商品的记录
            $res = M('product_pesticide')->field('period,sum(number)')->where($where)->group('period')->order('period asc')->select();
            
            //查询该类别下的所属时期
            $where4 = array();
            $where4['tag_id'] = $where2['pro_type'];
            $period = M('sys_stage')->field('stage_id,stage_name')->where($where4)->order('stage_id asc')->select();
            
            //对应时期和农药记录
            $fun = array();
            foreach ($period as $k=>$v){
                $i = $k;
                foreach ($res as $key=>$val){
                    if ($val['period'] == $v['stage_id']){
                        $fun[$i] = array_merge_recursive($period[$k],$res[$key]);
                    }
                }
                if ($fun[$i] == ''){
                    $fun[$i]['stage_name'] = $v['stage_name'];
                    $fun[$i]['sum(number)'] = 0;
                }
            }
            //dump($fun);die();
            $this->assign('qy',$where1['plant_area']);
            $this->assign('lb',$where2['pro_type']);
            $this->assign('nf',$where3['plant_year']);
            $this->assign('fun',$fun);
            $this->display();
        }else{
            $mes1 = M('sys_tag')->where(array('p_id'=>6))->order('tag_id asc')->limit(1)->select();
            if (!empty($mes1)){
                $where2['pro_type'] = $mes1[0]['tag_id'];
            }
            $mes2 = M('sys_tag')->where(array('p_id'=>16))->order('tag_id desc')->limit(1)->select();
            if (!empty($mes2)){
                 $where3['plant_year'] = $mes2[0]['tag_id'];
            }
            //$where3['plant_year'] = intval($_POST['plant_year']);
            $info = $model->where($where2)->where($where3)->select();
            if (!empty($info)){
                $ids = array();
                foreach ($info as $k=>$v){
                    $ids[$k] = $v['product_id'];
                }
            /* else{
                $this->error('暂无数据');
            } */
            $where['product_id'] = array('in',$ids);
            //查询农药表对应以上商品的记录
            $res = M('product_pesticide')->field('period,sum(number)')->where($where)->group('period')->order('period asc')->select();
            }else{
                $res = array();
            }
            //查询该类别下的所属时期
            $where4 = array();
            $where4['tag_id'] = $where2['pro_type'];
            $period = M('sys_stage')->field('stage_id,stage_name')->where($where4)->order('stage_id asc')->select();
            
            //对应时期和农药记录
            $fun = array();
            foreach ($period as $k=>$v){
                $i = $k;
                foreach ($res as $key=>$val){
                    if ($val['period'] == $v['stage_id']){
                        $fun[$i] = array_merge_recursive($period[$k],$res[$key]);
                    }
                }
                if ($fun[$i] == ''){
                    $fun[$i]['stage_name'] = $v['stage_name'];
                    $fun[$i]['sum(number)'] = 0;
                }
            }
//             /dump($fun);die();
            $this->assign('fun',$fun);

            $this->display();
        }
        
        
    }
    
    /*
     * 化肥使用情况统计报表
     * @plant_area 种植区域
     * @pro_type 产品种类
     * @plant_year 种植年限
     * @manure 肥料种类
     * 2016-11-17
     *   */
    public function feiliao(){
        $model = M('product');
        $manure = M('sys_tag')->where(array('p_id'=>38))->select();
        $this->assign('manure',$manure);
        $this->assign('pro_type',Tag_list(6));
        //地区
        $this->assign('areas',Tb_data('area'));
        //$year = M('product')->field('plant_year')->group('plant_year')->select();
        $this->assign('year',Tag_list(16));
        //dump(Tag_list(58));die();
        if (IS_POST){
            if (isset($_POST['plant_area'])){
                $where1 = array();
                $where1['plant_area'] = intval($_POST['plant_area']);
            }
            if (isset($_POST['pro_type'])){
                $where2 = array();
                $where2['pro_type'] = intval($_POST['pro_type']);
            }
            if (isset($_POST['plant_year'])){
                $where3 = array();
                $where3['plant_year'] = intval($_POST['plant_year']);
                
            }
            if (isset($_POST['manure'])){
                $where5 = array();
                $where5['kind'] = intval($_POST['manure']);
            }
            
            //根据提交的表单找到对应的所有产品
            $info = $model->where($where1)->where($where2)->where($where3)->select();
            //dump($where3);die();
            if (!empty($info)){
                $ids = array();
                foreach ($info as $k=>$v){
                    $ids[$k] = $v['product_id'];
                }
            }else{
                $this->error('暂无数据');
            }
            $where['product_id'] = array('in',$ids);
            //查询农药表对应以上商品的记录
            $res = M('product_manure')->field('period,sum(number)')->where($where)->where($where5)->group('period')->order('period asc')->select();
    
            //查询该类别下的所属时期
            $where4 = array();
            $where4['tag_id'] = $where2['pro_type'];
            $period = M('sys_stage')->field('stage_id,stage_name')->where($where4)->order('stage_id asc')->select();
    
            //对应时期和农药记录
            $fun = array();
            foreach ($period as $k=>$v){
                $i = $k;
                foreach ($res as $key=>$val){
                    if ($val['period'] == $v['stage_id']){
                        $fun[$i] = array_merge_recursive($period[$k],$res[$key]);
                    }
                }
                if ($fun[$i] == ''){
                    $fun[$i]['stage_name'] = $v['stage_name'];
                    $fun[$i]['sum(number)'] = 0;
                }
            }
            //dump($fun);die();
            $this->assign('qy',$where1['plant_area']);
            $this->assign('lb',$where2['pro_type']);
            $this->assign('nf',$where3['plant_year']);
            $this->assign('fl',$where5['kind']);
            $this->assign('fun',$fun);
            $this->display();
        }else{
            
            $mes1 = M('sys_tag')->where(array('p_id'=>6))->order('tag_id asc')->limit(1)->select();
            if (!empty($mes1)){
                $where2['pro_type'] = $mes1[0]['tag_id'];
            }
            $mes2 = M('sys_tag')->where(array('p_id'=>16))->order('tag_id desc')->limit(1)->select();
            if (!empty($mes2)){
                $where3['plant_year'] = $mes2[0]['tag_id'];
            }
            $info = $model->where($where2)->where($where3)->select();
            if (!empty($info)){
                $ids = array();
                foreach ($info as $k=>$v){
                    $ids[$k] = $v['product_id'];
                }
                
                $where['product_id'] = array('in',$ids);
                //查询农药表对应以上商品的记录
                $res = M('product_manure')->field('period,sum(number)')->where($where)->where($where5)->group('period')->order('period asc')->select();
                
            }else{
               $res = array();                 
            }
                        
            //查询该类别下的所属时期
            $where4 = array();
            $where4['tag_id'] = $where2['pro_type'];
            $period = M('sys_stage')->field('stage_id,stage_name')->where($where4)->order('stage_id asc')->select();
            
            //对应时期和农药记录
            $fun = array();
            foreach ($period as $k=>$v){
                $i = $k;
                foreach ($res as $key=>$val){
                    if ($val['period'] == $v['stage_id']){
                        $fun[$i] = array_merge_recursive($period[$k],$res[$key]);
                    }
                }
                if ($fun[$i] == ''){
                    $fun[$i]['stage_name'] = $v['stage_name'];
                    $fun[$i]['sum(number)'] = 0;
                }
            }
            //dump($fun);die();
            $this->assign('fun',$fun);
            
            $this->display();
        }
    
    
    }
    
    /*
     * 种植情况统计报表
     * @plant_area 种植区域
     * @pro_type 产品种类
     * 2016-11-17
     *   */
    public function zhongzhi(){
        $model = M('product');
        $this->assign('pro_type',Tag_list(6));
        //地区
        $this->assign('areas',Tb_data('area'));
        if (IS_POST){
            if (isset($_POST['plant_area'])){
                $where1 = array();
                $where1['plant_area'] = intval($_POST['plant_area']);
            }
            if (isset($_POST['pro_type'])){
                $where2 = array();
                $where2['pro_type'] = intval($_POST['pro_type']);
            }
            //根据提交的表单找到对应的所有产品
            $info = $model->field('plant_year,sum(plant_acreage)')->where($where1)->where($where2)->group('plant_year')->order('plant_year asc')->select();
    
            //查询年份
            $year = M('sys_tag')->field('tag_id,name')->where(array('p_id'=>16))->order('tag_id asc')->select(); 
            
            //对应年份和记录
            $fun = array();
            foreach ($year as $k=>$v){
                $i = $k;
                foreach ($info as $key=>$val){
                    if ($val['plant_year'] == $v['tag_id']){
                        $fun[$i] = array_merge_recursive($year[$k],$info[$key]);
                    }
                }
                if ($fun[$i] == ''){
                    $fun[$i]['name'] = $v['name'];
                    $fun[$i]['sum(plant_acreage)'] = 0;
                }
            }
            //dump($fun);die();
            $this->assign('qy',$where1['plant_area']);
            $this->assign('lb',$where2['pro_type']);
            $this->assign('fun',$fun);
            $this->display();
        }else{
            $mes1 = M('sys_tag')->where(array('p_id'=>6))->order('tag_id asc')->limit(1)->select();
            if (!empty($mes1)){
                $where2['pro_type'] = $mes1[0]['tag_id'];
            }
            $info = $model->field('plant_year,sum(plant_acreage)')->where($where2)->group('plant_year')->order('plant_year asc')->select();
            
            //查询年份
            $year = M('sys_tag')->field('tag_id,name')->where(array('p_id'=>16))->order('tag_id asc')->select();
            
            //对应年份和记录
            $fun = array();
            foreach ($year as $k=>$v){
                $i = $k;
                foreach ($info as $key=>$val){
                    if ($val['plant_year'] == $v['tag_id']){
                        $fun[$i] = array_merge_recursive($year[$k],$info[$key]);
                    }
                }
                if ($fun[$i] == ''){
                    $fun[$i]['name'] = $v['name'];
                    $fun[$i]['sum(plant_acreage)'] = 0;
                }
            }
            //dump($fun);die();
            $this->assign('fun',$fun);
            
            $this->display();
        }
    }
    
    
}