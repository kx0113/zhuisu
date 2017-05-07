<?php
namespace Admin\Controller;
use Think\Controller;

class TagController extends BaseController {
    public function upload_list(){
        $this->display();
    }
    public function upload_add(){
        $this->display();
    }
    public function tag_list(){
        $tag_list = M("sys_tag")->where(array('status'=>1,'p_id'=>0))->order('tag_id desc')->select();
        $this->assign("tag_list",$tag_list);
        Log_add(19,'访问标签列表');
        $this->display();
    }
    public function add_tag(){
        if(IS_POST){
            $model = M("sys_tag");
            $p_id = $_GET['p_id'];
            $data = I("post.");
            $data['status'] = 1;
            $data['name'] = trim($_POST['name']);
            $data['addtime'] = date('Y-m-d H-i-s',time());
           if(!empty($p_id)){
               $data['p_id'] = $p_id;
               $res = $model->add($data);
               if($res){
                   $this->success("新增标签成功！", U('Tag/tags_list',array('p_id'=>$p_id)));
               }else{
                   $this->error("新增标签失败，请稍后再试！", U('Tag/tags_list',array('p_id'=>$p_id)));
               }
           }else{
               $data['p_id'] = 0;
               $res = $model->add($data);
               if($res){
                   $this->success("新增标签成功！", U('Tag/tag_list'));
               }else{
                   $this->error("新增标签失败，请稍后再试！", U('Tag/tag_list'));
               }
           }
        }else{
            Log_add(9,'访问新建一级标签');
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
                Log_add(10,'修改一级标签成功');
                if($res){
                    $this->success("修改标签成功！", U('Tag/tag_list',array('p_id'=>$p_id)));
                }else{
                    $this->error("修改标签失败，请稍后再试！", U('Tag/tag_list',array('p_id'=>$p_id)));
                }
            }else{
                Log_add(10,'修改标签成功');
                if($res){
                    $this->success("修改标签成功！", U('Tag/tags_list',array('p_id'=>$p_id)));
                }else{
                    $this->error("修改标签失败，请稍后再试！", U('Tag/tags_list',array('p_id'=>$p_id)));
                }
            }
        }else{
            $this->display("add_tag");
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