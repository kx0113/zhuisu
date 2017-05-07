<?php
namespace Admin\Controller;
use Think\Controller;

class StageController extends BaseController {
    public function Stage_list(){
        $tag = M("sys_tag")->where(array('tag_id'=>$_GET['tag_id']))->find();
        $stage_list = M("sys_stage")->where(array('tag_id'=>$_GET['tag_id']))->select();
        $this->assign("tag_list",$stage_list);
        $this->assign("tag",$tag);
        $this->display();
    }
    public function add_stage(){
        $model = M("sys_stage");
        if($_GET['stage_id']){
            $stage_find = $model->where(array('stage_id'=>$_GET['stage_id']))->find();
            $this->assign("stage_find",$stage_find);
        }
        if(IS_POST){
            $data = I("post.");
            $tag_id = $_GET['tag_id'];
            $data['tag_id'] = $tag_id;
            $data['addtime'] = date('Y-m-d H-i-s',time());
            if($_POST['stage_id']){
                $data['stage_name'] = trim($_POST['stage_name']);
                $res = $model->where(array("stage_id"=>$_POST['stage_id']))->save($data);
                if($res){
                    $this->success("修改阶段信息成功！", U('Stage/Stage_list',array('tag_id'=>$tag_id,'p_id'=>$_GET['p_id'])));
                }else{
                    $this->error("修改阶段信息失败，请稍后再试！", U('Stage/Stage_list',array('tag_id'=>$tag_id,'p_id'=>$_GET['p_id'])));
                }
            }else{
                $data['stage_name'] = trim($_POST['stage_name']);
                $stage_num = $model->where(array('tag_id'=>$tag_id))->count();
                $data['number'] = $stage_num+1;
                $res = $model->add($data);
                if($res){
                    $this->success("新增阶段信息成功！", U('Stage/Stage_list',array('tag_id'=>$tag_id)));
                }else{
                    $this->error("新增阶段信息失败，请稍后再试！", U('Stage/Stage_list',array('tag_id'=>$tag_id)));
                }
            }
        }else{
            $this->display();
        }
    }
    public function edit_tag(){
        $p_id = $_GET['p_id'];
        if($_GET['tag_id']){
            $tag_list = M("sys_tag")->where(array('tag_id'=>$_GET['tag_id']))->find();
            $this->assign("tag_list",$tag_list);
        }
        if(IS_POST){
            $model = M("sys_tag");
            $data = I("post.");
            $data['status'] = 1;
            $data['name'] = trim($_POST['name']);
            $data['addtime'] = date('Y-m-d',time());
            $res = $model->where(array('tag_id'=>$data['tag_id']))->save($data);
            if($p_id == '0'){
                if($res){
                    $this->success("修改标签成功！", U('Tag/tag_list',array('p_id'=>$p_id)));
                }else{
                    $this->error("修改标签失败，请稍后再试！", U('Tag/tag_list',array('p_id'=>$p_id)));
                }
            }else{
                if($res){
                    $this->success("修改标签成功！", U('Tag/tags_list',array('p_id'=>$p_id)));
                }else{
                    $this->error("修改标签失败，请稍后再试！", U('Tag/tags_list',array('p_id'=>$p_id)));
                }
            }
        }else{
            $this->display("Tag_add_tag");
        }
    }
    public function switchs(){
        switch ($_POST['tag']){
            //删除标签
            case $_POST['tag'] == "tag_del":
                $model = M("sys_tag");
                $tag_find = $model->where(array('p_id'=>$_POST['tag_id']))->find();
                if(empty($tag_find)){
                    $res = $model->where(array('tag_id'=>$_POST['tag_id']))->delete();
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
    public function tags_list(){
        $model = M("sys_tag");
        $p_id = $_GET['p_id'];
        $tag_find = $model->where(array('tag_id'=>$p_id))->find();
        $tag_list = $model->where(array('status'=>1,'p_id'=>$p_id))->select();
        $this->assign("tag_list",$tag_list);
        $this->assign("tag_find",$tag_find);
        $this->display();
    }

}