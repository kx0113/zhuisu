<?php
namespace Admin\Controller;
use Think\Controller;
class BasicController extends BaseController {
    public function left_menu(){
        //获取搜索关键字
        $search_val = $_POST['search_val'];
        if($search_val){
            $menus_sql = "SELECT * FROM ims_sys_menu WHERE status<=2 AND type=1 AND name like '%$search_val%' OR path like '%$search_val%' OR icon like '%$search_val%' OR val1 like '%$search_val%' OR val2 like '%$search_val%' ORDER BY sort ASC";
            $menus = M()->query($menus_sql);
            //查找到包含该关键字的数据
            if($menus){
                $this->assign("res",1);
            }else{
                $menus_sql = "SELECT * FROM ims_sys_menu WHERE status<=2 AND type=1 ORDER BY sort ASC";
                $menus = M()->query($menus_sql);
                $this->assign("res",2);
            }
        }else{
            $menus_sql = "SELECT * FROM ims_sys_menu WHERE status<=2 AND type=1 ORDER BY sort ASC";
            $menus = M()->query($menus_sql);
        }
        $this->assign("menus",$menus);
        Log_add(3,'访问左侧导航菜单');
        $this->display();
    }
    //查询顶级菜单
    function menus_(){
        $where['status'] = array('in','0,1');
        $menus = M("sys_menu")->where(array('parent_id'=>0))->where($where)->order('sort asc')->select();
        return $menus;
    }
    /*
     *
     * wx
     * */
    public function wxpay(){
        if(IS_POST){
            $val = $_POST['val'];
            ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);

            require_once "weixin/lib/WxPay.Api.php";
            require_once "/weixin/example/WxPay.NativePay.php";
            require_once '/weixin/log.php';

//模式一
            /**
             * 流程：
             * 1、组装包含支付信息的url，生成二维码
             * 2、用户扫描二维码，进行支付
             * 3、确定支付之后，微信服务器会回调预先配置的回调地址，在【微信开放平台-微信支付-支付配置】中进行配置
             * 4、在接到回调通知之后，用户进行统一下单支付，并返回支付信息以完成支付（见：native_notify.php）
             * 5、支付完成之后，微信服务器会通知支付成功
             * 6、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
             */
            $notify = new NativePay();
            $url1 = $notify->GetPrePayUrl("123456789");

//模式二
            /**
             * 流程：
             * 1、调用统一下单，取得code_url，生成二维码
             * 2、用户扫描二维码，进行支付
             * 3、支付完成之后，微信服务器会通知支付成功
             * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
             */
            $input = new WxPayUnifiedOrder();
            $input->SetBody("20170212");
            $input->SetAttach("test");
            $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
            $input->SetTotal_fee("1");
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("test");
            $input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
            $input->SetTrade_type("NATIVE");
            $input->SetProduct_id("123456789");
            $result = $notify->GetPayUrl($input);
//echo "<pre>";
//var_dump($result);exit;
            $url2 = $result["code_url"];
        }
        $this->display();
    }
    public function left_menu_add(){
        $this->assign("parent_id",$this->menus_());
        $this->display();
    }
    public function left_classify_modify(){
        $sys_menu = M("sys_menu");
        $menu_find = $sys_menu->where(array('id'=>$_GET['id']))->find();
        $this->assign("menu_find",$menu_find);
        $this->assign("parent_id",$this->menus_());
        if(IS_POST){
            $data = I("post.");
            $res = $sys_menu->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                $this->success("修改成功！", U('Basic/left_menu'));
            }else{
                $this->error("修改失败！", U('Basic/left_menu'));
            }
        }else{
            $this->display();
        }
    }
    public function login_url(){
        Log_add(3,'访问登录首页了链接管理');
        $menus = M("sys_menu")->where(array('type'=>2,'status'=>1))->order("sort asc")->select();
        $this->assign("menus",$menus);
        $this->display();
    }
    public function switchs(){
        $tag = $_POST['tag'];
        $sys_menu = M("sys_menu");
        switch ($tag){
            //左侧导航更新
            case $tag == "update":
                $data = I('post.');
                $data['time'] =time();
                $res = $sys_menu->where(array('id'=>$_POST['id']))->data($data)->save();
                break;

            //左侧导航新建
            case $tag == "add":
                $data = I('post.');
                $data['status'] =1;
                $data['type'] =1;
                $data['time'] =time();
                if($_POST['icon'] == 1){
                    $res = $sys_menu->where(array('id'=>$_POST['parent_id']))->data(array('icon'=>$_POST['icon']))->save();
                }else{
                    $data['icon'] =$_POST['icon'];
                }
                $res = $sys_menu->data($data)->add();

                break;
            //左侧导航删除
            case $tag == "del":
                $data['status'] =3;
                $data['time'] =time();
                $res = $sys_menu->where(array('id'=>$_POST['id']))->data($data)->save();
                break;
            //删除通用函数
            case $tag == "common_delete":
                $model = $_POST['model'];//model
                $key   = $_POST['key'];//字段名称
                $val   = $_POST['val'];//字段值
                $val_1 = $_POST['val_1'];//备用
                $val_2 = $_POST['val_2'];//备用
                switch ($val_1 || $val_2){
                    case $val_1 == 'no':
                        //同时删除product_id 下面的全部信息
                        M("product")->where(array($key=>$val))->delete();
                        M("product_grow")->where(array($key=>$val))->delete();
                        M("product_manure")->where(array($key=>$val))->delete();
                        M("product_pesticide")->where(array($key=>$val))->delete();
                        M("product_plant")->where(array($key=>$val))->delete();
                        M("product_seed")->where(array($key=>$val))->delete();
                        $json['res'] = 'success';
                        $this->ajaxReturn($json);
                        Log_add(37,'删除商品成功',$model,$val);
                        break;
                    default:
                        if($val_1 == 'group'){
                            //如果删除用户组 并清空ims_sys_user_role_govern有关该role_id下面的权限数据
                            M()->execute("DELETE FROM ims_sys_user_role_govern WHERE role_id = $val");
                        }
                        switch ($val_1 || $val_2){
                            case $val_1 == 38:
                                Log_add(38,'删除企业信息成功',$model,$val);
                                break;
                        }
                        $res   = M($model)->where(array($key=>$val))->delete();
                }
                break;
        }
        if ($res) {
            $json['res'] = 'success';
        } else {
            $json['res'] = 'error';
        }
        $this->ajaxReturn($json);
    }
    /*
   * 通用删除函数
    * by King
    * 2016-11-15
    * m表名
    * k字段
    * v字段值
   * */
    public function del_common(){
        $model = $_GET['m'];
        $key   = $_GET['k'];
        $val   = $_GET['v'];
        $r     = $_GET['r'];
        if($r == 'n'){
            Log_add(30,'尝试删除一级标签失败');
            $this->error("删除失败，此商品包含其他数据，请检查！");
            exit;
        }
        $res   = M("$model")->where(array($key=>$val))->delete();
        if($res){
            $this->success("删除成功！");
        }else{
            $this->error("删除失败，请稍后再试！");
        }
    }
    /**
     * 获取产地
     * return json
     */
    public function ajax_place(){
        $place_code=$_GET['place_id'];
        p($place_code);
        $data = M('place')->field('place_code,place_name')->where(array('place_code'=>$place_code))->find();
        $this->ajaxReturn($data);
    }
}