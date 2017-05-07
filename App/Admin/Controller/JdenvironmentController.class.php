<?php
/**
 * 同江绿色食品管理平台
 * 认证管理控制器
 * Author: dalely
 * Date: 2016-12-3
 */
namespace Admin\Controller;
use Think\Controller;

class JdenvironmentController extends BaseController {
    public function worm_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = I('key_type');
        $key = I('key');
        $air = M('base_enworm');
        $place = M('place');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $air->where($where)->page($p.',10')->order('id desc')->select();
        $count = M('base_enworm')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        $this->assign('key_type',$key_type);
        Log_add(375,'访问病虫害信息明细列表页面');
        $this->display();
    }
    public function worm_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_enworm')->where('id='.$id)->find();
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
               
                $add = M('base_enworm')->add($data);
                if ($add) {
                    Log_add(377,'新增病虫害信息成功证明');          //添加log
                    $this->success('添加成功', U('Jdenvironment/worm_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdenvironment/worm_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_enworm')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(378,'修改病虫害信息成功证明');
                    $this->success('修改成功',U('Jdenvironment/worm_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdenvironment/worm_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
            Log_add(376,'访问新增病虫害页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    public function  worm_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_enworm')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }
    public function worm_del(){
        $model = M("base_enworm");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(379,'删除病虫害信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){
    
            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(379,'删除产品认证信息',$res);
                $this->success("删除成功！", U('Jdenvironment/worm_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdenvironment/worm_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    public function worm_import(){
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
    
                $data['place_id']=$val['A'];   
                $data['place_name'] = $val['B'];   
                $data['name']=$val['C'];
                $data['type']=$val['D'];  
                $data['normal']=$val['E'];
                $data['time']=$val['F'];
    
                $res= M("base_enworm")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '病虫害信息';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(381,'导入空气质量表');
                $this->success("导入成功！", U('Jdenvironment/worm_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdenvironment/worm_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 空气质量excel导出
     */
    public function worm_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_enworm");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('place_id,place_name,name,type,normal,time')->
        order('id desc')->select();
    
        Log_add(380,'导出病害虫信息认证数据');
    
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_enair";
        $headArr=array("产地编号","产地名称","病虫害名称","类型"
            ,"正常情况", "检测时间");
            downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    
    /*
     * 土壤质量明细
     * @$place  object  基础-产地信息表
     * @$p      int     获取页数
     * @$info   array   联合查询列表详情
     * @$page   object  实例化分页类 传入总记录数和每页显示的记录数
     * @log_add function记录log
     */
    public function index(){
    }
    /*
     * 空气质量明细
     * @$place  object  基础-产地信息表
     * @$p      int     获取页数
     * @$info   array   联合查询列表详情
     * @$page   object  实例化分页类 传入总记录数和每页显示的记录数
     * @log_add function记录log
     */
    public function air_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = trim(I('key_type'));
        $key = trim(I('key'));
        $air = M('base_enair');
        $place = M('place');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $air->where($where)->page($p.',10')->order('id desc')->select();
        $count = M('base_enair')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        $this->assign('key_type',$key_type);
        Log_add(301,'访问空气质量明细列表页面');
        $this->display();
    }
    /*
     * 空气质量添加
     * @$info   array    通过输入的place_id 获取 place_name
     * Log_add  function
     */
    public function air_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_enair')->where('id='.$id)->find();
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
                $add = M('base_enair')->add($data);
                if ($add) {
                    Log_add(303,'新增空气质量成功证明');          //添加log
                    $this->success('添加成功', U('Jdenvironment/air_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdenvironment/air_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_enair')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(304,'修改空气质量成功证明');
                    $this->success('修改成功',U('Jdenvironment/air_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdenvironment/air_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            Log_add(302,'访问新增空气质量页');
            $this->display();
        }
    }
    /*
     * 查看详情
     */
    public function  air_detail(){
    if($_GET['id']){
        $id = I('get.id');
        $info2 = M('base_enair')->where('id='.$id)->find();
        $this->assign('info2',$info2);
        $this->display();
        }
    }
    /*
     * ajax返回数据
     */
    public function air_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_name')->where(array('place_id'=>$place_id))->find();
        if(!empty($info)){
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
     * 空气质量删除
     */
    public function air_del(){
        $model = M("base_enair");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(305,'删除产品认证信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(305,'删除产品认证信息',$res);
                $this->success("删除成功！", U('Jdenvironment/air_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdenvironment/air_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 空气质量excel导入
     */
    public function air_import(){
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

                $data['place_id']=$val['A'];    //产地编号
                $data['place_name'] = $val['B'];    //产地名称
                $data['total_particulate']=$val['C'];//总悬浮颗粒物（标准状态）
                $data['so2_day']=$val['D'];   //二氧化硫（SO2）日均（标准状态）
                $data['so2_hour']=$val['E'];//二氧化硫（SO2）1h均（标准状态）
                $data['compound']=$val['F'];//氮氧化合物
                $data['f']=$val['G'];//氟化物(F-)
                $data['pb']=$val['H'];//铅(Pb)(标准状态)
                $data['pm10']=$val['I'];//PM10
                $data['test_firm']=$val['J'];//检测机构
                $data['test_time']=$val['K'];//检测时间
                $data['test_evaluate']=$val['L'];//检测评价

                $res= M("base_enair")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '空气质量';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(307,'导入空气质量表');
                $this->success("导入成功！", U('Jdenvironment/air_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdenvironment/air_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 空气质量excel导出
     */
    public function air_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_enair");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('place_id,place_name,total_particulate,so2_day,so2_hour,compound,f,pb,pm10,test_firm,test_time,test_evaluate')->
        order('id desc')->select();

        Log_add(306,'导出空气质量认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_enair";
        $headArr=array("产地编号","产地名称","总悬浮颗粒物（标准状态）","二氧化硫（SO2）日均（标准状态）"
        ,"二氧化硫（SO2）1h均（标准状态）", "氮氧化合物","氟化物(F-)","铅(Pb)(标准状态)","PM10","检测机构",
            "检测时间","检测评价");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }

    /*
     * 土壤质量明细
     * @$place  object  基础-产地信息表
     * @$p      int     获取页数
     * @$info   array   联合查询列表详情
     * @$page   object  实例化分页类 传入总记录数和每页显示的记录数
     * @log_add function记录log
     */
    public function soil_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = trim(I('key_type'));
        $key = trim(I('key'));
        $soil = M('base_ensoil');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $soil->where($where)->page($p.',10')->order('id desc')->select();
        //dump($info);exit;
        $count = M('base_ensoil')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        $this->assign('key_type',$key_type);
        Log_add(308,'访问土壤质量明细列表页面');
        $this->display();
    }
    /*
     *土壤质量添加与编辑
     */
    public function soil_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_ensoil')->where('id='.$id)->find();
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
//            $place = M('place');
//            $where = array();
//            $where = $data['place_id'];
//            $info = $place->field('place_name')->where($where)->find();
//
            if(empty($data)){
                $this->error('请输入相关信息！');
            }
//            if(empty($info)){
//                $this->error('产地信息不存在，请检查产地编号！');
//            }
            if(!$_POST['id']) {                                 //增加信息
                $add = M('base_ensoil')->add($data);
                if ($add) {
                    Log_add(310,'新增土壤质量成功证明');          //添加log
                    $this->success('添加成功', U('Jdenvironment/soil_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdenvironment/soil_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_ensoil')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(311,'修改土壤质量成功证明');
                    $this->success('修改成功',U('Jdenvironment/soil_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdenvironment/soil_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
            Log_add(309,'访问新增土壤空气质量页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    /*
     * 土壤质量删除
     */
    public function soil_del(){
        $model = M("base_ensoil");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(312,'删除产品认证信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(312,'删除产品认证信息',$res);
                $this->success("删除成功！", U('Jdenvironment/soil_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdenvironment/soil_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 查看详情
     */
    public function  soil_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_ensoil')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }
    /*
     * ajax
     */
    public function soil_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_name')->where(array('place_id'=>$place_id))->find();
        if(!empty($info)){
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
    * 土壤质量excel导入
    */
    public function soil_import(){
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

                $data['place_id']=$val['A'];    //产地编号
                $data['place_name'] = $val['B'];    //产地名称
                $data['soil_ph']=$val['C'];//总悬浮颗粒物（标准状态）
                $data['cd']=$val['D'];   //二氧化硫（SO2）日均（标准状态）
                $data['hg']=$val['E'];//二氧化硫（SO2）1h均（标准状态）
                $data['as']=$val['F'];//氮氧化合物
                $data['cu']=$val['G'];//氟化物(F-)
                $data['test_time']=$val['H'];//铅(Pb)(标准状态)

                $res= M("base_ensoil")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '土壤质量';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(314,'导入土壤质量表');
                $this->success("导入成功！", U('Jdenvironment/soil_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdenvironment/soil_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 土壤质量excel导出
     */
    public function soil_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_ensoil");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('place_id,place_name,soil_ph,cd,hg,as,cu,test_time')->
        order('id desc')->select();

        Log_add(313,'导出土壤质量认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_ensoil";
        $headArr=array("产地编号","产地名称","土壤pH值","镉（Cd）"
        ,"汞（Hg）", "砷（As）","铜（Cu）","检测时间");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /*
     * 土壤农药残留量明细列表页
     */
    public function residue_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = trim(I('key_type'));
        $key = trim(I('key'));
        $residue = M('base_enresidue');
        $place = M('place');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $residue->where($where)->page($p.',10')->order('id desc')->select();
        //dump($info);exit;
        $count = M('base_enresidue')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        $this->assign('key_type',$key_type);
        Log_add(315,'访问土壤农药残留量明细列表页面');
        $this->display();
    }
    /*
     *土壤农药残留量添加与编辑
     */
    public function residue_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_enresidue')->where('id='.$id)->find();
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
            //$data['place_name'] = $info['place_name'];                      //将place_name写入$data中
//            if(!empty($info)){                                              //存在place_id ajax返回 产地名称
//                $res = array('status'=>0,'msg'=>$info);
//                $this->ajaxReturn($res);
//            }else{
//                $res = array('status'=>0,'msg'=>'没有找到相关信息，请确认产地编号是否正确！');   //place_id 不存在 ajax返回错误
//                $this->ajaxReturn($res);
//            }
            if(empty($data)){
                $this->error('请输入相关信息！');
            }
//            if(empty($info)){
//                $this->error('产地信息不存在，请检查产地编号！');
//            }
            if(!$_POST['id']) {                                 //增加信息
                $add = M('base_enresidue')->add($data);
                if ($add) {
                    Log_add(317,'新增农药残留量成功证明');          //添加log
                    $this->success('添加成功', U('Jdenvironment/residue_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdenvironment/residue_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_enresidue')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(318,'修改土壤农药残留量成功证明');
                    $this->success('修改成功',U('Jdenvironment/residue_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdenvironment/residue_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
            Log_add(316,'访问新增农药残留量页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    /*
     * 土壤农药残留量删除
     */
    public function residue_del(){
        $model = M("base_enresidue");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(319,'删除农药残留量成功证明',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(319,'删除农药残留量成功证明',$res);
                $this->success("删除成功！", U('Jdenvironment/residue_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdenvironment/residue_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 查看详情
     */
    public function  residue_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_enresidue')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }
    /*
     * ajax
     */
    public function residue_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_name')->where(array('place_id'=>$place_id))->find();
        if(!empty($info)){
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
     * 灌溉水质量明细列表页
     */
    /*
     * /*
    * 土壤质量excel导入
    */
    public function residue_import(){
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

                $data['place_id']=$val['A'];
                $data['place_name'] = $val['B'];
                $data['chlorimuron']=$val['C'];
                $data['imazethapyr']=$val['D'];
                $data['omethoate']=$val['E'];
                $data['pretilachlor']=$val['F'];
                $data['test_time']=$val['G'];

                $res= M("base_enresidue")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '土壤农药残留量';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(321,'导入农药残留量表');
                $this->success("导入成功！", U('Jdenvironment/residue_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdenvironment/residue_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 土壤农药残留量excel导出
     */
    public function residue_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_enresidue");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('place_id,place_name,chlorimuron,imazethapyr,omethoate,pretilachlor,test_time')->
        order('id desc')->select();

        Log_add(320,'导出农药残留量认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_enresidue";
        $headArr=array("产地编号","产地名称","豆磺隆","普施特"
        ,"氧乐果", "丙草胺","检测时间");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /*
     * 灌溉水质量
     */
    public function irrigation_index(){
        $this->assign("menu_id",Detection_menu());
        $key_type = trim(I('key_type'));
        $key = trim(I('key'));
        $irrigation = M('base_enirrigation');
        $place = M('place');
        $where['is_del'] = 0;
        if($key_type){
            $where[$key_type] = array('like',"%{$key}%");
        }
        $p = $_GET['p']?$_GET['p']:1;
        $info = $irrigation->where($where)->page($p.',10')->order('id desc')->select();
        $a = $irrigation->count();
        //dump($info);exit;
        $count = M('base_enirrigation')->where($where)->count();
        $page       = new \Think\Page($count,10);
        $this->assign('page',$page->show());
        $this->assign('info',$info);
        $this->assign('key_type',$key_type);
        Log_add(322,'访问灌溉水质量明细列表页面');
        $this->display();
    }
    /*
     * 灌溉水质量增加与编辑
     */
    public function irrigation_add(){
        if($_GET['id']){            //列表详情页 进入 编辑-增加页面
            $id = I('get.id');
            $info2 = M('base_enirrigation')->where('id='.$id)->find();
            $this->assign('info2',$info2);
        }
        if(IS_POST)            //新增或编辑
        {
            $data = I('post.');
            $count = count($data);
            foreach($data as $k=>&$v){
                trim($v);
            }
           /* $place = M('place');
            $where = array();
            $where = $data['place_id'];
            $info = $place->field('place_name')->where($where)->find();*/
            //$data['place_name'] = $info['place_name'];                      //将place_name写入$data中
//            if(!empty($info)){                                              //存在place_id ajax返回 产地名称
//                $res = array('status'=>0,'msg'=>$info);
//                $this->ajaxReturn($res);
//            }else{
//                $res = array('status'=>0,'msg'=>'没有找到相关信息，请确认产地编号是否正确！');   //place_id 不存在 ajax返回错误
//                $this->ajaxReturn($res);
//            }
            if(empty($data)){
                $this->error('请输入相关信息！');
            }
//            if(empty($info)){
//                $this->error('产地信息不存在，请检查产地编号！');
//            }
            if(!$_POST['id']) {                                 //增加信息
                $add = M('base_enirrigation')->add($data);
                if ($add) {
                    Log_add(324,'新增灌溉水质量成功证明');          //添加log
                    $this->success('添加成功', U('Jdenvironment/irrigation_index',array('menu_id'=>$_GET['menu_id'])));
                } else {
                    $this->error('添加失败,请重试',U('Jdenvironment/irrigation_add',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{                                              //编辑信息
                $save = M('base_enirrigation')->where(array('id'=>$id))->save($data);
                if($save){
                    Log_add(325,'修改灌溉水质量成功证明');
                    $this->success('修改成功',U('Jdenvironment/irrigation_index',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error('没有更改任何数据，修改失败',U('Jdenvironment/irrigation_add',array('id'=>$id,'menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{                                                  //访问新增页面
            Log_add(323,'访问新增灌溉水质量页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }
    /*
     * 灌溉水质量删除
     */
    public function irrigation_del(){
        $model = M("base_enirrigation");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(326,'删除灌溉水质量成功证明',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(326,'删除灌溉水质量成功证明',$res);
                $this->success("删除成功！", U('Jdenvironment/r_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Jdenvironment/residue_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 查看详情
     */
    public function  irrigation_detail(){
        if($_GET['id']){
            $id = I('get.id');
            $info2 = M('base_enirrigation')->where('id='.$id)->find();
            $this->assign('info2',$info2);
            $this->display();
        }
    }
    /*
     * ajax
     */
    public function irrigation_ajax(){
        $place_id = I('post.place_id');
        $info = M('place')->field('place_name')->where(array('place_id'=>$place_id))->find();
        if(!empty($info)){
            $res = array('status'=>'1','msg'=>$info);
            exit(json_encode($res));
        }else{
            $res = array('status'=>'0','msg'=>'产地编号输入有误，请重试');
            exit(json_encode($res));
        }
    }
    /*
   * 土壤质量excel导入
   */
    public function irrigation_import(){
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

                $data['ph']=$val['A'];
                $data['petroleum'] = $val['B'];
                $data['phenol']=$val['C'];
                $data['place_id']=$val['D'];
                $data['place_name']=$val['E'];
                $data['test_time']=$val['F'];

                $res= M("base_enirrigation")->add($data);
                if($res){
                    $num_suc++;
                }
                $num_all++;
            }
            if($res){
                $user_name = $_SESSION['account'];
                $explain = '灌溉水质量';
                import_log($user_name,$explain,$num_all,$num_suc);
                Log_add(328,'导入灌溉水质量表');
                $this->success("导入成功！", U('Jdenvironment/irrigation_index',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Jdenvironment/irrigation_index',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /*
     * 灌溉水质量excel导出
     */
    public function irrigation_excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("base_enirrigation");
        $key_type=I('key_type');
        $key=I('key');
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('ph,petroleum,phenol,place_id,place_name,test_time')->
        order('id desc')->select();

        Log_add(327,'导出灌溉水质量认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="base_enirrigation";
        $headArr=array("pH值","石油类","挥发酚","产地编号"
        ,"产地名称", "检测时间");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    
    public function place_info(){
        $m = $_GET['m'];
       
        $Model = new \Think\Model();
        switch ($m){
            case $m == "place_info":
                $place_name =$_POST['plant_area'];
              
                $result = $Model->query("SELECT id,place_id,place_name FROM ims_place WHERE place_id = $place_name");
//                 p($result);
                output_data($result);
                break;
            
        }
    }
}
