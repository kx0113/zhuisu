<?php
/**
 * 同江绿色食品管理平台
 * 检验检测控制器
 * Author: dalely
 * Date: 2016-12-5
 */
namespace Admin\Controller;
use Think\Controller;

class TestController extends BaseController {
    /*
     * 定量检测列表
     */
    public function index(){
      $this->ration();
    }

    /**
     * 定量检测管理
     */
    public function ration(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        $map['is_del']=0;
        $model=M("test_ration");
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
        Log_add(412,'访问定量检测列表页面');
        $this->display();
    }

    /**
     * 详细定量检测
     */
    public function detail_ration(){
        $model = M("test_ration");
        if($_GET['id']){
            $ration= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("ration",$ration);
        }
        Log_add(445,'访问详细定量检测页');
        $this->display();

    }
   /**
     * 添加修改定量检测
     */
    public function add_ration(){
        $model = M("test_ration");
        if($_GET['id']){
            $ration= $model ->where(array('id'=>$_GET['id']))->find();
            $info2 = $model ->where(array('id'=>$_GET['id']))->find();
            $info2['place_id'] = $info2['origin_num'];
            $info2['place_name'] = $info2['origin'];
            unset($info2['origin_num']);
            unset($info2['origin']);
            $this->assign('info2',$info2);
            $this->assign("ration",$ration);
        }
        if(IS_POST){
            $data = I("post.");
            $data['origin_num'] = $data['place_id'];
            $data['origin'] = $data['place_name'];
            unset($data['place_info']);
            unset($data['place_id']);
            unset($data['place_name']);
            if($_POST['id']){
                $res = $model->where(array('id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(413,'修改定量检测',$_POST['id']);
                    $this->success("修改成功！", U('Test/ration',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请确认后重试！", U('Test/ration',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $data['origin'] = $data['place_name'];
                $data['origin_num'] = $data['place_id'];
                unset($data['place_id']);
                unset($data['place_name']);
                $res = $model->add($data);
                if($res){
                    $this->success("新增成功！", U('Test/ration',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请确认后重试！", U('Test/ration',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(414,'访问新增定量检测页');
            $place_info = M('place')->field('place_id,place_name')->select();
            $this->assign('place_info',$place_info);
            $this->display();
        }
    }

    /**
     * 删除定量检测信息
     */
    public function del_ration(){
        $model = M("test_ration");
        $data['is_del']=1;
        if($_POST['ids']){
             $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(415,'删除定量检测信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(415,'删除定量检测信息',$res);
                $this->success("删除成功！", U('Test/ration',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请确认后重试！", U('Test/ration',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /**
     * 导出定量检测信息
     */
    public function excelout_ration(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("Test_ration");
        $key_type=I('key_type');
        $key=trim(I('key'));
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;

        $data= $model->where($where)->field('id,is_del,addtime',true)->order('id desc')->select();

        Log_add(416,'导出定量检测数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="定量检测";
        $headArr=array("产品批次","产品名称","产地编号","产地名称","检测日期","检测人","检测类型","ph值","吸光值","是否合格");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /**
     * 导入定量检测数据
     */
    public function import_ration(){
             $arr=$this->import_common();
             $data=array();
            foreach($arr as $key => $val){
                if(count($val)!=10)
                    $this->error("导入失败,数据格式不正确，请确认后重试", U('Test/ration',array('menu_id'=>$_GET['menu_id'])));

                $data['product_batch']=$val['A'];    //产品批次
                $data['product_name']=$val['B'];    //产品名称
                $data['origin_num']=$val['C'];//产地编号
                $data['origin']=$val['D'];   //产地名称
                $data['test_date']=$val['E'];//检测日期
                $data['test_user']=$val['F'];//检测人
                $data['test_type']=$val['G'];//检测类型
                $data['ph_val']=$val['H'];//ph值
                $data['light_val']=$val['I'];//吸光值
                $data['is_qualified']=$val['J'];//是否合格
                $res= M("Test_ration")->add($data);
            }
            if($res){
                Log_add(417,'导入定量检测数据');
                $this->success("导入成功！", U('Test/ration',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请确认后重试", U('Test/ration',array('menu_id'=>$_GET['menu_id'])));
            }

    }


    /**
     * 农残速测管理
     */
    public function pesticide(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        $map['is_del']=0;
        $model=M("test_pesticide");
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
        Log_add(418,'访问农残速测列表页面');
        $this->display();
    }

    /**
     * 添加修改农残速测
     */
    public function add_pesticide(){
        $model = M("test_pesticide");
        if($_GET['id']){
            $pesticide= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("pesticide",$pesticide);
        }
        if(IS_POST){
            $data = I("post.");
            if($_POST['id']){
                $res = $model->where(array('id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(419,'修改农残速测',$_POST['id']);
                    $this->success("修改成功！", U('Test/pesticide',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请确认后重试！", U('Test/pesticide',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $res = $model->add($data);
                if($res){
                    $this->success("新增成功！", U('Test/pesticide',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请确认后重试！", U('Test/pesticide',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(420,'访问新增农残速测页');
            $this->display();
        }
    }
    /**
     * 详细农残速测
     */
    public function detail_pesticide(){
        $model = M("test_pesticide");
        if($_GET['id']){
            $pesticide= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("pesticide",$pesticide);
        }

        Log_add(446,'访问详细农残速测页');
        $this->display();

    }

    /**
     * 删除农残速测信息
     */
    public function del_pesticide(){
        $model = M("test_pesticide");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(421,'删除农残速测信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(421,'删除农残速测信息',$res);
                $this->success("删除成功！", U('Test/pesticide',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请确认后重试！", U('Test/pesticide',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /**
     * 导出农残速测信息
     */
    public function excelout_pesticide(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("Test_pesticide");
        $key_type=I('key_type');
        $key=trim(I('key'));
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;

        $data= $model->where($where)->field('id,is_del,addtime',true)->order('id desc')->select();

        Log_add(422,'导出农残速测数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="农残速测";
        $headArr=array("产品批次","产品名称","产地编号","产地名称","抑制率","检测日期","检测人","检测类型","是否合格");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /**
     * 导入农残速测数据
     */
    public function import_pesticide(){
            $arr=$this->import_common();
            //需要保存的数据，
            $data=array();
            foreach($arr as $key => $val){
                if(count($val)!=19)
                    $this->error("导入失败,数据格式不正确，请确认后重试", U('Test/pesticide',array('menu_id'=>$_GET['menu_id'])));

                $data['product_batch']=$val['A'];    //产品批次
                $data['product_name']=$val['B'];    //产品名称
                $data['origin_num']=$val['C'];//产地编号
                $data['origin']=$val['D'];   //产地名称
                $data['inhibition_rate']=$val['E'];//检测日期
                $data['test_date']=$val['F'];//检测日期
                $data['test_user']=$val['G'];//检测人
                $data['test_type']=$val['H'];//检测类型
                $data['is_qualified']=$val['I'];//是否合格
                $res= M("Test_pesticide")->add($data);
            }
            if($res){
                Log_add(423,'导入农残速测数据');
                $this->success("导入成功！", U('Test/pesticide',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("导入失败，请确认后重试", U('Test/pesticide',array('menu_id'=>$_GET['menu_id'])));
            }

    }

    /**
     * PH原始测定记录管理
     */
    public function ph(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        $map['is_del']=0;
        $model=M("test_ph");
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
        Log_add(424,'访问PH原始测定记录列表页面');
        $this->display();
    }

    /**
     * 添加修改PH原始测定记录
     */
    public function add_ph(){
        $model = M("test_ph");
        if($_GET['id']){
            $ph= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("ph",$ph);
        }
        if(IS_POST){
            $data = I("post.");
            if($_POST['id']){
                $res = $model->where(array('id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(425,'修改PH原始测定记录',$_POST['id']);
                    $this->success("修改成功！", U('Test/ph',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请确认后重试！", U('Test/ph',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $res = $model->add($data);
                if($res){
                    $this->success("新增成功！", U('Test/ph',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请确认后重试！", U('Test/ph',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(426,'访问新增PH原始测定记录页');
            $this->display();
        }
    }
    /**
     * 详细PH原始测定记录
     */
    public function detail_ph(){
        $model = M("test_ph");
        if($_GET['id']){
            $ph= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("ph",$ph);
        }
        Log_add(447,'访问详细PH原始测定记录页');
        $this->display();

    }

    /**
     * 删除PH原始测定记录信息
     */
    public function del_ph(){
        $model = M("test_ph");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(427,'删除PH原始测定记录信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(427,'删除PH原始测定记录信息',$res);
                $this->success("删除成功！", U('Test/ph',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请确认后重试！", U('Test/ph',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /**
     * 导出PH原始测定记录信息
     */
    public function excelout_ph(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("Test_ph");
        $key_type=I('key_type');
        $key=trim(I('key'));
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;

        $data= $model->where($where)->field('id,is_del,addtime',true)->order('id desc')->select();

        Log_add(428,'导出PH原始测定记录数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="PH原始测定记录";
        $headArr=array("产品批次", "送样单位", "分析编号", "土重", "ph测定值第一次", "ph测定值第二次", "仪器型号", "检测人", "检测方法", "检测日期",);
       // $headArr=array("产品批次","产品名称","产地编号","产地名称","抑制率","检测日期","检测人","检测类型","是否合格");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /**
     * 导入PH原始测定记录数据
     */
    public function import_ph(){
        $arr=$this->import_common();
        //需要保存的数据，
        $data=array();
        foreach($arr as $key => $val){
            if(count($val)!=10)
                $this->error("导入失败,数据格式不正确，请确认后重试", U('Test/ph',array('menu_id'=>$_GET['menu_id'])));

            $data['product_batch']=$val['A'];    //产品批次
            $data['sample_unit']=$val['B'];    //送样单位
            $data['analysisnum']=$val['C'];//分析编号
            $data['soil']=$val['D'];   //土重
            $data['ph_val1']=$val['E'];//ph值第一次
            $data['ph_val2']=$val['F'];//ph值第二次
            $data['instrument']=$val['G'];//仪器型号
            $data['test_user']=$val['H'];//检测人
            $data['test_method']=$val['I'];//检测方法
            $data['test_date']=$val['J'];//检测日期
            $res= M("Test_ph")->add($data);
        }
        if($res){
            Log_add(429,'导入PH原始测定记录数据');
            $this->success("导入成功！", U('Test/ph',array('menu_id'=>$_GET['menu_id'])));
        }else{
            $this->error("导入失败，请确认后重试", U('Test/ph',array('menu_id'=>$_GET['menu_id'])));
        }

    }
/**
     * 滴定法测定原始记录管理
     */
    public function titration(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        $map['is_del']=0;
        $model=M("test_titration");
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
        Log_add(430,'访问滴定法测定原始记录列表页面');
        $this->display();
    }

    /**
     * 添加修改滴定法测定原始记录
     */
    public function add_titration(){
        $model = M("test_titration");
        if($_GET['id']){
            $titration= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("titration",$titration);
        }
        if(IS_POST){
            $data = I("post.");
            if($_POST['id']){
                $res = $model->where(array('id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(431,'修改滴定法测定原始记录',$_POST['id']);
                    $this->success("修改成功！", U('Test/titration',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请确认后重试！", U('Test/titration',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $res = $model->add($data);
                if($res){
                    $this->success("新增成功！", U('Test/titration',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请确认后重试！", U('Test/titration',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(432,'访问新增滴定法测定原始记录页');
            $this->display();
        }
    }
    /**
     * 详细滴定法测定原始记录
     */
    public function detail_titration(){
        $model = M("test_titration");
        if($_GET['id']){
            $titration= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("titration",$titration);
        }
        Log_add(448,'访问详细滴定法测定原始记录页');
        $this->display();
    }

    /**
     * 删除滴定法测定原始记录信息
     */
    public function del_titration(){
        $model = M("test_titration");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(433,'删除滴定法测定原始记录信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(433,'删除滴定法测定原始记录信息',$res);
                $this->success("删除成功！", U('Test/titration',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请确认后重试！", U('Test/titration',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /**
     * 导出滴定法测定原始记录信息
     */
    public function excelout_titration(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("Test_titration");
        $key_type=I('key_type');
        $key=trim(I('key'));
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;

        $data= $model->where($where)->field('id,is_del,addtime',true)->order('id desc')->select();

        Log_add(434,'导出滴定法测定原始记录数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="滴定法测定原始记录";
        $headArr=array("产品批次", "送样单位", "分析编号", "水分系数", " 风干样重", "烘干样重", "定容体积", "分取倍数", "滴定剂浓度", "计算结果", "样品处理方法", "检测人", "检测日期");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /**
     * 导入滴定法测定原始记录数据
     */
    public function import_titration(){
        $arr=$this->import_common();
        //需要保存的数据，
        $data=array();
        foreach($arr as $key => $val){
            if(count($val)!=13)
                $this->error("导入失败,数据格式不正确，请确认后重试", U('Test/titration',array('menu_id'=>$_GET['menu_id'])));

            $data['product_batch']=$val['A'];    //产品批次
            $data['sample_unit']=$val['B'];    //送样单位
            $data['analysisnum']=$val['C'];//分析编号
            $data['water_coefficient']=$val['D'];   //水分系数
            $data['dry1']=$val['E'];//风干样重
            $data['dry2']=$val['F'];//烘干样重
            $data['volume']=$val['G'];//定容体积
            $data['multiple']=$val['H'];//分取倍数
            $data['titration']=$val['I'];//滴定剂浓度
            $data['result']=$val['J'];//计算结果
            $data['processe_method']=$val['K'];//样品处理方法
            $data['test_user']=$val['L'];//检测人
            $data['test_date']=$val['M'];//检测日期
            $res= M("Test_titration")->add($data);
        }
        if($res){
            Log_add(435,'导入滴定法测定原始记录数据');
            $this->success("导入成功！", U('Test/titration',array('menu_id'=>$_GET['menu_id'])));
        }else{
            $this->error("导入失败，请确认后重试", U('Test/titration',array('menu_id'=>$_GET['menu_id'])));
        }

    }

    /**
     * 光度法测定记录管理
     */
    public function luminosity(){
        //判断增删改查权限
        $this->assign("menu_id",Detection_menu());
        $map['is_del']=0;
        $model=M("test_luminosity");
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
        Log_add(436,'访问光度法测定记录列表页面');
        $this->display();
    }

    /**
     * 添加修改光度法测定记录
     */
    public function add_luminosity(){
        $model = M("test_luminosity");
        if($_GET['id']){
            $luminosity= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("luminosity",$luminosity);
        }
        if(IS_POST){
            $data = I("post.");
            if($_POST['id']){
                $res = $model->where(array('id'=>$_POST['id']))->save($data);
                if($res){
                    Log_add(437,'修改光度法测定记录',$_POST['id']);
                    $this->success("修改成功！", U('Test/luminosity',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("修改失败，请确认后重试！", U('Test/luminosity',array('menu_id'=>$_GET['menu_id'])));
                }
            }else{
                $res = $model->add($data);
                if($res){
                    $this->success("新增成功！", U('Test/luminosity',array('menu_id'=>$_GET['menu_id'])));
                }else{
                    $this->error("新增失败，请确认后重试！", U('Test/luminosity',array('menu_id'=>$_GET['menu_id'])));
                }
            }
        }else{
            Log_add(438,'访问新增光度法测定记录页');
            $this->display();
        }
    }
    /**
     * 详细光度法测定记录
     */
    public function detail_luminosity(){
        $model = M("test_luminosity");
        if($_GET['id']){
            $luminosity= $model ->where(array('id'=>$_GET['id']))->find();
            $this->assign("luminosity",$luminosity);
        }

        Log_add(449,'访问详细光度法测定记录页');
        $this->display();

    }

    /**
     * 删除光度法测定记录信息
     */
    public function del_luminosity(){
        $model = M("test_luminosity");
        $data['is_del']=1;
        if($_POST['ids']){
            $where['id']=array('in',$_POST['ids']);
            $res = $model ->where($where)->save($data);
            if($res){
                Log_add(439,'删除光度法测定记录信息',$res);
                $stata=1;
            }else{
                $stata=0;
            }
            $this->ajaxReturn($stata);
        }
        if($_GET['id']){

            $res = $model ->where(array('id'=>$_GET['id']))->save($data);
            if($res){
                Log_add(440,'删除光度法测定记录信息',$res);
                $this->success("删除成功！", U('Test/luminosity',array('menu_id'=>$_GET['menu_id'])));
            }else{
                $this->error("删除失败，请确认后重试！", U('Test/luminosity',array('menu_id'=>$_GET['menu_id'])));
            }
        }
    }
    /**
     * 导出光度法测定记录信息
     */
    public function excelout_luminosity(){
        //此处需要导出的数据，可以调数据库内容
        $model=M("Test_luminosity");
        $key_type=I('key_type');
        $key=trim(I('key'));
        if($key_type){
            $where[$key_type] = array('like', "%{$key}%");
        }
        $where['is_del']=0;

        $data= $model->where($where)->field('id,is_del,addtime',true)->order('id desc')->select();

        Log_add(441,'导出光度法测定记录数据');

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $filename="光度法测定记录";

        $headArr=array("产品批次", "送样单位", "测定项目", "分析编号", "水分", " 样品重量", "制待测液", "分取倍数", "测定体积", "读数", "待测液浓度", "计算结果", "测定方法", "检测人", "测定日期");
        downloadExcel($filename,$headArr,$data);//导出数据到excel
    }
    /**
     * 导入光度法测定记录数据
     */
    public function import_luminosity(){
        $arr=$this->import_common();
        //需要保存的数据，
        $data=array();

        foreach($arr as $key => $val){
            if(count($val)!=15)
                $this->error("导入失败,数据格式不正确，请确认后重试", U('Test/luminosity',array('menu_id'=>$_GET['menu_id'])));

            $data['product_batch']=$val['A'];    //产品批次
            $data['sample_unit']=$val['B'];    //送样单位
            $data['measure_item']=$val['C'];//测定项目
            $data['analysisnum']=$val['D'];//分析编号
            $data['water']=$val['E'];   //水分
            $data['sample_weight']=$val['F'];//样品重量
            $data['extracting']=$val['G'];//制待测液
            $data['multiple']=$val['H'];//分取倍数
            $data['volume']=$val['I'];//测定体积
            $data['reading']=$val['J'];//读数
            $data['titration']=$val['K'];//待测液浓度
            $data['result']=$val['L'];//计算结果
            $data['test_method']=$val['M'];//测定方法
            $data['test_user']=$val['N'];//检测人
            $data['test_date']=$val['O'];//检测日期
            $res= M("Test_luminosity")->add($data);
        }
        if($res){
            Log_add(442,'导入光度法测定记录数据');
            $this->success("导入成功！", U('Test/luminosity',array('menu_id'=>$_GET['menu_id'])));
        }else{
            $this->error("导入失败，请确认后重试", U('Test/luminosity',array('menu_id'=>$_GET['menu_id'])));
        }

    }



    /**
     * 导入数据公共部分
     */
    public function import_common(){
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
            return $arr; //需要保存的数据，
        }
    }

    /**
     * 检测统计页
     */
    public function statistics(){
        $map['is_del']= 0;
        $list1 = M ( 'test_ration' )->field('product_name,test_user,addtime')->where($map)->select ();
        foreach($list1 as $key=>$val){
            $list1[$key]['name']='定量检测-'.$val['product_name'];
        }
        $list2 = M ( 'test_pesticide' )->field('product_name,test_user,addtime')->where($map)->select ();
        foreach($list2 as $key2=>$val2){
            $list2[$key2]['name']='农残速测-'.$val2['product_name'];
        }
        $list0=array_merge($list1,$list2);//合并数组

        $flag = array();
        foreach ($list0 as $arr) {
            $flag[] = $arr["addtime"];
        }

        array_multisort($flag, SORT_DESC, $list0);//重排数组降序排列
        //array_multisort($flag, SORT_ASC, $list0);//重排数组升序排列
        $count=count($list0);
        $p = new \Think\Page($count,20);
        $list=array_slice($list0,$p->firstRow,$p->listRows);
        $this->assign ( 'list', $list );
        $this->assign ( 'page', $p->show());
        Log_add(450,'访问检测统计页面');
        $this->display();
    }

}