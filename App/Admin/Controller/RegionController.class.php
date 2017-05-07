<?php
namespace Admin\Controller;
use Think\Controller;

class RegionController extends BaseController {
    public function index(){
        //判断增删改查权限
//        $this->assign("menu_id",Detection_menu());
        //行政区划信息
        $region_model = M("sys_region");
        $where['status'] = array('in','1,2');
        $region_list = $region_model->where($where)->select();
        $this->assign("region_list",$region_list);
        Log_add(59,'访问行政区划信息列表页面');
        $this->display();
    }
    public function add_region(){
        $parent_id = M("sys_region")->where(array('parent_id'=>7,'status'=>1))->order('id asc')->select();
        $this->assign("parent_id",$parent_id);
        $this->display();
    }
    public function switchs(){
        $tag = $_POST['tag'];
        $sys_region = M("sys_region");
        switch ($tag){
            //行政区域更新
            case $tag == "update":
                $data['name'] =$_POST['name'];
//                $data['icon'] =$_POST['icon'];
//                $data['path'] =$_POST['path'];
                $data['status'] =$_POST['status'];
//                $data['sort'] =$_POST['sort'];
                $data['time'] =time();
                $res = $sys_region->where(array('id'=>$_POST['id']))->data($data)->save();
                break;

            //行政区域新建
            case $tag == "add":
                $data['name'] =$_POST['name'];
//                $data['path'] =$_POST['path'];
                $data['parent_id'] =$_POST['parent_id'];
                $data['status'] =1;
                $region_max_code = $sys_region->max('id');
                $time = substr(time(),2);
                $data['code'] = $region_max_code.$time;
//                if($_POST['icon'] == 1){
//                    $res = $sys_region->where(array('id'=>$_POST['parent_id']))->data(array('icon'=>$_POST['icon']))->save();
//                }else{
//                    $data['icon'] =$_POST['icon'];
//                }
                $res = $sys_region->data($data)->add();

                break;
            //行政区域删除
            case $tag == "del":
                $data['status'] =3;
                $data['time'] =time();
                $res = $sys_region->where(array('id'=>$_POST['id']))->data($data)->save();
                break;
            //删除通用函数
            case $tag == "common_delete":
                $model = $_POST['model'];//model
                $key   = $_POST['key'];//字段名称
                $val   = $_POST['val'];//字段值
                $val_1 = $_POST['val_1'];//备用
                $val_2 = $_POST['val_2'];//备用
                switch ($val_1 || $val_2){
                    case $val_1 == 'no':
                        //同时删除product_id 下面的全部信息
                        M("product")->where(array($key=>$val))->delete();
                        M("product_grow")->where(array($key=>$val))->delete();
                        M("product_manure")->where(array($key=>$val))->delete();
                        M("product_pesticide")->where(array($key=>$val))->delete();
                        M("product_plant")->where(array($key=>$val))->delete();
                        M("product_seed")->where(array($key=>$val))->delete();
                        $json['res'] = 'success';
                        $this->ajaxReturn($json);
                        Log_add(37,'删除商品成功',$model,$val);
                        break;
                    default:
                        if($val_1 == 'group'){
                            //如果删除用户组 并清空ims_sys_user_role_govern有关该role_id下面的权限数据
                            M()->execute("DELETE FROM ims_sys_user_role_govern WHERE role_id = $val");
                        }
                        switch ($val_1 || $val_2){
                            case $val_1 == 38:
                                Log_add(38,'删除企业信息成功',$model,$val);
                                break;
                        }
                        $res   = M($model)->where(array($key=>$val))->delete();
                }
                break;
        }
        if ($res) {
            $json['res'] = 'success';
        } else {
            $json['res'] = 'error';
        }
        $this->ajaxReturn($json);
    }
}