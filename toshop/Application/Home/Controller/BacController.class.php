<?php
namespace Home\Controller;
use Think\Controller;
class BacController extends Controller {
    public function login(){
        $m=M('admin');
        $s=$m->field('id')->select();
        $this->display();
    }
    public function selec(){
        $m=M('news');
        if (!empty($_GET['action'])) {
            $action=$_GET['action'];
        }
        if (empty($_POST['se1'])==false) {
            $se1=$_POST['se1'];
        }
        if ($action=='acti') {
                $data=$m->where("title like '%$se1%' or conner like '%$se1%' or author like '%$se1%'")->select();
        }
        $this->assign('con',$data);
        $this->display();
    }
    public function index(){
        if($_POST){
            $id=$_POST['id'];
            $pwd=MD5($_POST['passwd']);
            $m=M('admin');
            $data=$m->where("id='$id'")->select();
            if($pwd==$data[0][passwd]){
                session_start();
                $_SESSION['admin']=$id;
                $_SESSION['power']=$data[0][power];
                setcookie('admin',$id,time()+3600);
                    if ($data[0][power]==1) {
                        $this->display('index');
                    }else{
                        $this->display('newsindex');
                    }
            }else{
                $this->error('账号或密码错误',U('bac/login'));
            }
        }else{
            if($_SESSION['admin'] && $_SESSION['power']){
                if($_SESSION['power']==1){
                    $this->display('index');
                }else{
                    $this->display('newsindex');
                }
            }else{
                $this->error('非法登录!',U('bac/login'));}
            }
        }
    public function wrong(){
        $this->display();
    }
}
