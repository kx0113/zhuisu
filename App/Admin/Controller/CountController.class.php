<?php
namespace Admin\Controller;
use Think\Controller;

class CountController extends BaseController {
    public function index(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        //基地统计
        $region_count = M("sys_region")->where(array('status'=>1))->count();
        $this->assign("region_count",$region_count);


        //企业统计
        $company_count = M("sys_company")->where(array('status'=>1))->count();
        $this->assign("company_count",$company_count);


        //溯源码统计
        $base_base_count = M("base_base")->where(array('status'=>0))->count();
        $this->assign("base_base_count",$base_base_count);


        //产品统计
        $commodity_where['parent_id'] = array('GT','0');
        $commodity_count = M("commodity")->where(array('status'=>1))->where($commodity_where)->count();
        $this->assign("commodity_count",$commodity_count);

        $this->display();
    }
}