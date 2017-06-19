<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
class DropController extends Controller {
	//关于我们
	public function about(){
		if(IS_POST){
			$m=D('about');
			$content=I('post.content');
			if($content){
				$m->create();
				$data=$m->where('id=1')->save();
				if($data){
					$this->success('修改成功',U('drop/about'));
				}else{
					$this->error('遇到未知错误,修改失败',U('drop/about'));
				}
			}else{
				$this->error('请填写内容',U('drop/about'));
			}
		}else{
			$m=M('about')->select();
			$this->assign('about',$m);
			$this->display();
		}
	}
	//首推显示
	public function firstm(){
		$m=M('firstm');
		$count=$m->count();
		$p = new \Think\Page($count,5);
		$list = $m->order('id,time desc')->limit($p->firstRow, $p->listRows)->select();
		$this->assign('page', $p->show()); // 赋值分页输出
		$this->assign('list',$list);
        $this->display();
    }
    //首推修改页面显示
    public function upd(){
    	if (!empty($_GET['id'])) {
    		$id=$_GET['id'];
    		$datacon=M('firstm')->where('id='.$id)->select();
			$pic=$datacon[0][pic];
			$ss=explode(',',$pic);
			$this->assign('ibox',$ss);
    		$this->assign('content',$datacon);
    		$this->display();
    	}
    }
    //首推修改
    public function upda(){
    	if ($_POST) {
    		if (!empty($_GET['id'])) {
    			$id=$_GET['id'];
    		}else{$this->error('cannot found',U('drop/firstm'));}
    		$conner=$_POST['conner'];
    		$copy=$_POST['copy'];
    		$pic=$_POST['pic'];
			$pic_pic=$_POST['pic_pic'];
    		$title=$_POST['title'];
    		$d=D('firstm');
    		$d->create();
    		if (empty($_POST['time'])) {
    			$time=date('Y-m-d');
    		}else{
    			$time=$_POST['time'];
    		}
    		$data=array('id'=>$id,'time'=>$time,'conner'=>$conner,'copy'=>$copy,'title'=>$title,'pic'=>$pic,'pic_pic'=>$pic_pic);
    		$re=$d->data($data)->where('id='.$id)->save();
    		if ($re) {
				$dta=array('id'=>$id,'conner'=>$conner,'copy'=>$copy,'title'=>$title,'pic'=>$pic,'pic_pic'=>$pic_pic);
				$new=M('firstf')->data($dta)->where('id='.$id)->save();
    			$this->success('修改成功',U('drop/firstm'));
    		}else{
    			$this->error('修改失败',U('drop/firstm'));
    		}
    	}else{
    		$m=M('firstm');
	    	$data=$m->select();
	    	$this->assign('firstm',$data);
	    	$this->display('firstm');
    	}
    }
    //新闻显示
    public function news(){
        $m=M('news');
		$count=$m->count();
		$p = new \Think\Page($count,10);
		$list = $m->order('typeid,time desc')->limit($p->firstRow, $p->listRows)->select();
		$this->assign('page', $p->show()); // 赋值分页输出
		$this->assign('list',$list);
        $this->display();
    }
    //新闻添加
    public function newsadd(){
    	if (IS_POST) {
			$db=M('news');
    		$content=$_POST['content'];
			$pic=$_POST['pic'];
    		$two=$_POST['two'];
    		$title=$_POST['title'];
    		$typeid=$_POST['typeid'];
			if (empty($_POST['time'])) {
				$tim=date('Y/m/d');
			}else{
				$tim=$_POST['time'];
			};
			$data=array('title'=>$title,'two'=>$two,'pic'=>$pic,'content'=>$content,'typeid'=>$typeid,'time'=>$tim);
    		$db->create();
    		$re=$db->add($data);
    		if ($re) {
    			$this->success('新闻添加成功',U('drop/news'));
    		}else{
    			$this->error('新闻添加失败',U('drop/news'));
    		}
    	}else{
    		$this->display();
    	}
    }
    //新闻修改
    public function newsupd(){
    	if (IS_POST) {
    		if (!empty($_GET['id'])) {
    			$id=I('get.id');
    		}else{$this->error('cannot found',U('drop/news'));}
			$content=$_POST['content'];
    		$two=$_POST['two'];
    		$title=$_POST['title'];
    		$typeid=$_POST['typeid'];
			$pic=$_POST['pic'];
			$time=$_POST['time'];
    		$d=D('news');
    		$d->create();
    		$re=$d->where('id='.$id)->save();
    		if ($re) {
    			$this->success('修改成功',U('drop/news'));
    		}else{
    			$this->error('修改失败',U('drop/news'));
    		}
		}else{
			$id=$_GET['id'];
    		$dat=M('news')->where("id='$id'")->select();
    		$this->assign('news',$dat);
    		$this->display();
		}
    }
    //单条新闻删除
    public function newsdel(){
    	$m=D('news');
        $m->create();
        $id=$_GET['id'];
        $data=$m->where("id='$id'")->delete();
        if ($data) {
            $this->success('删除成功',U('drop/news'));
        }else{
            $this->error('删除失败',U('drop/news'));
        }
    }
	//批量新闻删除
	public function newsadel(){
		if(empty(I('checkid'))){
			$this->error('请选中要删除的信息！');
		}else{
			$data = I('post.checkid');
			$m = M('news');
			$id=implode(',',$data);
			if($m->delete($id)){
				$this->success('批量删除成功',U('drop/news'));
			}else{
				$this->error('批量删除失败');
			}
		}
	}
	//首页轮播图修改
	public function ibox(){
		if(IS_POST){
				$d=D('ibox');
				if($d->create()){
					$check="0";
					$del=M('ibox')->where("id = 1")->find();
					$delpic=$del[pic];
					$ss=explode(',',$delpic);
					for($i=0;$i<count($ss)-1;$i++){
					$whe='D:/phpStudy/WWW'.$ss[$i];
						if($whe && file_exists($whe)){
							unlink($whe);
							$check++;
						}
					}if($check!=0){
						$pic=$_POST['pic'];
						$new=$d->where("id= 1 ")->save();
						if($new){
							$this->success('修改成功,原图片已删除.',U('drop/ibox'));
						}else{
							$this->error('遇到未知错误，修改失败,请及时反馈.',U('left/content'));
						}
					}
				}
		}else{
			$m=M('ibox')->where('id=1')->select();
			$pic=$m[0][pic];
			$ss=explode(',',$pic);
			$this->assign('ibox',$ss);
			$this->display();
		}
	}
	//风格轮播图修改
	public function sbox(){
		if(IS_POST){
			$d=D('sbox');
			if($d->create()){
				$check="0";
				$del=M('sbox')->where("id = 1")->find();
				$delpic=$del[pic];
				$ss=explode(',',$delpic);
				for($i=0;$i<count($ss)-1;$i++){
					$whe='D:/phpStudy/WWW'.$ss[$i];
					if($whe && file_exists($whe)){
						unlink($whe);
						$check++;
					}
				}if($check!=0){
					$pic=$_POST['pic'];
					$new=$d->where("id= 1 ")->save();
					if($new){
						$this->success('修改成功,原图片已删除.',U('drop/sbox'));
					}else{
						$this->error('遇到未知错误，修改失败,请及时反馈.',U('left/content'));
					}
				}
			}
		}else{
			$m=M('sbox')->where('id=1')->select();
			$pic=$m[0][pic];
			$ss=explode(',',$pic);
			$this->assign('sbox',$ss);
			$this->display();
		}
	}
	//版权区修改
	public function copy(){
		if(IS_POST){
			$copy=D('copy');
			if($copy->create()){
				$re=$copy->where('id=1')->save();
				if($re){
					$this->success('版权区内容已修改',U('drop/copy'));
				}else{
					$this->error('修改失败',U('drop/copy'));
				}
			}
		}else{
			$copy=M('copy')->select();
			$this->assign('copy',$copy);
			$this->display();
		}
	}
}