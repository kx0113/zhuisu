<?php
namespace Admin\Controller;
use Think\Controller;

class ContractorController extends BaseController {
    public function index(){
        $contractor_list = M("contractor")->order('id desc')->select();
        $this->assign("contractor_list",$contractor_list);
        Log_add(17,'访问承包人列表页面');
        $this->display();
    }
    //添加承包人
    public function add_contractor(){
        $model = M("contractor");
        if($_GET['id']){
            $contractor = $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("contractor",$contractor);
            if($contractor['plant_area']){
                $this->assign("site_name",Tb_data('site_name',$contractor['plant_area']));
            }
        }
        //肥料品牌
        $this->assign("area",Tag_list(1));
        //生厂商
        $this->assign("manufacturer",Tag_list(28));
        //销售商
        $this->assign("retailer",Tag_list(31));
        if(IS_POST){
            $data = I("post.");
            $data['addtime'] = date('Y-m-d H-i-s',time());
            if($_POST['id']){
                $res = $model->where(array('id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(15,'修改新建承包人成功',$_POST['id']);
                    $this->success("修改成功！", U('contractor/index'));
                }else{
                    $this->error("修改失败，请稍后再试！", U('contractor/index'));
                }
            }else{
                $res = $model->add($data);
                if($res){
                    Log_add(16,'新建承包人成功',$res);
                    $this->success("新增成功！", U('contractor/index'));
                }else{
                    $this->error("新增失败，请稍后再试！", U('contractor/index'));
                }
            }
        }else{
            Log_add(14,'访问新建承包人页面');
            $this->display();
        }
    }
    public function switchs(){
        switch ($_POST['contractor']){
            //删除标签
            case $_POST['contractor'] == "contractor_del":
                $model = M("sys_contractor");
                $contractor_find = $model->where(array('p_id'=>$_POST['contractor_id']))->find();
                if(empty($contractor_find)){
                    $res = $model->where(array('contractor_id'=>$_POST['contractor_id']))->delete();
                }
                $this->js_ajaxReturn($res);
                break;
        }
    }
    //ajax
    function js_ajaxReturn($res){
        if ($res) {
            $json['res'] = 'success';
        } else {
            $json['res'] = 'error';
        }
        $this->ajaxReturn($json);
    }
    /*
     * 二级标签
     * */
    public function contractors_list(){
        $model = M("sys_contractor");
        $p_id = $_GET['p_id'];
        $contractor_find = $model->where(array('contractor_id'=>$p_id))->find();
        $contractor_list = $model->where(array('status'=>1,'p_id'=>$p_id))->select();
        $this->assign("contractor_list",$contractor_list);
        $this->assign("contractor_find",$contractor_find);
        $this->display();
    }
}