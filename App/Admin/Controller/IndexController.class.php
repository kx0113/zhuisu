<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $this->display();
    }
    function maopao($arr){
        //一共是多少趟
        for($i = count($arr)-1; $i>0; $i--){
            $flag = 0;
            //每一趟进行相邻两个数进行比较
            for($j = 0; $j < $i; $j++){
                if($arr[$j]>$arr[$j+1]){
                    $temp = $arr[$j];
                    $arr[$j] = $arr[$j+1];
                    $arr[$j+1] =$temp;
                    $flag = 1;
                }
            }
            if($flag == 0){
                break;
            }
        }
        return $arr;
    }
    /*
     * 首页信息输出
     * by King
     * 2016-12-23
     * */
    public function main(){
        /*
         * 产品种植比重统计
         * */
        $product = M("product");
        //查询所有产品种类
        $pro_type = Tag_list(6,'data');
        foreach($pro_type as $k=>$v){
            //统计产品种类 种植数量
            $count = $product->where(array('pro_type'=>$v['tag_id']))->count();
            $pro_type[$k]['count'] = $count;
        }
        $this->assign("pro_type",$pro_type);
        /*
         * 总面积（公顷）
         * */
        $plant_acreage = $product->Sum('plant_acreage');
        $this->assign('plant_acreage',$plant_acreage);
        /*
         * 企业
         * */
        $sys_company = M("sys_company");
        $company_list = $sys_company->where(array('status'=>1))->field('name,area,addtime')->limit(6)->select();
        $this->assign('company_list',$company_list);

        $this->display();
    }
}