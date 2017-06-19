<?php
namespace Home\Controller;
use Think\Controller;
class StyleController extends Controller {
	//没啥解释的，正如题目所言
	public function index(){
		$m=M('sbox')->where('id=1')->select();
		$pic=$m[0][pic];
		$ss=explode(',',$pic);
		$this->assign('sbox',$ss);
		$copy=M('copy')->select();
		$this->assign('copy',$copy);
		$this->display('style_1');
	}
	public function style_2(){
		$m=M('sbox')->where('id=1')->select();
		$pic=$m[0][pic];
		$ss=explode(',',$pic);
		$copy=M('copy')->select();
		$this->assign('copy',$copy);
		$this->assign('sbox',$ss);
		$this->display();
	}
	public function style_3(){
		$m=M('sbox')->where('id=1')->select();
		$pic=$m[0][pic];
		$ss=explode(',',$pic);
		$copy=M('copy')->select();
		$this->assign('copy',$copy);
		$this->assign('sbox',$ss);
		$this->display();
	}
	public function style_4(){
		$m=M('sbox')->where('id=1')->select();
		$pic=$m[0][pic];
		$ss=explode(',',$pic);
		$copy=M('copy')->select();
		$this->assign('copy',$copy);
		$this->assign('sbox',$ss);
		$this->display();
	}
	public function style_5(){
		$m=M('sbox')->where('id=1')->select();
		$pic=$m[0][pic];
		$ss=explode(',',$pic);
		$copy=M('copy')->select();
		$this->assign('copy',$copy);
		$this->assign('sbox',$ss);
		$this->display();
	}
	public function style_6(){
		$m=M('sbox')->where('id=1')->select();
		$pic=$m[0][pic];
		$ss=explode(',',$pic);
		$copy=M('copy')->select();
		$this->assign('copy',$copy);
		$this->assign('sbox',$ss);
		$this->display();
	}

}