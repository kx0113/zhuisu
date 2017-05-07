<?php
namespace Admin\Controller;
use Think\Controller;

class ProductController extends BaseController {
    public function index(){
        $product_list = M("product")->order('product_id desc')->select();
        foreach($product_list as $k=>$v){
            if($v['site_name']){
                $site = Tb_data('find_area',$v['site_name']);
                $product_list[$k]['site_name'] = $site[0]['area_name'];
            }
        }
        $this->assign("product_list",$product_list);
        Log_add(13,'访问产品列表',$_POST['pro_name']);
        $this->display();
    }
    //添加基础信息
    public function add_base(){
        $model = M("product");
        if($_GET['product_id']){
            $product = $model ->where(array('product_id'=>$_GET['product_id']))->find();
            $product['contractor_mobile'] = Contractor($product['contractor']);
            if($product['plant_area']){
                $this->assign("site_name",Tb_data('site_name',$product['plant_area']));
            }
            $this->assign("product",$product);
        }
        //产品类别
        $this->assign("pro_type",Tag_list(6));
        //承包人
        $this->assign("contractor",Contractor());
        if(IS_POST){
            $data = I("post.");
            $data['addtime'] = date('Y-m-d H-i-s',time());
            if($_POST['id']){
                $data['id'] = 'ZS-00'.$_POST['id'];
                $res = $model->where(array('product_id'=>$_POST['id']))->save($data);
                if($res){
                    $this->success("修改基础信息成功！", U('product/add_intro',array('product_id'=>$_GET['product_id'],'m'=>'intro')));
                }else{
                    $this->error("修改基础信息失败，请稍后再试！", U('product/index'));
                }
            }else{
                $res = $model->add($data);
                //拼装溯源码
                $datas['id'] = 'ZS-00'.$res;
                $ress = $model->where(array('product_id'=>$res))->save($datas);
                if($res){
                    Log_add(12,'新增基础信息成功',$_POST['pro_name']);
                    $this->success("新增基础信息成功！", U('product/add_base',array('product_id'=>$res,'m'=>'base')));
                }else{
                    $this->error("新增基础信息失败，请稍后再试！", U('product/index'));
                }
            }
        }else{
            Log_add(11,'访问新建产品页面');
            $this->display();
        }
    }
    public function step_area(){
        $m = $_GET['m'];
        $Model = new \Think\Model();
        switch ($m){
            case $m == "area_val":
                $area_val =$_POST['area_val'];
                $result = $Model->query("SELECT id,device_name FROM tb_device WHERE area_id = $area_val");
                output_data($result);
                break;
            case $m == "device_type":
                $device_type =$_POST['device_type'];
                $result = $Model->query("SELECT id,device_name,device_code FROM tb_device WHERE id = $device_type");
                output_data($result);
                break;
            case $m == "plant_area":
                $parent_id =$_POST['plant_area'];
                $result = $Model->query("SELECT id,area_name FROM tb_area WHERE parent_id = $parent_id");
                output_data($result);
                break;
        }
    }
 
    //添加产品简介
    public function add_intro(){
        $model = M("product");
        if($_GET['product_id']){
            $product = $model ->where(array('product_id'=>$_GET['product_id']))->find();
            if (!empty($product['certificate'])){
                $pic = explode(',',$product['certificate']);
            }else{
                $pic = array();
            }
            $this->assign("pic",$pic);
            $this->assign("product",$product);
        }
        if(IS_POST){
            $config = array(
                'mimes'         =>  array(), //允许上传的文件MiMe类型
                'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
                'exts'          =>  array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
                'autoSub'       =>  true, //自动子目录保存文件
                'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
                'saveName'   =>    array('uniqid',''),
                'rootPath'      =>  './Public/Uploads/', //保存根路径
                'savePath'      =>  '',//保存路径

            );
            $data = array();
            $where = array();
            $where['product_id'] = intval($_POST['product_id']);
            $data['pro_desc'] = trim($_POST['pro_desc']);
            $upload = new \Think\Upload($config);// 实例化上传类
            $info   =   $upload->upload();
            if (!empty($info['pro_picture']['savename'])){
                $data['pro_picture'] = 'Public/Uploads/'.$info['pro_picture']['savepath'].$info['pro_picture']['savename'];
            }
            //print_r($where);die();
            $res = $model->where($where)->save($data);
            if($res){
                $this->success("新增基础信息成功！", U('product/add_seed',array('product_id'=>$_GET['product_id'],'m'=>'seed')));
            }else{
                $this->error("新增基础信息失败，请稍后再试！", U('product/index'));
            }
        }else{
            $this->display();
        }
    }
    //上传图片处理函数
    public function add_introPic(){
        if (isset($_GET['id'])){
            $where =array();
            $where['product_id'] = intval($_GET['id']);
        }else{
            $this->error('非法请求');
        }
        //print_r($where);die();
        $config = array(
            'mimes'         =>  array(), //允许上传的文件MiMe类型
            'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
            'exts'          =>  array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
            'autoSub'       =>  true, //自动子目录保存文件
            'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'saveName'   =>    array('uniqid',''),
            'rootPath'      =>  './Public/Uploads/', //保存根路径
            'savePath'      =>  '',//保存路径

        );
        $upload = new \Think\Upload($config);// 实例化上传类
        $m = M('product');
        $bf = $m->field('product_id,certificate')->where($where)->select();
        if (empty($bf)){
            $this->error('暂无此产品');
        }else{
            $bf = $bf[0];
        }
        $mes = $bf['certificate'];
        $num = explode(',',$mes);
        if (count($num)>3){
            $this->aaaaaaa();
        }
        $info   =   $upload->upload();
        //print_r($info);die();
        $data = array();
        if (empty($mes)){
            $data['certificate'] = 'Public/Uploads/'.$info['file']['savepath'].$info['file']['savename'];
        }else{
            $data['certificate'] = $mes.','.'Public/Uploads/'.$info['file']['savepath'].$info['file']['savename'];
        }
        //print_r($data);die();
        $m->where(array('product_id'=>$where['product_id']))->save($data);
    }
    //删除证书图片
    public function delCertificate(){
        if (!isset($_GET['id'])){
            $this->error('非法请求');
        }else{
            $where = array();
            $where['product_id'] = intval($_GET['id']);
        }
        if (isset($_GET['num'])){
            $num = intval($_GET['num'])-1;
        }else{
            $num = 0;
        }
        $model = M('product');
        $mes = $model->field('product_id,certificate')->where($where)->select();
        if (empty($mes)){
            $this->error('未找到该商品');
        }else{
            $mes = $mes[0]['certificate'];
        }
        $res = explode(',',$mes);
        unset($res[$num]);
        $fun = '';
        foreach ($res as $k=>$v){
            if ($fun == ''){
                $fun = $v;
            }else{
                $fun = $fun.','.$v;
            } 
        }
        if($model->where($where)->save(array('certificate'=>$fun))){
            $this->success('删除成功');
        }else{
            $this->error('删除失败，请稍后重试');
        }
    }
    //添加种子信息
    public function add_seed(){
        $model = M("product_seed");
        if($_GET['product_id']){
            $product = $model ->where(array('product_id'=>$_GET['product_id']))->find();
            $this->assign("product",$product);
        }
        //品牌
        $this->assign("brand",stride_mysql('brand'));
        //生厂商
        $this->assign("manufacturer",stride_mysql('manufacturer'));
        //销售商
        $this->assign("retailer",stride_mysql('retailer'));
        //承包人
        $purchaser = M("contractor") ->select();
        $this->assign("purchaser",$purchaser);
        if(IS_POST){
            $data = I("post.");
            $data['product_id'] = $_GET['product_id'];
            $data['addtime'] = date('Y-m-d H-i-s',time());
            $config = array(
                'mimes'         =>  array(), //允许上传的文件MiMe类型
                'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
                'exts'          =>  array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
                'autoSub'       =>  true, //自动子目录保存文件
                'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
                'saveName'   =>    array('uniqid',''),
                'rootPath'      =>  './Public/Uploads/', //保存根路径
                'savePath'      =>  '',//保存路径
            
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $info   =   $upload->upload();
            if (!empty($info['picture']['savename'])){
                $data['picture'] = 'Public/Uploads/'.$info['picture']['savepath'].$info['picture']['savename'];
            }
            if($_POST['id']){
                $res = $model->where(array('product_id'=>$_POST['id']))->save($data);
                if($res){
                    $this->success("修改种子信息成功！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }else{
                    $this->error("修改种子信息失败，请稍后再试！", U('product/index'));
                }
            }else{
                $res = $model->add($data);
                if($res){
                    $this->success("新增种子信息成功！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }else{
                    $this->error("新增种子信息失败，请稍后再试！", U('product/index'));
                }
            }
        }else{
            $this->display();
        }
    }
    //植物生长图片列表
    public function index_plant(){
        $product_plant_list = M("product_plant")->where(array('product_id'=>$_GET['product_id']))->order('plant_id desc')->select();
        foreach($product_plant_list as $k=>$v){
            //设备信息
            $Model = new \Think\Model();
            if($v['device_id']){
                $Tb_data_device = $Model->query("SELECT * FROM tb_device WHERE id = $v[device_id]");
                $area_id = $Tb_data_device[0]['area_id'];
            }
            //查询区域
            if($area_id){
                $Tb_data_area = $Model->query("SELECT area_name FROM tb_area WHERE id = $area_id");
                $product_plant_list[$k]['device_name'] = $Tb_data_device[0]['device_name'];
                $product_plant_list[$k]['device_num'] = $Tb_data_device[0]['device_code'];
                $product_plant_list[$k]['device_type'] = $Tb_data_device[0]['device_type'];
                $product_plant_list[$k]['area'] = $Tb_data_area[0]['area_name'];
            }
        }
        $this->assign("product_plant_list",$product_plant_list);
        $this->display();
    }
    //添加植物生长图片
    public function add_plant(){
        $model = M("product_plant");
        //新建  如果之前有数据
        $product_plant = $model->where(array('product_id'=>$_GET['product_id']))->find();
        if($product_plant){
            //设备信息
            if($product_plant['device_id']){
                $this->assign("device_info",Tb_data('device_info',$product_plant['device_id']));
            }
        }
        //编辑信息
        $product = $model ->where(array('plant_id'=>$_GET['plant_id']))->find();
        $this->assign("product",$product);
         //设备信息
         if($product['device_id']){
             $this->assign("device_info",Tb_data('device_info',$product['device_id']));
         }
        //地块
        $product_site_name = M("product") ->where(array('product_id'=>$_GET['product_id']))->getField('site_name');//取得主表地块id
        if($product_site_name){
            $this->assign("area",Tb_data('find_area',$product_site_name));
        }
        //时期
        $this->assign("Stage_list",Stage_list($_GET['product_id']));
        if(IS_POST){
            $data = I("post.");
            $data['product_id'] = $_GET['product_id'];
            $data['addtime'] = date('Y-m-d H-i-s',time());
            //查询多少个
            $plant_count = $model->where(array('product_id'=>$_GET['product_id']))->count();
            if($_POST['id']){
                $res = $model->where(array('plant_id'=>$_POST['id']))->save($data);
                if($res){
                    $this->success("修改植物生长成功！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }else{
                    $this->error("修改植物生长失败，请稍后再试！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }
            }else{
                $data['number'] = $plant_count+1;
                $res = $model->add($data);
                if($res){
                    $this->success("新增植物生长成功！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }else{
                    $this->error("新增植物生长失败，请稍后再试！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }
            }
        }else{
            $this->display();
        }
    }
    //添加植物生长照片-本地
    public function add_plant_local(){
        $model = M("product_plant");
        if($_GET['plant_id']){
            $product = $model ->where(array('plant_id'=>$_GET['plant_id']))->find();
            $this->assign("product",$product);
        }
        $this->assign("Stage_list",Stage_list($_GET['product_id']));
        if(IS_POST){
            $data = I("post.");
            $data['product_id'] = $_GET['product_id'];
            $data['addtime'] = date('Y-m-d H-i-s',time());
            $config = array(
                'mimes'         =>  array(), //允许上传的文件MiMe类型
                'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
                'exts'          =>  array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
                'autoSub'       =>  true, //自动子目录保存文件
                'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
                'saveName'   =>    array('uniqid',''),
                'rootPath'      =>  './Public/Uploads/', //保存根路径
                'savePath'      =>  '',//保存路径
            
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $info   =   $upload->upload();
            if (!empty($info['pic']['savename'])){
                $data['pic'] = 'Public/Uploads/'.$info['pic']['savepath'].$info['pic']['savename'];
            }
            //查询多少个
            $plant_count = $model->where(array('product_id'=>$_GET['product_id']))->count();
            if($_POST['id']){
                $res = $model->where(array('plant_id'=>$_POST['id']))->save($data);
                if($res){
                    $this->success("修改植物生长成功！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }else{
                    $this->error("修改植物生长失败，请稍后再试！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }
            }else{
                $data['number'] = $plant_count+1;
                $res = $model->add($data);
                if($res){
                    $this->success("新增植物生长成功！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }else{
                    $this->error("新增植物生长失败，请稍后再试！", U('product/index_plant',array('product_id'=>$_GET['product_id'],'m'=>'plant')));
                }
            }
        }else{
            $this->display();
        }
    }
    //肥料使用列表
    public function index_manure(){
        $product_manure_list = M("product_manure")->where(array('product_id'=>$_GET['product_id']))->select();
        foreach($product_manure_list as $k=>$v){
            $tag = M("sys_tag")->where(array('tag_id'=>$v['kind']))->find();
            $product_manure_list[$k]['kind'] = $tag['name'];
        }
        $this->assign("product_manure_list",$product_manure_list);
        $this->display();
    }
    //添加肥料使用
    public function add_manure(){
        $model = M("product_manure");
        if($_GET['manure_id']){
            $product = $model ->where(array('manure_id'=>$_GET['manure_id']))->find();
            $this->assign("product",$product);
        }
        //品牌
        $this->assign("brand",stride_mysql("brand"));
        //生厂商
        $this->assign("manufacturer",stride_mysql("manufacturer"));
        //销售商
        $this->assign("retailer",stride_mysql("retailer"));
        //时期
        $this->assign("stage_list",Stage_list($_GET['product_id']));
        //肥料种类
        $this->assign("kind",Tag_list(38));
        if(IS_POST){
            $data = I("post.");
            $data['product_id'] = $_GET['product_id'];
            $data['addtime'] = date('Y-m-d H-i-s',time());
            //查询多少个
            $manure_count = $model->where(array('product_id'=>$_GET['product_id']))->count();
            if($_POST['id']){
                $res = $model->where(array('manure_id'=>$_POST['id']))->save($data);
                if($res){
                    $this->success("修改肥料使用成功！", U('product/index_manure',array('product_id'=>$_GET['product_id'],'m'=>'manure')));
                }else{
                    $this->error("修改肥料使用失败，请稍后再试！", U('product/index_manure',array('product_id'=>$_GET['product_id'],'m'=>'manure')));
                }
            }else{
                $data['number'] = $manure_count+1;
                $res = $model->add($data);
                if($res){
                    $this->success("新增肥料使用成功！", U('product/index_manure',array('product_id'=>$_GET['product_id'],'m'=>'manure')));
                }else{
                    $this->error("新增肥料使用失败，请稍后再试！", U('product/index_manure',array('product_id'=>$_GET['product_id'],'m'=>'manure')));
                }
            }
        }else{
            $this->display();
        }
    }
    //农药使用列表
    public function index_pesticide(){
        $product_pesticide_list = M("product_pesticide")->where(array('product_id'=>$_GET['product_id']))->select();
        foreach($product_pesticide_list as $k=>$v){
            $stage = M("sys_stage")->where(array('stage_id'=>$v['period']))->find();
            $product_pesticide_list[$k]['stage_id'] = $stage['stage_name'];
        }
        $this->assign("product_pesticide_list",$product_pesticide_list);
        $this->display();
    }
    //添加农药使用
    public function add_pesticide(){
        $model = M("product_pesticide");
        if($_GET['pesticide_id']){
            $product = $model ->where(array('pesticide_id'=>$_GET['pesticide_id']))->find();
            $this->assign("product",$product);
        }
        //品牌
        $this->assign("brand",stride_mysql("brand"));
        //生厂商
        $this->assign("manufacturer",stride_mysql("manufacturer"));
        //销售商
        $this->assign("retailer",stride_mysql("retailer"));
        //时期
        $this->assign("stage_list",Stage_list($_GET['product_id']));
        if(IS_POST){
            $data = I("post.");
            $data['product_id'] = $_GET['product_id'];
            $data['addtime'] = date('Y-m-d H-i-s',time());
            if($_POST['id']){
                $res = $model->where(array('pesticide_id'=>$_POST['id']))->save($data);
                if($res){
                    $this->success("修改农药使用成功！", U('product/index_pesticide',array('product_id'=>$_GET['product_id'],'m'=>'pesticide')));
                }else{
                    $this->error("修改农药使用失败，请稍后再试！", U('product/index_pesticide',array('product_id'=>$_GET['product_id'],'m'=>'pesticide')));
                }
            }else{
                //查询多少个
                $pesticide_count = $model->where(array('product_id'=>$_GET['product_id']))->count();
                $data['number'] = $pesticide_count+1;
                $res = $model->add($data);
                if($res){
                    $this->success("新增农药使用成功！", U('product/index_pesticide',array('product_id'=>$_GET['product_id'],'m'=>'pesticide')));
                }else{
                    $this->error("新增农药使用失败，请稍后再试！", U('product/index_pesticide',array('product_id'=>$_GET['product_id'],'m'=>'pesticide')));
                }
            }
        }else{
            $this->display();
        }
    }

    //添加生长环境数据
    public function add_grow(){
        $model = M("product_grow");
        if($_GET['product_id']){
            $product = $model ->where(array('product_id'=>$_GET['product_id']))->find();
            //设备信息
            if($product['device_id']){
                $this->assign("device_info",Tb_data('device_info',$product['device_id']));
            }
            $this->assign("product",$product);
        }
        //地块
        $product_site_name = M("product") ->where(array('product_id'=>$_GET['product_id']))->getField('site_name');//取得主表地块id
        if($product_site_name){
            $this->assign("area",Tb_data('find_area',$product_site_name));
        }
        if(IS_POST){
            $data = I("post.");
            $data['product_id'] = $_GET['product_id'];
            $data['addtime'] = date('Y-m-d H-i-s',time());
            if($_POST['id']){
                $res = $model->where(array('pesticide_id'=>$_POST['id']))->save($data);
                if($res){
                    $this->success("修改生长环境数据成功！", U('product/add_grow',array('product_id'=>$_GET['product_id'],'m'=>'grow')));
                }else{
                    $this->error("修改生长环境数据失败，请稍后再试！", U('product/index'));
                }
            }else{
                $res = $model->add($data);
                if($res){
                    $this->success("新增生长环境数据成功！", U('product/add_grow',array('product_id'=>$_GET['product_id'],'m'=>'grow')));
                }else{
                    $this->error("新增生长环境数据失败，请稍后再试！", U('product/index'));
                }
            }
        }else{
            $this->display();
        }
    }
    public function edit_product(){
        $p_id = $_GET['p_id'];
        if($_GET['product_id']){
            $product_list = M("sys_product")->where(array('product_id'=>$_GET['product_id']))->find();
            $this->assign("product_list",$product_list);
        }
        if(IS_POST){
            $model = M("sys_product");
            $data = I("post.");
            $data['status'] = 1;
            $data['name'] = trim($_POST['name']);
            $data['addtime'] = date('Y-m-d',time());
            $res = $model->where(array('product_id'=>$data['product_id']))->save($data);
            if($p_id == '0'){
                if($res){
                    $this->success("修改标签成功！", U('product/product_list',array('p_id'=>$p_id)));
                }else{
                    $this->error("修改标签失败，请稍后再试！", U('product/product_list',array('p_id'=>$p_id)));
                }
            }else{
                if($res){
                    $this->success("修改标签成功！", U('product/products_list',array('p_id'=>$p_id)));
                }else{
                    $this->error("修改标签失败，请稍后再试！", U('product/products_list',array('p_id'=>$p_id)));
                }
            }
        }else{
            $this->display("product_add_product");
        }
    }
    public function switchs(){
        switch ($_POST['product']){
            //删除标签
            case $_POST['product'] == "product_del":
                $model = M("sys_product");
                $product_find = $model->where(array('p_id'=>$_POST['product_id']))->find();
                if(empty($product_find)){
                    $res = $model->where(array('product_id'=>$_POST['product_id']))->delete();
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
    public function products_list(){
        $model = M("sys_product");
        $p_id = $_GET['p_id'];
        $product_find = $model->where(array('product_id'=>$p_id))->find();
        $product_list = $model->where(array('status'=>1,'p_id'=>$p_id))->select();
        $this->assign("product_list",$product_list);
        $this->assign("product_find",$product_find);
        $this->display();
    }
}