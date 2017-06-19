<?php
namespace home\Controller;
use Think\Controller;
use Think\Model;
use Think\Page;
class IndexController extends Controller {
    //跳转后台管理
    public function back(){
    	$this->redirect('bac/login');
    }
    //首页
    public function index(){
        $a=M('web');
        $aa=$a->where('id=0')->find();
        if ($aa['value']=='Y') {
            //公司信息
            $copy=M('copy')->select();
            //首页轮播图
            $ib=M('ibox')->where('id=1')->select();
            $ibpic=$ib[0][pic];
            $ibss=explode(',',$ibpic);
            //首推显示
            $m=M('firstm')->select();
            //随机取出3行数据显示家具
            $ss=M('firstf');
            $ara=$ss->getField('id',true);
            $a=array();
            $b=count($ara);
            for($i=1;$i<=3;$i++){
                $ids=rand(1,$b);
                $f=$ss->where("id= $ids" )->find();
                if($f){
                    array_push($a,$f);}
                else{$i=$i-1;};
            };
            $this->assign('copy',$copy);
            $this->assign('firstm',$m);
            $this->assign('ibox',$ibss);
            $this->assign('conner',$a);
            $this->display();
        }else{
            echo "$aa[con]";
            die;
        }
    }
    //图像上传
    public function uploadify(){

        if (!empty($_FILES)) {
            //图片上传设置
            $config = array(   
                'maxSize'    =>    9437184, 
                'savePath'   =>    '',
                'saveName'   =>    array('uniqid',''), 
                'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),  
                'autoSub'    =>    true,   
                'subName'    =>    array('date','Ymd'),
            );
            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if($images){
                for($i=0;$i<count($images);$i++) {
                    $info .= '_' . $images['Filedata']['savepath'] . $images['Filedata']['savename'];

                };
                //返回文件地址和名给JS作回调用
                echo $info;
            }
            else{
                //$this->error($upload->getError());//获取失败信息
            }
        }
    }
    //家具展示
    public function assortment(){
        //随机取3条家庭家具数据
        $ss=M('firstf');
        $ara=M('firstf')->getField('id',true);
        $a=array();
        $b=count($ara);
        for($i=1;$i<=3;$i++){
            $ids=rand(1,$b);
            $f=$ss->where("id= $ids and type = 1 " )->find();
            if($f){
            array_push($a,$f);}
            else{$i=$i-1;};
        }
        $this->assign('firstf',$a);
        //随机取8条家庭办公数据
        $ab=array();
        $bb=count($ara);
        for($i=1;$i<=8;$i++){
            $ids=rand(1,$bb);
            $f=$ss->where("id= $ids and type = 2 " )->find();
            if($f){
                array_push($ab,$f);}
            else{$i=$i-1;};
        }
        $copy=M('copy')->select();
        $this->assign('copy',$copy);
        $this->assign('firstfs',$ab);
        $this->display();
    }
    //总体新闻
    public function news(){
        $ty=M('type')->select();
        $con=M('news');
        $count=$con->count();//计算条数
        $page=new Page($count,9);//设置分页条数
        $show=$page->show();
        $list=$con->limit($page->firstRow.','.$page->listRows)->select();
        $copy=M('copy')->select();
        $this->assign('copy',$copy);
        $this->assign('type',$ty);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display();
    }
    //显示某一类新闻
    public function newsclass(){
        $typeid=$_GET['id'];
        $ty=M('type')->where("typeid='$typeid'")->select();
        $con=M('news')->where("typeid='$typeid'");
        $count=$con->count();//计算条数
        $page=new Page($count,9);//设置分页条数
        $show=$page->show();
        $list=$con->limit($page->firstRow.','.$page->listRows)->where("typeid='$typeid'")->select();
        $copy=M('copy')->select();
        $this->assign('copy',$copy);
        $this->assign('type',$ty);
        $this->assign('list',$list);
        $this->assign('pages',$show);
        $this->display('newsclass');
    }
    //显示单个详细新闻
    public function news_index(){
    	$id=$_GET['id'];
    	$dat=M('news')->where("id=$id")->select();
        $copy=M('copy')->select();
        $this->assign('copy',$copy);
    	$this->assign('news_index',$dat);
    	$this->display();
    }
    //关于我们
    public function aboutus(){
        $about=M('about')->select();
        $copy=M('copy')->select();
        $this->assign('copy',$copy);
        $this->assign('about',$about);
        $this->display();
    }
    //商品详情
    public function detail(){
        if(IS_GET) {
            $id = I('get.id');
            $dat = M('firstf')->where("id='$id'")->select();
            $ss=M('firstf');
            $typ=$dat[0][type];
            //获取8个当前类型家具
            $ara=M('firstf')->getField('id',true);
            $ab=array();
            $bb=count($ara);
            for($i=1;$i<=8;$i++){
                $ids=rand(1,$bb);
                $f=$ss->where("id= $ids and type = '$typ' " )->find();
                if($f){
                    array_push($ab,$f);}
                else{$i=$i-1;};
            }
            $del=M('firstf')->where("id = '$id'")->find();
            $delpic=$del[pic];
            $box=explode(',',$delpic);
            $this->assign('box',$box);
            $copy=M('copy')->select();
            $this->assign('copy',$copy);
            $this->assign('link',$ab);
            $this->assign('detail', $dat);
            $this->display();
        }
    }
}