<?php
/**
 * 同江绿色食品管理平台
 * 认证管理控制器
 * Author: dalely
 * Date: 2016-12-3
 */
namespace Admin\Controller;
use Think\Controller;

class ProveController extends BaseController {

   /*
     * 产地证明列表
     */
    public function index(){
      $this->origin();
    }
    /*
     * 产地证明列表
     */
    public function origin(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        $map['is_del']=0;
        $model=M("prove_origin");
        $key_type=I('key_type');
        $key=trim(I('key'));
        if($key_type){
            switch ($key_type){
                //删除标签
                case $key_type == "1":
                    $where['number'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['certificate_time'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['origin_num'] = array('like', "%{$key}%");
                    break;
                case $key_type == "4":
                    $where['origin'] = array('like', "%{$key}%");
                    break;
                case $key_type == "5":
                    $where['producers'] = array('like', "%{$key}%");
                    break;
                case $key_type == "6":
                    $where['product_name'] = array('like', "%{$key}%");
                    break;
                case $key_type == "7":
                    $where['quantity'] = array('like', "%{$key}%");
                    break;
                case $key_type == "8":
                    $where['marketing_agency'] = array('like', "%{$key}%");
                    break;
                case $key_type == "9":
                    $where['tel'] = array('like', "%{$key}%");
                    break;
                case $key_type == "10":
                    $where['agent'] = array('like', "%{$key}%");
                    break;
            }
        }
        $where['is_del']=0;
        $page=isset($_GET['p'])?$_GET['p']:1;
        $list = $model->where($where)->order('id desc')->page($page.',14')->select();
        $this->assign('info_list',$list);// 赋值数据集
        $count      = $model->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,14);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);// 赋值分页输出*/
        $this->assign('key',$key);// 赋值分页输出*/
        $this->assign('page',$show);// 赋值分页输出*/
        Log_add(401,'访问产地证明列表页面');
        $this->display();
    }
    //添加-编辑产地证明
    public function add_origin(){
        $model = M("Prove_origin");
        if($_GET['id']){
            $origin = $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("origin",$origin);
        }
        if(IS_POST){
            $data = I("post.");

            $file = $_FILES['picture'];
            if($file['size'] > 0) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 3145728;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath = './Uploads/'; // 设置附件上传根目录
                $upload->savePath = ''; // 设置附件上传（子）目录
                // 上传文件
                $info = $upload->upload();
                if (!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                } else {// 上传成功

                    $filename = '/Uploads/' . $info['picture']['savepath'] . '/' . $info['picture']['savename'];
                    $data['picture'] = $filename;
                }
            }
            if($_POST['id']){
                $res = $model->where(array('id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(406,'修改产地证明',$_POST['id']);
                    $this->success("修改成功！", U('Prove/origin',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请稍后再试！", U('Prove/origin',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $res = $model->add($data);
                if($res){
                    $this->success("新增成功！", U('Prove/origin',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请稍后再试！", U('Prove/origin',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(402,'访问新增产地证明页');
            $this->display();
        }
    }
     //detail详细产地证明
    public function detail_origin(){
        $model = M("Prove_origin");
        if($_GET['id']){
            $origin = $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("origin",$origin);
        }
        Log_add(443,'访问详细产地证明页');
            $this->display();
    }
    /**
     * 删除产地证明数据
     */
    public function del_origin(){
        $model = M("Prove_origin");
        $data['is_del']=1;
        if($_POST['ids']){

            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(403,'删除产地证明',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(403,'删除产地证明',$res);
                $this->success("删除成功！", U('Prove/origin',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Prove/origin',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /**
     * 导入产地证明数据
     */
    public function import(){
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
            foreach($arr as $key => $val){

                $data['number']=$val['A'];    //编号
                $data['origin']=$val['B'];    //产地（乡,村）
                $data['origin_num']=$val['C'];//产地编号
                $data['agent']=$val['D'];   //经办人
                $data['producers']=$val['E'];//生产者
                $data['quantity']=$val['F'];//数量(吨)
                $data['detection_type']=$val['G'];//质量检测类型
                $data['detection_result']=$val['H'];//质量检测结果
                $data['marketing_agency']=$val['I'];//运销商
                $data['certificate_time']=$val['J'];//发证日期
                $data['product_name']=$val['K'];//产品名称
                $data['gain_time']=$val['L'];//收获日期
                $data['tel']=$val['M'];//电话

               $res= M("prove_origin")->add($data);
            }
            if($res){
                   Log_add(404,'导入产地证明表');
                   $this->success("导入成功！", U('Prove/origin',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Prove/origin',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }

    /**
     * 导出产地证明数据
     */
    public function excelout(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("prove_origin");
        $key_type=I('key_type');
        $key=trim(I('key'));
        if($key_type){
            switch ($key_type){
                case $key_type == "1":
                    $where['number'] = array('like', "%{$key}%");
                    break;
                case $key_type == "2":
                    $where['certificate_time'] = array('like', "%{$key}%");
                    break;
                case $key_type == "3":
                    $where['origin_num'] = array('like', "%{$key}%");
                    break;
                case $key_type == "4":
                    $where['origin'] = array('like', "%{$key}%");
                    break;
                case $key_type == "5":
                    $where['producers'] = array('like', "%{$key}%");
                    break;
                case $key_type == "6":
                    $where['product_name'] = array('like', "%{$key}%");
                    break;
                case $key_type == "7":
                    $where['quantity'] = array('like', "%{$key}%");
                    break;
                case $key_type == "8":
                    $where['marketing_agency'] = array('like', "%{$key}%");
                    break;
                case $key_type == "9":
                    $where['tel'] = array('like', "%{$key}%");
                    break;
                case $key_type == "10":
                    $where['agent'] = array('like', "%{$key}%");
                    break;
            }
        }
        $where['is_del']=0;
        $data= $model->where($where)->field('id,is_del,addtime',true)->order('id desc')->select();//排除is_del,addtime字段

        Log_add(405,'导出产地证明数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="产地证明";
        $headArr=array("编号","产地(乡、村)","产地编号","经办人","生产者","数量(吨)","检测类型","质量检测结果","运销商","发证日期","产品名称","收获日期","电话","图片");
        downloadExcel($filename,$headArr,$data);//导出数据到excel

    }

    /**
     * 产品认证管理
     */
    public function product(){
        $this->assign("menu_id",Detection_menu());
        $map['is_del']=0;
        $model=M("prove_product");
        $key_type=I('key_type');
        $key=trim(I('key'));
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;
        $page=isset($_GET['p'])?$_GET['p']:1;
        $list = $model->where($where)->order('id desc')->page($page.',14')->select();
        $this->assign('info_list',$list);// 赋值数据集
        $count      = $model->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,14);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $this -> assign('count',$count);
        $this->assign('key_type',$key_type);// 赋值分页输出*/
        $this->assign('key',$key);// 赋值分页输出*/
        $this->assign('page',$show);// 赋值分页输出*/
        Log_add(401,'访问产地证明列表页面');
        $this->display();
    }

    /**
     * 添加修改产品认证管理
     */
    public function add_product(){
        $model = M("Prove_product");
        if($_GET['id']){
            $product= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("product",$product);
        }
        if(IS_POST){
            $data = I("post.");

            $file = $_FILES['picture'];
            if($file['size'] > 0) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 3145728;// 设置附件上传大小
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath = './Uploads/'; // 设置附件上传根目录
                $upload->savePath = ''; // 设置附件上传（子）目录
                // 上传文件
                $info = $upload->upload();
                if (!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                } else {// 上传成功

                    $filename = '/Uploads/' . $info['picture']['savepath'] . '/' . $info['picture']['savename'];
                    $data['picture'] = $filename;
                }
            }
            if($_POST['id']){
                $res = $model->where(array('id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(407,'修改产品认证',$_POST['id']);
                    $this->success("修改成功！", U('Prove/product',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请稍后再试！", U('Prove/product',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $res = $model->add($data);
                if($res){
                    $this->success("新增成功！", U('Prove/product',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请稍后再试！", U('Prove/product',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(408,'访问新增产品认证页');
            $this->display();
        }
    }
 /**
     * 详细产品认证管理
     */
    public function detail_product(){
        $model = M("Prove_product");
        if($_GET['id']){
            $product= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("product",$product);
        }
        Log_add(444,'访问详细产品认证页');
        $this->display();
    }

    /**
     * 删除产品认证信息
     */
    public function del_product(){
        $model = M("Prove_product");
        $data['is_del']=1;
        if($_POST['ids']){
             $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(409,'删除产品认证信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(409,'删除产品认证信息',$res);
                $this->success("删除成功！", U('Prove/product',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请稍后再试！", U('Prove/product',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /**
     * 导出产品认证信息
     */
    public function excelout_product(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("prove_product");
        $key_type=I('key_type');
        $key=trim(I('key'));
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;

        $data= $model->where($where)->field('id,is_del,addtime',true)->order('id desc')->select();

        Log_add(410,'导出产品认证数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="产品认证";
        $headArr=array("认证类型","认证类别","企业编号","企业名称","企业信息码","企业地址","生产基地","产品编号","产品种类","产品名称","证书编号","发证日期","有效期","产量（吨）","注册商标","图片");
        downloadExcel($filename,$headArr,$data);//导出数据到excel

    }
    /**
     * 导入产品认证数据
     */
    public function import_product(){
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
            foreach($arr as $key => $val){

                $data['prove_type']=$val['A'];    //认证类型
                $data['prove_category']=$val['B'];    //认证类别
                $data['company_num']=$val['C'];//企业编号
                $data['company_name']=$val['D'];   //企业名称
                $data['company_code']=$val['E'];//企业信息码
                $data['company_address']=$val['F'];//企业地址
                $data['producers']=$val['G'];//生产基地
                $data['product_num']=$val['H'];//产品编号
                $data['product_type']=$val['I'];//产品类型
                $data['product_name']=$val['J'];//产品名称
                $data['certificate_num']=$val['K'];//证书编号
                $data['certificate_time']=$val['L'];//发证日期
                $data['validity_time']=$val['M'];//有效期
                $data['yield']=$val['N'];//产量（吨）
                $data['brand']=$val['O'];//注册商标
                $data['picture']=$val['P'];//图片

                $res= M("prove_product")->add($data);
            }
            if($res){
                Log_add(411,'导入产品认证表');
                $this->success("导入成功！", U('Prove/product',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请稍后再试", U('Prove/product',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
}