<?php
/*
* File: NoticeController.class.php
* Author: Arliki
* Date: 2017-12-28 9:31
* 公告/通知/提醒
*/
defined('IN_ECTOUCH') or die('Deny Access');
header('contend-type:text/html;charset=utf8');
class NoticeController extends CommonController{
    public function shop(){
        if ($_SESSION ['user_id'] == 0) {
            $this->redirect(url('user/login', array('step' => 'flow')));
            exit;
        }
        $user_id=$_SESSION ['user_id'];
        $notify=model('Notice')->get_shop_notify($user_id);
        if ($notify['update']) {
            $this->assign('url',url('notice/shop_all_read'));
            $this->assign('all_read',1);
        }
        $this->assign('shop_notify',$notify['con']);
        $this->assign('title',"通知列表");
        $this->display('notify_list.dwt');
    }
    public function shop_detail(){
        $nid=I('get.nid');
        $res=model('Notice')->get_shop_detail($nid);
        $this->assign('title',$res['name']);
        $this->assign('notify',$res);
        $this->display('notify_detail.dwt');
    }
    public function shop_all_read(){
        $user_id=$_SESSION ['user_id'];
        model('Notice')->update_shop_notify($user_id);
        die;
    }
}