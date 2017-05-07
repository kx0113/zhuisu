<?php
namespace Admin\Controller;
use Think\Controller;

class PlaceController extends BaseController {
    public function index(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        $place_model = M("place");
        $p = $_GET['p']?$_GET['p']:1;
        $place_list = $place_model->page($p.',10')->order('id DESC')->select();//分页
        foreach($place_list as $k=>$v){
            if($v['plant_area']){
                //输出所属地区
                $Tb_data = Tb_data('find_area',$v['plant_area']);
                $place_list[$k]['plant_area'] = $Tb_data[0]['area_name'];
            }
        }
        $count = $place_model->count();//分页
        $page       = new \Think\Page($count,10);//分页
        $this->assign('page',$page->show());//分页
        $this->assign("place_list",$place_list);
        Log_add(39,'访问产地信息列表页面');
        $this->display();
    }
    public function add_place(){
        $model = M("place");
        $place_id = $_REQUEST['id'];
        $place = $model->where(array('id'=>$place_id))->find();
        //地区编码
        $region_code = M("sys_region")->where(array('code'=>$place['region_code']))->field('name')->find();
        $place['region_code'] = $region_code['name'];
        $this->assign("place_list",$place);
        if(IS_POST){
            $data = I("post.");
            $data['place_name'] = trim($_POST['place_name']);
            $data['addtime'] = date('Y-m-d H-i-s',time());
            if($place_id){
                $res = $model->where(array('id'=>$place_id))->save($data);
                if($res){
                    Log_add(40,'修改产地信息成功');
                    $this->success("修改产地信息成功！", U('place/index',array('menu_id'=>$_REQUEST['menu_id'])));
                }else{
                    $this->error("修改产地信息失败，请稍后再试！", U('place/index',array('menu_id'=>$_REQUEST['menu_id'])));
                }
            }else{
                $res = $model->add($data);
                //拼装溯源码
                $datas['place_id'] = '201600'.$res;
                $ress = $model->where(array('id'=>$res))->save($datas);
                if($res){
                    Log_add(41,'新建产地信息成功');
                    $this->success("新增产地信息成功！", U('place/index',array('menu_id'=>$_REQUEST['menu_id'])));
                }else{
                    $this->error("新增产地信息失败，请稍后再试！", U('place/index',array('menu_id'=>$_REQUEST['menu_id'])));
                }
            }
        }else{
            Log_add(42,'访问新建企业信息页面');
            $this->display();
        }
    }
}