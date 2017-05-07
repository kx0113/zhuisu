<?php
namespace Admin\Controller;
use Think\Controller;

class CreditController extends BaseController {
    /*
    * 信用评价
    * by King
    * 2016-12-31
    */
    public function honor_index(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        //输出所有企业信息
        $this->assign("Company_list",Company_list());
        if($_GET['company_id']){
            $where['company_id'] = $_GET['company_id'];
        }
        $where['status'] = 1;
        $where['type'] = 1;
        $honor_list = M("credit")->where($where)->order('credit_id desc')->select();
        foreach($honor_list as $k=>$v){
            //查询企业名称
            $company = Company_list($v['company_id']);
            $honor_list[$k]['company_name'] = $company['name'];
        }
        $this->assign("honor_list",$honor_list);
        $this->assign("val",'honor');
        $this->display();
    }
    public function honor_add(){
        $credit_model = M("credit");
        $credit_id = $_GET['credit_id'];
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        //输出所有企业信息
        $this->assign("Company_list",Company_list());
        if($credit_id){
            $company_find = $credit_model->where(array('credit_id'=>$credit_id))->find();
            $this->assign("credit",$company_find);
        }
        if(IS_POST){
            $data = I("post.");
            $data['status'] = 1;
            $data['type'] = 1;
            if($credit_id){
                $res = $credit_model->where(array('credit_id'=>$credit_id))->save($data);
                if($res){
                    Log_add(52,'修改信用数据成功',$res);
                    $this->success("修改信用数据成功！", U('Credit/honor_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    Log_add(53,'修改信用数据失败',$res);
                    $this->error("修改信用数据失败，请稍后再试！", U('Credit/honor_index',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $res = $credit_model->add($data);
                if($res){
                    Log_add(54,'增加信用数据成功',$res);
                    $this->success("增加信用数据成功！", U('Credit/honor_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    Log_add(55,'增加信用数据失败',$res);
                    $this->error("增加信用数据失败，请稍后再试！", U('Credit/honor_index',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            $this->display();
        }
    }
    /**
     * 删除信用
     */
    public function honor_del(){
        $model = M("credit");
        if($_POST['ids']){
            p($_POST);
            $where['credit_id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->delete();
            if($res){
                Log_add(56,'删除信用指标',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
    }
    public function honor_blacklist(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        //输出所有企业信息
        $this->assign("Company_list",Company_list());
        if($_GET['company_id']){
            $where['company_id'] = $_GET['company_id'];
        }
        $where['status'] = 2;
        $where['type'] = 1;
        $honor_list = M("credit")->where($where)->order('credit_id desc')->select();
        foreach($honor_list as $k=>$v){
            //查询企业名称
            $company = Company_list($v['company_id']);
            $honor_list[$k]['company_name'] = $company['name'];
        }
        $this->assign("honor_list",$honor_list);
        $this->assign("val",'blacklist');
        $this->display();
    }

    public function honor_add_blacklist(){
        $credit_model = M("credit");
        $credit_id = $_GET['credit_id'];
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        //输出所有企业信息
        $this->assign("Company_list",Company_list());
        if($credit_id){
            $company_find = $credit_model->where(array('credit_id'=>$credit_id))->find();
            $this->assign("credit",$company_find);
        }
        if(IS_POST){
            $data = I("post.");
            $data['status'] = 2;
            $data['type'] = 1;
            if($credit_id){
                $res = $credit_model->where(array('credit_id'=>$credit_id))->save($data);
                if($res){
                    Log_add(52,'修改黑名单数据成功',$res);
                    $this->success("修改黑名单数据成功！", U('Credit/honor_blacklist',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    Log_add(53,'修改黑名单数据失败',$res);
                    $this->error("修改黑名单数据失败，请稍后再试！", U('Credit/honor_blacklist',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $res = $credit_model->add($data);
                if($res){
                    Log_add(54,'增加黑名单数据成功',$res);
                    $this->success("增加黑名单数据成功！", U('Credit/honor_blacklist',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    Log_add(55,'增加黑名单数据失败',$res);
                    $this->error("增加黑名单数据失败，请稍后再试！", U('Credit/honor_blacklist',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            $this->display();
        }
    }
    /*
    * 信用分类列表
    */
    public function credit_classify(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());

        $model=M("credit");
        $key=trim(I('key'));
        if(!empty($key)){
            $where['classify_name'] = array('like', "%{$key}%");
        }
        $where['status']=1;
        $where['type']=2;

        $page=isset($_GET['p'])?$_GET['p']:1;
        $list = $model->where($where)->order('credit_id desc')->page($page.',14')->select();
        $this->assign('info_list',$list);// 赋值数据集
        $count      = $model->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,14);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $this -> assign('count',$count);
        $this->assign('key',$key);// 赋值分页输出*/
        $this->assign('page',$show);// 赋值分页输出*/
        Log_add(474,'访问信用分类列表页面');
        $this->assign('cz','credit_classify');
        $this->display();
    }
    //添加-编辑信用分类
    public function add_credit_classify(){
        $model = M("credit");
        if($_GET['id']){
            $credit_classify = $model ->where(array('credit_id'=>$_GET['id']))->find();
            $this->assign("info",$credit_classify);
        }
        if(IS_POST){
            $data = I("post.");


            if($_POST['id']){
                $data['type']=2;
                $res = $model->where(array('credit_id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(475,'修改信用分类',$_POST['id']);
                    $this->success("修改成功！", U('Credit/credit_classify',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请稍后再试！", U('Credit/credit_classify',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $data['type']=2;
                $res = $model->add($data);
                if($res){
                    $this->success("新增成功！", U('Credit/credit_classify',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请稍后再试！", U('Credit/credit_classify',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(476,'访问新增信用分类页');
            $this->display();
        }
    }
    //detail详细信用分类
    public function detail_credit_classify(){
        $model = M("credit");
        if($_GET['id']){
            $credit_classify = $model ->where(array('credit_id'=>$_GET['id']))->find();
            $this->assign("credit_classify",$credit_classify);
        }
        Log_add(477,'访问详细信用分类页');
        $this->display();
    }
    /**
     * 删除信用分类数据
     */
    public function del_credit_classify(){
        $model = M("credit");

        if($_POST['ids']){

            $where['credit_id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->delete();
            if($res){
                Log_add(478,'删除信用分类',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('credit_id'=>$_GET['id']))->delete();
            if($res){
                Log_add(479,'删除信用分类',$res);
                $this->success("删除成功！", U('Credit/credit_classify',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Credit/credit_classify',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }






    /*
    * 信用指标列表
    */
    public function credit_evaluating(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());

        $model=M("evaluating");
        $key=trim(I('key'));
        if(!empty($key)){
            $where['name'] = array('like', "%{$key}%");
        }


        $page=isset($_GET['p'])?$_GET['p']:1;
        $list = $model->where($where)->order('id desc')->page($page.',14')->select();
        foreach($list as $key=>$val){
           $info= M('credit')->where(array('credit_id'=>$val['credit_id']))->field('classify_name')->find();
            $list[$key]['classify_name']=$info['classify_name'];
        }
        $this->assign('info_list',$list);// 赋值数据集
        $count      = $model->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,14);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $this -> assign('count',$count);
        $this->assign('key',$key);// 赋值分页输出*/
        $this->assign('page',$show);// 赋值分页输出*/
        Log_add(480,'访问信用指标列表页面');
        $this->assign('cz','credit_evaluating');
        $this->display();
    }
    //添加-编辑信用指标
    public function add_credit_evaluating(){
        $model = M("evaluating");
        $credit= M('credit')->where(array('type'=>2,'status'=>1))->select();
        $this->assign("credit_classify",$credit);
        if($_GET['id']){
            $credit_evaluating = $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("info",$credit_evaluating);
        }
        if(IS_POST){
            $data = I("post.");


            if($_POST['id']){
                $data['type']=2;
                $res = $model->where(array('id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(480,'修改信用指标',$_POST['id']);
                    $this->success("修改成功！", U('Credit/credit_evaluating',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请稍后再试！", U('Credit/credit_evaluating',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $data['type']=2;
                $res = $model->add($data);
                if($res){
                    $this->success("新增成功！", U('Credit/credit_evaluating',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请稍后再试！", U('Credit/credit_evaluating',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(481,'访问新增信用指标页');
            $this->display();
        }
    }
    //detail详细信用指标
    public function detail_credit_evaluating(){
        $model = M("evaluating");
        if($_GET['id']){
            $credit_evaluating = $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("credit_evaluating",$credit_evaluating);
        }
        Log_add(482,'访问详细信用指标页');
        $this->display();
    }
    /**
     * 删除信用指标数据
     */
    public function del_credit_evaluating(){
        $model = M("evaluating");

        if($_POST['ids']){

            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->delete();
            if($res){
                Log_add(483,'删除信用指标',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->delete();
            if($res){
                Log_add(483,'删除信用指标',$res);
                $this->success("删除成功！", U('Credit/credit_evaluating',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Credit/credit_evaluating',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }

    public function evaluate_index(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        $this->display();
    }
    public function add(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        $this->display();
    }

}