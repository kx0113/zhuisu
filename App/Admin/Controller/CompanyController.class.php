<?php
namespace Admin\Controller;
use Think\Controller;

class CompanyController extends BaseController {
    public function index(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        //企业信息
        $this->assign("company_list",Company_list());
        Log_add(39,'访问企业信息列表页面');
        $this->display();
    }
        public function add_company(){
            $model = M("sys_company");
            $company_id = $_REQUEST['company_id'];
            //企业信息
            $this->assign("company_list",Company_list($company_id));
            if(IS_POST){
                $data = I("post.");
//                $file = $_FILES['picture'];
//                if($file['size'] > 0) {
//                    $upload = new \Think\Upload();// 实例化上传类
//                    $upload->maxSize = 3145728;// 设置附件上传大小
//                    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
//                    $upload->rootPath = './Uploads/'; // 设置附件上传根目录
//                    $upload->savePath = ''; // 设置附件上传（子）目录
//                    // 上传文件
//                    $info = $upload->upload();
//                    if (!$info) {// 上传错误提示错误信息
//                        $this->error($upload->getError());
//                    } else {// 上传成功
//                        $filename = '/Uploads/' . $info['picture']['savepath'] . '/' . $info['picture']['savename'];
//                        $data['license'] = $filename;
//                    }
//                }
                $data['name'] = trim($_POST['name']);
                $data['status'] = 1;
                $data['addtime'] = date('Y-m-d H-i-s',time());
                if($company_id){
                    $res = $model->where(array('company_id'=>$company_id))->save($data);
                    if($res){
                        Log_add(37,'修改企业信息成功');
                        $this->success("修改企业信息成功！", U('company/index',array('menu_id'=>$_REQUEST['menu_id'])));
                    }else{
                        $this->error("修改企业信息失败，请稍后再试！", U('company/index',array('menu_id'=>$_REQUEST['menu_id'])));
                    }
                }else{
                    $res = $model->add($data);
                    if($res){
                        Log_add(36,'新建企业信息成功');
                        $this->success("新增企业信息成功！", U('company/index',array('menu_id'=>$_REQUEST['menu_id'])));
                    }else{
                        $this->error("新增企业信息失败，请稍后再试！", U('company/index',array('menu_id'=>$_REQUEST['menu_id'])));
                    }
                }
            }else{
                Log_add(35,'访问新建企业信息页面');
                $this->display();
            }
        }
    //企业信息
    public function message(){
        if(!$_GET['company_id']){
            $this->error("暂无企业信息!");
        }
        //企业信息
        $this->assign("company_list",Company_list($_GET['company_id']));
        Log_add(39,'访问企业信息列表页面');
        $this->display();
    }

}