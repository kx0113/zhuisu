<?php
/*
* user role表联查
* by King
* 2016-11-08
*/
function user_find(){
    $user = M("sys_user")->alias('u')
        ->join('ims_sys_user_role as r on u.role_id = r.id')
        ->where(array('u.user_id'=>$_SESSION['user_id']))
        ->field('u.user_id,u.account,u.role_id,u.status,r.name')
        ->find();
    return $user;
}

/*
* 查询所有role表
* by King
* 2016-11-10
*/
function user_role(){
    $user_role = M("sys_user_role")->where(array('status'=>1))->select();
    return $user_role;
}

/*
* ajax
* by King
* 2016-11-18
*/
function js_ajaxReturn($res){
    if ($res) {
        $json['res'] = 'success';
    } else {
        $json['res'] = 'error';
    }
    $this->ajaxReturn($json);
}
/*
* tag
* by King
* 2016-11-18
*/
function Tag_list($tag_id,$type){
    $model = M("sys_tag");
    if($type == 'data'){
        $Tag_list = $model->where(array('p_id'=>$tag_id))->field('tag_id,p_id,name,remark')->select();
    }else{
        $Tag_list = $model->where(array('tag_id'=>$tag_id))->field('tag_id,name,remark')->select();
        foreach($Tag_list as $k=>$v){
            $Tag = $model->where(array('p_id'=>$v['tag_id']))->field('tag_id,p_id,name,remark')->select();
            $Tag_list[$k]['data'] = $Tag;
        }
    }
    return $Tag_list;
}


/*
* 区域 地块 设备信息
* by King
* 2016-11-18
*/
function Tb_data($type,$id){
    $Model = new \Think\Model();
    switch ($type){
        //select区域全部
        case $type == "area":
            $Tb_data_area = $Model->query("SELECT id,area_name FROM tb_area WHERE parent_id=0");
            return $Tb_data_area;
            break;
        //条件查询单个区域
        case $type == "find_area":
            $Tb_data_area = $Model->query("SELECT id,area_name FROM tb_area WHERE id=$id");
            return $Tb_data_area;
            break;
        //地块
        case $type == "site_name":
            $Tb_data_site_name = $Model->query("SELECT id,area_name FROM tb_area WHERE parent_id=$id");
            return $Tb_data_site_name;
            break;
        //设备信息
        case $type == "device_info":
            $Tb_data_device = $Model->query("SELECT * FROM tb_device WHERE id = $id");
            return $Tb_data_device;
            break;
    }
}
/*
* 时期
* by King
* 2016-11-20
* $product_id   主表id
*/
function Stage_list($product_id){
    $product_ = M("product") ->where(array('product_id'=>$product_id))->find();
    $Stage = M("sys_stage") ->where(array('tag_id'=>$product_['pro_type']))->select();
    return $Stage;
}
/*
* 承包人
* by King
* 2016-11-20
* $contractor_id
*/
function Contractor($contractor_id){
    $model = M("contractor");
    if($contractor_id){
        $contractor =$model->where(array("id"=>$contractor_id))->find();
    }else{
        $contractor = $model->select();
    }
    return $contractor;
}
/*
* 行为跟踪写入
* by King
* 2016-12-01
* $type    编号
* $source  来源
* $value1  备用值1
* $value2  备用值2
*/
function Log_add($type,$source,$value1='',$value2=''){
    if($type ==''){
        exit("参数错误!");
    }if($source ==''){
        exit("参数错误!");
    }
    $data['ip'] = $_SERVER['REMOTE_ADDR'];
    $data['user_id'] = $_SESSION['user_id'];
    $data['account'] = $_SESSION['account'];
    $data['value3'] = $_SERVER['SERVER_NAME'];
    $data['value4'] = $_SERVER['DOCUMENT_ROOT'];
    $data['value1'] = trim($value1);
    $data['value2'] = trim($value2);
    $data['type'] = $type;
    $data['source'] = $source;
    $data['time'] = date('Y-m-d H-i-s',time());
    $log_model =  M("sys_log")->add($data);
}

function downloadExcel($fileName,$headArr,$data){
    //对数据进行检验
    if(empty($data) || !is_array($data)){
        die("data must be a array");

    }
    //检查文件名
    if(empty($fileName)){
        exit;
    }

    $date = date("Y_m_d",time());
    $fileName .= "_{$date}.xls";

    //创建PHPExcel对象，注意，不能少了\
    $objPHPExcel = new \PHPExcel();
    $objProps = $objPHPExcel->getProperties();

    //设置表头
    $key = ord("A");
    foreach($headArr as $v){
        $colum = chr($key);
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
        $key += 1;
    }

    $column = 2;
    $objActSheet = $objPHPExcel->getActiveSheet();
    foreach($data as $key => $rows){ //行写入
        $span = ord("A");
        foreach($rows as $keyName=>$value){// 列写入
            $j = chr($span);
            $objActSheet->setCellValue($j.$column, $value);
            $span++;
        }
        $column++;
    }

    $fileName = iconv("utf-8", "gb2312", $fileName);
    //重命名表
    // $objPHPExcel->getActiveSheet()->setTitle('test');
    //设置活动单指数到第一个表,所以Excel打开这是第一个表
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output'); //文件通过浏览器下载
    exit;
}
/*
* 二维数组去除重复字段值
* by King
* 2016-12-05
* */
function a_array_unique($array){
    $out = array();
    foreach ($array as $key=>$value) {
        if (!in_array($value, $out)) {
            $out[$key] = $value;
        }
    }
    return $out;
}
/*
* 多个二维数组，转换成字符串，用逗号隔开
* by King
* 2016-12-05
* $array_list  输入数组
* $field       需要转换字段
*/
function switch_($array_list,$field){
    $sum = 0;
    $count = count($array_list);
    for($i = 0; $i < $count; $i++){
        $sum .= $array_list[$i][$field].",";
    }
    $sum = substr($sum,1,-1);
    return $sum;
}
/*
* 检测该菜单下增删改查权限
* by King
* 2016-12-07
* 必传参数  mune_id = $_GET['munu_id']
*/
function Detection_menu(){
    $menu_id = $_REQUEST['menu_id'];
    if(!$menu_id){
        exit("缺少参数menu_id!");
    }
    if($menu_id){
        //超级管理员用户
        if($_SESSION['account'] == 'admin' || $_SESSION['account'] == 'King'){
            $menu = 'admin';
        }else{
            //如果是分配权限用户
            if($menu_id){
                /*
                 * *******开发测试使用*********
                 * 开发测试使用 直接读取数据库  用户权限值
                 * */

                $menu = M()->query("SELECT val FROM ims_sys_user_role_govern WHERE role_id = $_SESSION[role_id] AND menu_id = $menu_id");
                //调用转换字符串函数
                $menu = switch_($menu,'val');
                $menu = explode(',',$menu);
                $menu =array('val'=>$menu);

                /*
                * 读取$_SESSION  用户权限值
                * */
//                $menu = array();
//                foreach($_SESSION['role_govern'] as $value){
//                    if($value['menu_id'] == $menu_id){
//                        array_push($menu,$value['val']);
//                    }
//                }
//                $menu =array('val'=>$menu);
//                p($menu);
            }
        }
        return $menu;
    }

}
/* 
 * 导入记录函数
 *  by Rocky
 *  2016-12-6
 *  
 *  */
function import_log($username,$explain,$num_all = 0,$num_suc = 0){
    $data = array();
    $data['name'] = $explain;
    $data['username'] = $username;
    $data['num_all'] = $num_all;
    $data['num_suc'] = $num_suc;
    $data['addtime'] = date('Y-m-d H:i:s');
    $data['status'] = 1;
    $m = M('base_inlog');
    if ($m->add($data)){
        return true;
    }else{
        return false;
    }
}
/*
* 全部企业信息
* by King
* 2016-12-07
*/
function Company_list($company_id){
    $model = M("sys_company");
    if($company_id){
        $company_list = $model->where(array('company_id'=>$company_id))->find();
    }else{
        $company_list = $model->where(array('status'=>1))->select();
    }
    return $company_list;
}
/*
* API输出
* by King
* 2016-12-12
* 输出json格式
*/
function output_data($datas, $extend_data = array()) {
    $data = array();
    $data['error'] = '0';

    if(!empty($extend_data)) {
        $data = array_merge($data, $extend_data);
    }

    $data['result'] = $datas;

    if(!empty($_GET['callback'])) {
        echo $_GET['callback'].'('.json_encode($data).')';die;
    } else {
        echo json_encode($data);die;
    }
}
/*
* 跨数据库查询函数
* by King
* 2016-12-13
*/
function stride_mysql($type,$id) {
    $connection = array(
        'DB_TYPE'   => 'mysql',                 // 数据库类型
        'DB_HOST'   => '58.59.18.69',             // 服务器地址
        'DB_NAME'   => 'pre_jgpt',               // 数据库名
        'DB_USER'   => 'normal',                  // 用户名
        'DB_PWD'    => '!LeiFeng&F',                  // 密码
        'DB_PORT'   => 3306,                    // 端口
        'DB_PREFIX' => ''                   // 数据库表前缀
    );
    switch ($type) {
        //销售商
        case $type == 'retailer' ||  $type == 'retailer' && $id :
            $model = M('pre_jgpt.jxs_user','jc_',$connection);
            if($id){
                $result = $model->query("SELECT username FROM jc_jxs_user WHERE isDel = 0 AND id = '$id'");
            }else{
                $result = $model->query("SELECT id,username FROM jc_jxs_user WHERE isDel = 0");
            }
            break;

        //生产商
        case $type == 'manufacturer' || $type == 'manufacturer' && $id :
            $model = M('pre_jgpt.scs_info','jc_',$connection);
            if($id){
                $result = $model->query("SELECT sname FROM jc_scs_info WHERE isDel = 0 AND id = '$id'");
            }else{
                $result = $model->query("SELECT id,sname FROM jc_scs_info WHERE isDel = 0");
            }
            break;

        //品牌
        case $type == 'brand' || $type == 'brand' && $id :
            $model = M('pre_jgpt.pp','jc_',$connection);
            if($id){
                $result = $model->query("SELECT pname FROM jc_pp WHERE isDel = 0 AND id = '$id'");
            }else{
                $result = $model->query("SELECT id,pname FROM jc_pp WHERE isDel = 0");
            }
            break;
    }
    return $result;
}
/*
* 多表导入一表函数
* by King
* 2016-12-27
* 直接调用即可  字段名称必须修改
*/
function import_data(){
    $sql = "SELECT *,kx.phone as principal_mobile FROM table1 dk LEFT JOIN table2 kx ON dk.CODE = kx.DKCODE";
    $tbldk = M()->query($sql);
//        foreach($tbldk as $value){
//            $data['place_name'] = $value['name'];//产地名称
//            $data['place_code'] = $value['code'];//产地编号
//            $data['farmer_name'] = $value['nhname'];//农户名称
//            $data['region_code'] = $value['regioncode'];//地区编码
//            $data['mobile'] = $value['phone'];//联系方式
//            $data['longitude'] = $value['lng'];//经度
//            $data['latitude'] = $value['lat'];//纬度
//            $data['place_type'] = $value['regiontype'];//产地类型
//            $data['site_address'] = $value['dkaddr'];//地块地址
//            $data['principal'] = $value['fzrname'];//负责人
//            $data['principal_mobile'] = $value['principal_mobile'];//负责人电话
//            $data['place_size'] = $value['cdmj'];//产地面积
//            $data['plant_mode'] = $value['planting'];//种植方式
//            $data['soil_type'] = $value['soiltype'];//土壤类型
//            $data['irrigation_water'] = $value['watername'];//灌溉水源名称
//            $data['environment_description'] = $value['environment'];//环境描述
//            $data['remark'] = $value['bz'];//备注
//            M("place")->add($data);
//        }
    p($tbldk);
}
