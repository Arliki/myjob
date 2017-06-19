<?php
namespace Home\Controller;
use Think\Controller;
class FfController extends Controller {
    //家具显示
    public function firstf(){
        $m=M('firstf');
        $count=$m->count();
        $p = new \Think\Page($count,10);
        $list = $m->order('type,id desc')->limit($p->firstRow, $p->listRows)->select();
        $this->assign('page', $p->show()); // 赋值分页输出
        $this->assign('list',$list);
        $this->display();
    }
    //家具修改页面显示
    public function fupd(){
        if (!empty($_GET['id'])) {
            $id=$_GET['id'];
            $datacon=M('firstf')->where('id='.$id)->select();
            $pic=$datacon[0][pic];
            $ss=explode(',',$pic);
            $this->assign('ibox',$ss);
            $this->assign('firstf',$datacon);
            $this->display();
        }
    }
    //家具修改
    public function fupda(){
        if (IS_POST) {
            if (!empty($_GET['id'])) {
                $id=I('get.id');
            }else{$this->error('cannot found',U('ff/firstf'));}
            $conner=I('post.conner');
            $copy=$_POST['copy'];
            $pic=$_POST['pic'];
            $pic_pic=$_POST['pic_pic'];
            $title=$_POST['title'];
            $price=$_POST['price'];
            $d=D('firstf');
            $d->create();
            $data=array('id'=>$id,'price'=>$price,'conner'=>$conner,'copy'=>$copy,'title'=>$title,'pic'=>$pic,'pic_pic'=>$pic_pic);
            $re=$d->data($data)->where('id='.$id)->save();
            if ($re) {
                $this->success('修改成功',U('ff/firstf'));
            }else{
                $this->error('修改失败',U('ff/firstf'));
            }
        }else{
            $dat=M('firstf')->where('id='.$id)->select();
            $this->assign('firstf',$dat);
            $this->display();
        }
    }
    //家具添加
    public function fadd(){
        if (IS_POST) {
            $conner=$_POST['conner'];
            $copy=$_POST['copy'];
            $pic=$_POST['pic'];
            $pic_pic=$_POST['pic_pic'];
            $title=$_POST['title'];
            $price=$_POST['price'];
            $type=$_POST['type'];
            $db=M('firstf');
            $db->create();
            $re=$db->add();
            if ($re) {
                $this->success('家具新闻添加成功',U('ff/firstf'));
            }else{
                $this->error('家具新闻添加失败',U('ff/firstf'));
            }
        }else{
            $m=M('firstf')->where('id=1')->select();
            $this->assign('span',$m);
            $this->display();
        }
    }
    //单个家具删除
    public function fdel(){
        $m=D('firstf');
        $m->create();
        $id=$_GET['id'];
        if($id<10014 && $id>10009){
            $this->error('看在你这么执着的想删除首推信息，给你指一条明路吧!',U('left/content'));
        }else{
            $data=$m->where("id='$id'")->delete();
            if ($data) {
                $this->success('删除成功',U('ff/firstf'));
            }else{
                $this->error('删除失败',U('ff/firstf'));
            }
        }
    }
    //批量家具删除
    public function fadel(){
        if(empty(I('checkid'))){
            $this->rediract('bac/wrong');
        }else{
            $data = I('post.checkid');
            $m = M('firstf');
            $id=implode(',',$data);
            if($m->delete($id)){
                $this->success('批量删除成功',U('ff/firstf'));
            }else{
                $this->error('批量删除失败');
            }
        }
    }
}