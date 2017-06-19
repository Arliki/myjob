<?php
namespace Home\Controller;
use Think\Controller;
class LeftController extends Controller {
    //admin 显示
    public function admin(){
        $m=M('admin');
        $count=$m->count();
        $p = new \Think\Page($count,3);
        $list = $m->limit($p->firstRow, $p->listRows)->select();
        $this->assign('page', $p->show()); // 赋值分页输出
        $this->assign('list',$list);
        $this->display();
    }
    //add admin
    public function add(){
        if(IS_POST){
            $db=M('admin');
            $pwd=$_POST['passwd'];
            $pwd2=$_POST['repassword'];
            $id=$_POST['id'];
            $pow=$_POST['power'];
            $dd=$db->where("id='$id'")->select();
            if (!empty($dd)) {
                $this->error('用户名重复',U('Left/admin'));
            }else{
                if ($pwd===$pwd2) {
                    $data=array('id'=>$id,'passwd'=>md5($pwd),'power'=>$pow);
                    $rs=$db->data($data)->add();
                }else{
                    $this->error('确认密码不正确',U('Left/admin'));
                }
                if($rs){
                    $this->success('创建管理员成功',U('Left/admin'));
                }else{
                    $this->error('创建管理员失败',U('Left/admin'));
                }
            }
        }
    }
    //修改admin页面显示
    public function upd(){
        $m=M('admin');
        $id=$_GET['id'];
        $data=$m->where("id= '$id' ")->select();
        $this->assign('data',$data);
        $this->display();
    }
    //修改admin
    public function upda(){
        if($_POST){
            if(!empty($_GET['id'])){
                $id=$_GET['id'];
            }
            $newp=$_POST['passwd'];
            $rep=$_POST['repassword'];
            $pow=$_POST['power'];
            if ($newp==$rep) {
                $m=D('admin');
                $m->create();
                $pwd=MD5($_POST['passwd']);
                $data=array('passwd'=>$pwd,'power'=>$pow);
                $dat=$m->data($data)->where("id='$id'")->save();
                if ($dat) {
        			$this->success('修改成功',U('left/admin'));
        		}else{
        			$this->error('修改失败',U('left/admin'));
        		}
            }else{
                $this->error('确认密码错误',U('left/upd'));
            }
    	}else{
    		$m=M('admin');
	    	$dt=$m->select();
	    	$this->assign('data',$dt);
	    	$this->display('admin');
        }
    
    }
    //删除admin
    public function del(){
        $m=D('admin');
        $m->create();
        $id=$_GET['id'];
        $data=$m->where("id='$id'")->delete();
        if ($data) {
            $this->redirect('left/admin');
        }else{
            $this->error('删除失败',U('left/admin'));
        }
    }
    //站点开关显示（防止跳转出乱）
    public function webnow(){
        $m=M('web');
        $da=$m->select();
        $this->assign('web',$da);
        $this->display();
    }
    //站点开关页面
    public function web(){
        if ($_POST) {
            $m=M('web');
            $id=$_POST['id'];
            $con=$_POST['con'];
            if ($m->create()) {
                $data=array('value'=>$id,'con'=>$con);
                $da=$m->data($data)->where('id=0')->save();
                if ($da) {
                    $this->success('修改成功',U('left/webnow'));
                }
            }else{
                $this->error('修改失败',U('left/webnow'));
                }
        }else{
            $m=M('web');
        $da=$m->select();
        $this->assign('web',$da);
        $this->display();
        }
    }
    //帮助与反馈
    public function content(){
        $this->display();
    }
}