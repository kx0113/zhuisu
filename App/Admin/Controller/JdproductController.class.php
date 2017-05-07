<?php
/**
 * 同江绿色食品管理平台
 * 认证管理控制器
 * Author: dalely
 * Date: 2016-12-3
 */
namespace Admin\Controller;
use Think\Controller;

class JdproductController extends BaseController {
    /*
     * 生产档案首页
     */
    public function index(){

    }
    /*
     *生产档案-基本信息管理首页
     */
    public function base_information(){
        $this->display();
    }
    /*
     * 生产档案-田间操作管理首页
     */
    public function field_product(){
        $this->display();
    }
    /*
     * 生产基地基本情况明细列表页
     */
    public function base_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = trim(I('key_type'));
        $key = trim(I('key'));
        $base = M('base_prbase');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $base->where($where)->page($p.',10')->order('datetime desc')->select();
        //dump($info);exit;
        $count = M('base_prbase')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        Log_add(329,'访问生产基地基本情况细列表页面');
        $nav = 'base';
        $this->assign('key_type',$key_type);
        $this->assign('nav',$nav);
        $this->display();
    }
    /*
     * 生产基地基本情况添加与编辑
     */
    public function base_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_prbase')->where('id='.$id)->find();
            $this->assign('info2',$info2);
        }
        if(IS_POST)            //新增或编辑
        {
            $data = I('post.');
            $count = count($data);
            foreach($data as $k=>&$v){
                trim($v);
            }
//            $place = M('place');
//            $where = array();
//            $where = $data['place_id'];
//            $info = $place->field('place_name')->where($where)->find();
            if(empty($data)){
                $this->error('请输入相关信息！');
            }
//            if(empty($info)){
//                $this->error('产地信息不存在，请检查产地编号！');
//            }
            if(!$_POST['id']) {                                 //增加信息
                $add = M('base_prbase')->add($data);
                if ($add) {
                    Log_add(331,'新增生产基地基本情况成功证明');          //添加log
                    $this->success('添加成功', U('Jdproduct/base_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdproduct/base_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_prbase')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(332,'修改生产基地基本情况成功证明');
                    $this->success('修改成功',U('Jdproduct/base_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdproduct/base_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
//            $place_id = I('pose.place_id');
//            $info2 = M('base_place')->field('place_address,plant_size,soil_type,irrigation_water,plant_area,environment,remark')->find();
            Log_add(330,'访问新增空气质量页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    /*
     * 生产基地基本情况删除
     */
    public function base_del(){
        $model = M("base_prbase");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(333,'删除产品认证信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(333,'删除产品认证信息',$res);
                $this->success("删除成功！", U('Jdproduct/base_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdproduct/base_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
    * 查看详情
    */
    public function  base_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_prbase')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }
    /*
    * ajax返回数据
    */
    public function base_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_address,place_name,farmer_name,soil_type,plant_size,irrigation_water,plant_area,site_name,environment,remark')->where(array('place_id'=>$place_id))->find();
        if(!empty($info)){
            $model = new \Think\Model();
            $a = 'find_area';
            $b = $info['plant_area'];
            $c = Tb_data($a,$b);
            $d = $c[0]['area_name'];
            $e = $info['site_name'];
            $f = 'select area_name from tb_area where id='."$e";
            $k = $model->query($f);
            $info['site_name'] = $k[0]['area_name'];
            $info['plant_area'] = $d;
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
     * 基础信息excel导入
     */
    public function base_import(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功

            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];

            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类

            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }

            }
            //需要保存的数据，
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){

                $data['place_address']=$val['A'];
                $data['plant_size'] = $val['B'];
                $data['soil_type']=$val['C'];
                $data['irrigation_water']=$val['D'];
                $data['datetime']=$val['E'];
                $data['farmer_name']=$val['F'];
                $data['plantinfo']=$val['G'];
                $data['plant_area']=$val['H'];
                $data['site_name']=$val['I'];

                $res= M("base_prbase")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '生产基地基本情况';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(335,'导入生产基地基本情况表');
                $this->success("导入成功！", U('Jdproduct/base_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdproduct/base_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 基本信息excel导出
     */
    public function base_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_prbase");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('place_address,plant_size,soil_type,irrigation_water,datetime,farmer_name,plantinfo,plant_area,site_name')->
        order('id desc')->select();

        Log_add(334,'导出生产基地基本情况认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_prbase";
        $headArr=array("生产基地地址","种植面积","土壤类型","灌溉水源名称"
        ,"日期", "农户名称","种植品种","所属乡镇","所属村");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /*
     * 生产资料购买管理明细列表页
     */
    public function data_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = trim(I('key_type'));
        $key = trim(I('key'));
        $data = M('base_prdata');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $data->where($where)->page($p.',10')->order('datetime desc')->select();
        //dump($info);exit;
        $count = M('base_prdata')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        Log_add(336,'访问生产资料购买管理明细列表页面');
        $nav = 'data';
        $this->assign('key_type',$key_type);
        $this->assign('nav',$nav);
        $this->display();
    }
    /*
     * 生产资料购买管理增加与编辑
     */
    public function data_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_prdata')->where('id='.$id)->find();
            $this->assign('info2',$info2);
        }
        if(IS_POST)            //新增或编辑
        {
            $data = I('post.');
            $count = count($data);
            foreach($data as $k=>&$v){
                trim($v);
            }
            if(empty($data)){
                $this->error('请输入相关信息！');
            }
            if(!$_POST['id']) {                                 //增加信息
                $add = M('base_prdata')->add($data);
                if ($add) {
                    Log_add(338,'新增生产资料购买管理成功证明');          //添加log
                    $this->success('添加成功', U('Jdproduct/data_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdproduct/data_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_prdata')->where(array('id'=>$id))->save($data);
                if($save){
                    //dump($data);exit;
                    Log_add(339,'修改生产资料购买管理成功证明');
                    $this->success('修改成功',U('Jdproduct/data_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdproduct/data_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
//            $place_id = I('pose.place_id');
//            $info2 = M('base_place')->field('place_address,plant_size,soil_type,irrigation_water,plant_area,environment,remark')->find();
            Log_add(336,'访问生产资料购买管理页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    /*
     * 生产资料购买管理删除
     */
    public function data_del(){
        $model = M("base_prdata");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(340,'删除生产资料购买管理成功证明',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(340,'删除生产资料购买管理成功证明',$res);
                $this->success("删除成功！", U('Jdproduct/data_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdproduct/data_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
       * 查看详情
       */
    public function  data_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_prdata')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }
    /*
    * ajax返回数据
    */
    public function data_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_address,place_name,farmer_name,soil_type,plant_size,site_name,irrigation_water,plant_area,environment,remark')->where(array('place_id'=>$place_id))->find();
        //dump($info);exit;
        if(!empty($info)){
            $model = new \Think\Model();
            $a = 'find_area';
            $b = $info['plant_area'];
            $c = Tb_data($a,$b);
            $d = $c[0]['area_name'];
            $e = $info['site_name'];
            $f = 'select area_name from tb_area where id='."$e";
            $k = $model->query($f);
            $info['site_name'] = $k[0]['area_name'];
            $info['plant_area'] = $d;
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
     * 基础信息excel导入
     */
    public function data_import(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功

            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];

            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类

            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }

            }
            //需要保存的数据，
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){

                $data['datetime']=$val['A'];
                $data['goods_name'] = $val['B'];
                $data['num']=$val['C'];
                $data['price']=$val['D'];
                $data['production']=$val['E'];
                $data['farmer_name']=$val['F'];
                $data['place_id']=$val['G'];
                $data['plant_area']=$val['H'];
                $data['site_name']=$val['I'];

                $res= M("base_prdata")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                Log_add(342,'导入生产资料购买管理表');
                $user_name = $_SESSION['account'];
                $explain = '生产资料购买';
                import_log($user_name,$explain,$num_all,$num_suc);
                $this->success("导入成功！", U('Jdproduct/data_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdproduct/data_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
    * 基本信息excel导出
    */
    public function data_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_prdata");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $info = M('place')->field('place_address,place_name,farmer_name,soil_type,plant_size,irrigation_water,plant_area,site_name,environment,remark')->where(array('place_id'=>$place_id))->find();

        Log_add(341,'导出生产资料购买管理认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_prdata";
        $headArr=array("日期","产品名称","数量","价格"
        ,"生产单位", "农户名称","产地编号","所属乡镇","所属村");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /*
     * 农药使用状态明细列表页
     */
    public function druguse_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = trim(I('key_type'));
        $key = trim(I('key'));
        $druguse = M('base_prdruguse');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $druguse->where($where)->page($p.',10')->order('datetime desc')->select();
        //dump($info);exit;
        $count = M('base_prdruguse')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        Log_add(343,'访问农药使用情况记录明细列表页面');
        $nav = 'druguse';
        $this->assign('key_type',$key_type);
        $this->assign('nav',$nav);
        $this->display();
    }
    /*
     *农药使用状况添加与编辑
     */
    public function druguse_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_prdruguse')->where('id='.$id)->find();
            $place = M('place')->where(array('id'=>$id))->find();
            $this->assign('info2',$info2);
            $this->assign('place',$place);
        }
        if(IS_POST)            //新增或编辑
        {
            $data = I('post.');
            $count = count($data);
            foreach($data as $k=>&$v){
                trim($v);
            }
            if(empty($data)){
                $this->error('请输入相关信息！');
            }
            if(!$_POST['id']) {                                 //增加信息
                $add = M('base_prdruguse')->add($data);
                if ($add) {
                    Log_add(345,'新增农药使用情况记录成功证明');          //添加log
                    $this->success('添加成功', U('Jdproduct/druguse_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdproduct/druguse_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_prdruguse')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(346,'修改农药使用情况记录成功证明');
                    $this->success('修改成功',U('Jdproduct/druguse_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdproduct/druguse_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
//            $place_id = I('pose.place_id');
//            $info2 = M('base_place')->field('place_address,plant_size,soil_type,irrigation_water,plant_area,environment,remark')->find();
            Log_add(344,'访问农药使用情况记录页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    /*
     *农药使用状况删除
     */
    public function druguse_del(){
        $model = M("base_prdruguse");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(347,'删除农药使用情况记录成功证明',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(347,'删除农药使用情况记录成功证明',$res);
                $this->success("删除成功！", U('Jdproduct/druguse_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdproduct/druguse_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }

    /*
     * 查看详情
     */
    public function  druguse_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_prdruguse')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }

    /*
      * ajax返回数据
      */
    public function druguse_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_address,place_name,farmer_name,soil_type,plant_size,irrigation_water,plant_area,site_name,environment,remark')->where(array('place_id'=>$place_id))->find();   //dump($info);exit;
        if(!empty($info)){
            $model = new \Think\Model();
            $a = 'find_area';
            $b = $info['plant_area'];
            $c = Tb_data($a,$b);
            $d = $c[0]['area_name'];
            $e = $info['site_name'];
            $f = 'select area_name from tb_area where id='."$e";
            $k = $model->query($f);
            $info['site_name'] = $k[0]['area_name'];
            $info['plant_area'] = $d;
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
    * 基础信息excel导入
    */
    public function druguse_import(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功

            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];

            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类

            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }

            }
            //需要保存的数据，
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){

                $data['drug_name']=$val['A'];
                $data['amount'] = $val['B'];
                $data['datetime']=$val['C'];
                $data['weather']=$val['D'];
                $data['operator']=$val['E'];
                $data['place_name']=$val['F'];
                $data['farmer_name']=$val['G'];
                $data['plantinfo']=$val['H'];
                $data['plant_area']=$val['I'];
                $data['site_name']=$val['J'];

                $res= M("base_prdruguse")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '农药使用情况';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(349,'导入农药使用情况记录表');
                $this->success("导入成功！", U('Jdproduct/druguse_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdproduct/druguse_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
   * 农药使用情况excel导出
   */
    public function druguse_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_prdruguse");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('drug_name,amount,datetime,weather,operator,place_name,farmer_name,plantinfo,plant_area,site_name')->
        order('id desc')->select();

        Log_add(348,'导出农药使用情况记录认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_prdruguse";
        $headArr=array("农药名称","使用量/亩","日期","天气"
        ,"操作人", "产地名称","农户名称","种植品种","所属乡镇","所属村");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /*
     * 肥料使用情况记录明细列表页
     */
    public function fertilizer_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = trim(I('key_type'));
        $key = trim(I('key'));
        $fertilizer = M('base_prfertilizer');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $fertilizer->where($where)->page($p.',10')->order('id desc')->select();
        $count = M('base_prfertilizer')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        Log_add(350,'访问肥料使用情况记录明细列表页面');
        $nav = 'fertilizer';
        $this->assign('key_type',$key_type);
        $this->assign('nav',$nav);
        $this->display();
    }
    /*
     * 肥料使用情况记录增加与编辑
     */
    public function fertilizer_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_prfertilizer')->where('id='.$id)->find();
            $place = M('place')->where(array('id'=>$id))->find();
            $this->assign('info2',$info2);
            $this->assign('place',$place);
        }
        if(IS_POST)            //新增或编辑
        {
            $data = I('post.');
            $count = count($data);
            foreach($data as $k=>&$v){
                trim($v);
            }
            if(empty($data)){
                $this->error('请输入相关信息！');
            }
            if(!$_POST['id']) {                                 //增加信息
                $add = M('base_prfertilizer')->add($data);
                if ($add) {
                    Log_add(352,'新增肥料使用情况记录成功证明');          //添加log
                    $this->success('添加成功', U('Jdproduct/fertilizer_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdproduct/fertilizer_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_prfertilizer')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(353,'修改肥料使用情况记录成功证明');
                    $this->success('修改成功',U('Jdproduct/fertilizer_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdproduct/fertilizer_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
//            $place_id = I('pose.place_id');
//            $info2 = M('base_place')->field('place_address,plant_size,soil_type,irrigation_water,plant_area,environment,remark')->find();
            Log_add(351,'访问肥料使用情况记录页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    /*
     * 肥料使用情况删除
     */
    public function fertilizer_del(){
        $model = M("base_prfertilizer");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(354,'删除肥料使用情况记录成功证明',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(354,'删除肥料使用情况记录成功证明',$res);
                $this->success("删除成功！", U('Jdproduct/fertilizer_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdproduct/fertilizer_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
        $this->display();
    }
    /*
     * 查看详情
     */
    public function  fertilizer_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_prfertilizer')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }
    /*
     * ajax返回数据
     */
    public function fertilizer_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_address,place_name,farmer_name,soil_type,plant_size,irrigation_water,plant_area,site_name,environment,remark')->where(array('place_id'=>$place_id))->find();    //dump($info);exit;
        if(!empty($info)){
            $model = new \Think\Model();
            $a = 'find_area';
            $b = $info['plant_area'];
            $c = Tb_data($a,$b);
            $d = $c[0]['area_name'];
            $e = $info['site_name'];
            $f = 'select area_name from tb_area where id='."$e";
            $k = $model->query($f);
            $info['site_name'] = $k[0]['area_name'];
            $info['plant_area'] = $d;
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
    * 基础信息excel导入
    */
    public function fertilizer_import(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功

            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];

            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类

            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }

            }
            //需要保存的数据，
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){

                $data['fertilizer_name']=$val['A'];
                $data['amount'] = $val['B'];
                $data['datetime']=$val['C'];
                $data['weather']=$val['D'];
                $data['operator']=$val['E'];
                $data['place_name']=$val['F'];
                $data['farmer_name']=$val['G'];
                $data['plantinfo']=$val['H'];
                $data['plant_area']=$val['I'];
                $data['site_name']=$val['I'];

                $res= M("base_prfertilizer")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '肥料使用情况';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(356,'导入肥料使用情况记录表');
                $this->success("导入成功！", U('Jdproduct/fertilizer_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdproduct/fertilizer_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
   * 农药使用情况excel导出
   */
    public function fertilizer_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_prfertilizer");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('fertilizer_name,amount,datetime,weather,operator,place_name,farmer_name,plantinfo,plant_area,site_name')->
        order('id desc')->select();

        Log_add(355,'导出肥料使用情况记录认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_prfertilizer";
        $headArr=array("肥料名称","使用量/亩","日期","天气"
        ,"操作人", "产地名称","农户名称","种植品种","所属乡镇","所属村");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /*
     *田间农事操作表明细列表页
     */
    public function farmwork_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = trim(I('key_type'));
        $key = trim(I('key'));
        $farmwork = M('base_prfarmwork');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $farmwork->where($where)->page($p.',10')->order('datetime desc')->select();
        //dump($info);exit;
        $count = M('base_prfarmwork')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        Log_add(357,'访问田间农事操作明细列表页面');
        $nav = 'farmwork';
        $this->assign('key_type',$key_type);
        $this->assign('nav',$nav);
        $this->display();
    }
    /*
     *田间农事操作表增加与编辑
     */
    public function farmwork_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_prfarmwork')->where('id='.$id)->find();
            $place = M('place')->where(array('id'=>$id))->find();
            $this->assign('info2',$info2);
            $this->assign('place',$place);
        }
        if(IS_POST)            //新增或编辑
        {
            $data = I('post.');
            $count = count($data);
            foreach($data as $k=>&$v){
                trim($v);
            }
            if(empty($data)){
                $this->error('请输入相关信息！');
            }
            if(!$_POST['id']) {                                 //增加信息
                $add = M('base_prfarmwork')->add($data);
                if ($add) {
                    Log_add(359,'新增田间农事操作成功证明');          //添加log
                    $this->success('添加成功', U('Jdproduct/farmwork_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdproduct/farmwork_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_prfarmwork')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(360,'修改田间农事操作成功证明');
                    $this->success('修改成功',U('Jdproduct/farmwork_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdproduct/farmwork_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
//            $place_id = I('pose.place_id');
//            $info2 = M('base_place')->field('place_address,plant_size,soil_type,irrigation_water,plant_area,environment,remark')->find();
            Log_add(358,'访问田间农事操作页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    /*
    * 查看详情
    */
    public function  farmwork_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_prfarmwork')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }
    /*
     * ajax返回数据
     */
    public function farmwork_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_address,place_name,farmer_name,soil_type,plant_size,irrigation_water,plant_area,site_name,environment,remark')->where(array('place_id'=>$place_id))->find();    //dump($info);exit;
        if(!empty($info)){
            $model = new \Think\Model();
            $a = 'find_area';
            $b = $info['plant_area'];
            $c = Tb_data($a,$b);
            $d = $c[0]['area_name'];
            $e = $info['site_name'];
            $f = 'select area_name from tb_area where id='."$e";
            $k = $model->query($f);
            $info['site_name'] = $k[0]['area_name'];
            $info['plant_area'] = $d;
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
    * 基础信息excel导入
    */
    public function farmwork_import(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功

            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];

            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类

            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }

            }
            //需要保存的数据，
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){

                $data['farm_project']=$val['A'];
                $data['datetime'] = $val['B'];
                $data['operator']=$val['C'];
                $data['place_id']=$val['D'];
                $data['place_name']=$val['E'];
                $data['farmer_name']=$val['F'];
                $data['plantinfo']=$val['G'];
                $data['plant_area']=$val['H'];
                $data['site_name']=$val['I'];

                $res= M("base_prfarmwork")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '田间农事操作';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(363,'导入田间农事操作表');
                $this->success("导入成功！", U('Jdproduct/farmwork_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdproduct/farmwrok_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
   * 农药使用情况excel导出
   */
    public function farmwork_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_prfarmwork");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('farm_project,datetime,operator,place_id,place_name,farmer_name,plantinfo,plant_area,site_name')->
        order('id desc')->select();

        Log_add(362,'导出田间农事操作认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_prfarmwork";
        $headArr=array("农事活动项目","日期","操作人","产地编号"
        ,"产地名称", "农户名称","种植品种","所属乡镇","所属村");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /*
     *田间农事操作表删除
     */
    public function farmwork_del(){
        $model = M("base_prfarmwork");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(361,'删除田间农事操作成功证明',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(361,'删除田间农事操作成功证明',$res);
                $this->success("删除成功！", U('Jdproduct/farmwork_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdproduct/farmwork_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
        $this->display();
    }

    /*
     * 采购记录管理明细列表页
     */
    public function record_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = I('key_type');
        $key = I('key');
        $record = M('base_prrecord');
        $where['is_del'] = 0;
        $qy = $_SESSION['company_id'];
        if(!empty($qy)){
            $where['company_id'] = $qy;
        }
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        
//         ini_set ('memory_limit', '128M');
//         set_time_limit(0);
//        $a = M('base_prbase')->limit(20001,35000)->select();
//        //dump($a);exit;
//        foreach ($a as $k=>&$v){
//            $b = $v['place_id'];
//            $c = M('place')->where(array('place_id'=>$b))->getField('place_name');
//            $v['place_name'] = $c;
//        }
//        //dump($a);exit;
//        foreach ($a as $k1=>$v1){
//            $d = array();
//            $d['place_name'] = $v1['place_name'];
//            M('base_prbase')->where(array('id'=>$v1['id']))->save($d);
//        }
//        exit;

//         $a = M('base_prrecord')->select();
//         foreach($a as $k=>&$v){
//             $b = $v['cpcode'];
//             $c = M('commodity')->where(array('code'=>$b))->getField('name');
//             $v['goods_name'] = $c;
//         }
//         //dump($a);exit;
//        foreach ($a as $k1=>$v1){
//            $d = array();
//            $d['goods_name'] = $v1['goods_name'];
//            M('base_prrecord')->where(array('id'=>$v1['id']))->save($d);
//        }
//        exit;
        
//         $a = M('base_prrecord')->select();
//         foreach($a as $k=>&$v){
//             if($v['operator'] == '447'){
//                 $v['company_id'] = '';
//                 $v['qycode'] = '';
//             }
//             if($v['operator'] == '484'){
//                 $v['company_id'] = '1';
//                 $v['qycode'] = '0001';
//             }
//             if($v['operator'] == '485'){
//                 $v['company_id'] = '3';
//                 $v['qycode'] = '0003';
//             }
//             if($v['operator'] == '486'){
//                 $v['company_id'] = '2';
//                 $v['qycode'] = '0002';
//             }
//             if($v['operator'] == '487'){
//                 $v['company_id'] = '4';
//                 $v['qycode'] = '0004';
//             }
//             if($v['operator'] == '488'){
//                 $v['company_id'] = '5';
//                 $v['qycode'] = '0005';
//             }
//             if($v['operator'] == '489'){
//                 $v['company_id'] = '6';
//                 $v['qycode'] = '0006';
//             }
//             if($v['operator'] == '490'){
//                 $v['company_id'] = '7';
//                 $v['qycode'] = '0007';
//             }
//             if($v['operator'] == '491'){
//                 $v['company_id'] = '8';
//                 $v['qycode'] = '0008';
//             }
//             if($v['operator'] == '494'){
//                 $v['company_id'] = '10';
//                 $v['qycode'] = '0010';
//             }
//         }
//         foreach ($a as $k1=>$v1){
//             $id = '';
//             $id = $v1['batch_id'];
//             $data = array();
//             $data['company_id'] = $v1['company_id'];
//             $data['qycode'] = $v1['qycode'];
//             M('base_prrecord')->where(array('batch_id'=>$id))->save($data);
//         }exit;
      
        
        $p = $_GET['p']?$_GET['p']:1;
        $info = $record->where($where)->page($p.',10')->order('recovery_time desc')->select();
        $count = M('base_prrecord')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        Log_add(364,'访问采收记录表明细列表页面');
        $nav = 'record';
        $this->assign('key_type',$key_type);
        $this->assign('nav',$nav);
        $this->display();
    }
    /*
     *采购记录管理增加与编辑
     */
    public function record_add(){
       
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_prrecord')->where('id='.$id)->find();
            $place = M('place')->where(array('id'=>$id))->find();
            $this->assign('info2',$info2);
            $this->assign('place',$place);
        }
        if(IS_POST)            //新增或编辑
        {
            $data = I('post.');
            $count = count($data);
            foreach($data as $k=>&$v){
                trim($v);
            }
            //dump($data);exit;
            if($_POST) {                                 //增加信息
                $qy = $_SESSION['company_id'];
                if(!empty($qy)){
                     $data['company_id'] = $qy;
                }
                $first = $this->getFirstCharter($data['plant_area']);
                $first2 = $this->getFirstCharter(substr($data['plant_area'],3,3));
                $second = $this->getFirstCharter($data['site_name']);
                $second2 = $this->getFirstCharter(substr($data['site_name'],3,3));
                $time = date('Ymd',time());
                $batch_id = "$first"."$first2"."$second"."$second2"."$time";
                $batch_id = $this->code($batch_id);
                $data['batch_id'] = $batch_id;
                $add = M('base_prrecord')->add($data);
                if ($add) {
                    Log_add(366,'新增采收记录表成功证明');          //添加log
                    $this->success('添加成功', U('Jdproduct/record_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdproduct/record_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $first = $this->getFirstCharter($data['plant_area']);
                $first2 = $this->getFirstCharter(substr($data['plant_area'],3,3));
                $second = $this->getFirstCharter($data['site_name']);
                $second2 = $this->getFirstCharter(substr($data['site_name'],3,3));
                $time = date('Ymd',time());
                $batch_id = "$first"."$first2"."$second"."$second2"."$time";
                $id = $_POST['id'];
                $batch_id  = "$batch_id"."$id";
                $data['batch_id'] = $batch_id;
                                 //编辑信息
                $save = M('base_prrecord')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(367,'修改采收记录表成功证明');
                    $this->success('修改成功',U('Jdproduct/record_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdproduct/record_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
//            $place_id = I('pose.place_id');
//            $info2 = M('base_place')->field('place_address,plant_size,soil_type,irrigation_water,plant_area,environment,remark')->find();
            Log_add(365,'访问采收记录表页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    /*
     *采购记录管理删除
     */
    public function record_del(){
        $model = M("base_prrecord");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(368,'删除采收记录表成功证明',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){
            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(368,'删除采收记录表成功证明',$res);
                $this->success("删除成功！", U('Jdproduct/record_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdproduct/record_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * ajax返回数据
     */
    public function record_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_address,place_name,farmer_name,soil_type,plant_size,irrigation_water,plant_area,site_name,environment,remark')->where(array('place_id'=>$place_id))->find();   //dump($info);exit;
        if(!empty($info)){
            $model = new \Think\Model();
            $a = 'find_area';
            $b = $info['plant_area'];
            $c = Tb_data($a,$b);
            $d = $c[0]['area_name'];
            $e = $info['site_name'];
            $f = 'select area_name from tb_area where id='."$e";
            $k = $model->query($f);
            $info['site_name'] = $k[0]['area_name'];
            $info['plant_area'] = $d;
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
    * 查看详情
    */
    public function  record_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_prrecord')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }
    /*
    * 基础信息excel导入
    */
    public function record_import(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功

            //导入excel的保存路径 位于根目录下的Uploads文件夹
            $filename = './Uploads/'.$info['import']['savepath'].'/'.$info['import']['savename'];

            //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
            import("Org.Util.PHPExcel");
            $PHPExcel=new \PHPExcel();
            //如果excel文件后缀名为.xls，导入这个类
            import("Org.Util.PHPExcel.Reader.Excel5");
            //如果excel文件后缀名为.xlsx，导入这下类

            $PHPReader=new \PHPExcel_Reader_Excel5();
            //载入文件
            $PHPExcel=$PHPReader->load($filename);
            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet=$PHPExcel->getSheet(0);
            //获取总列数
            $allColumn=$currentSheet->getHighestColumn();
            //获取总行数
            $allRow=$currentSheet->getHighestRow();
            //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
            for($currentRow=2;$currentRow<=$allRow;$currentRow++){
                //从哪列开始，A表示第一列
                for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                    //数据坐标
                    $address=$currentColumn.$currentRow;
                    //读取到的数据，保存到数组$arr中
                    $arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
                }

            }
            //需要保存的数据，
            $data=array();
            $num_all = 0;
            $num_suc = 0;
            foreach($arr as $key => $val){

                $data['batch_id']=$val['A'];
                $data['place_id'] = $val['B'];/* 
                $data['operator']=$val['C']; */
                $data['place_name']=$val['D'];
                /* $data['plant_area']=$val['E'];
                $data['site_name']=$val['F']; */
                $data['goods_name']=$val['G'];
                /* $data['area']=$val['H'];
                $data['yield']=$val['I']; */
                $data['plant_time']=$val['I'];
                $data['recovery_time']=$val['I'];

                $res= M("base_prrecord")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '采收记录';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(370,'导入采收记录表');
                $this->success("导入成功！", U('Jdproduct/record_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdproduct/record_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
   * 农药使用情况excel导出
   */
    public function record_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_prrecord");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('batch_id,place_id,place_name,goods_name,recovery_time')->
        order('id desc')->select();

        Log_add(369,'导出采收记录表认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_prrecord";
        $headArr=array("采收批次","产地编号","产地名称"
        ,"产品名称","种植日期","采收日期");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    public function getFirstCharter($str){
        if(empty($str)){return '';}
        $fchar=ord($str{0});
        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';
        return null;
    }
    public function code($str){
        $res = M('base_prrecord')->field('id')->order('id desc')->find();
        $id = $res['id'];
        $id++;
        $str = "$str"."$id";
        return $str;
    }

}

