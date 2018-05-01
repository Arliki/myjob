<?php
defined('IN_ECTOUCH') or die('Deny Access');

class CouponController extends CommonController {
    public $user_id;
    public function __construct()
    {
        parent::__construct();
        if(!$_SESSION['user_id']){
            if(IS_AJAX){
                echo json_encode(array('error'=>1,'info'=>'请登录','no_login'=>1));die();
            }else{
                $this->redirect(url('user/login'));
            }
        }
        $this->user_id = $_SESSION['user_id'];
    }

    /**
     * 优惠券列表
     */
    public function coupon_list(){
        //更新优惠券查看时间
        $next_time = date('Y-m-d', time());
        $next_time = strtotime($next_time) + 24 * 3600;
        M()->query("update ecs_user_notice set coupon_time='$next_time' where user_id ='".$_SESSION['user_id']."' ");
        //↑↑↑↑↑↑↑
        $type = I('get.type','');
        if(!in_array($type, array('','unuse','exceed'))){
            $type = '';
        }
        $now = time();
        switch ($type){
            case '':
                //未领取的优惠券,优惠券在发放时间段内，并且用户没有领取记录
                //及未使用的优惠券
                $sql1 = "SELECT 'to_get',id,coupon_money,min_money,coupon_img,use_start_date,use_end_date,coupon_title FROM ".M()->pre."coupon AS c WHERE send_start_date<$now AND send_end_date>$now AND (SELECT COUNT(id) FROM ".M()->pre."user_coupon AS uc WHERE uc.user_id='".$this->user_id."' AND uc.coupon_id=c.id)='0' AND (coupon_num=0 OR (coupon_num>0 AND (SELECT COUNT(id) FROM ".M()->pre."user_coupon AS uc WHERE uc.coupon_id=c.id)<c.coupon_num))";
                $sql2 = "SELECT 'to_use',c.id,c.coupon_money,c.min_money,c.coupon_img,c.use_start_date,c.use_end_date,c.coupon_title FROM ".M()->pre."user_coupon AS uc LEFT JOIN ".M()->pre."coupon AS c ON uc.coupon_id=c.id WHERE uc.user_id='".$this->user_id."' AND uc.use_time='0' AND c.use_end_date>$now";
                $sql = $sql1.' UNION '.$sql2;
                break;
            case 'unuse':
                //未使用
                $sql = "SELECT c.coupon_money,c.min_money,c.coupon_img,c.use_start_date,c.use_end_date,c.coupon_title FROM ".M()->pre."user_coupon AS uc LEFT JOIN ".M()->pre."coupon AS c ON uc.coupon_id=c.id WHERE uc.user_id='".$this->user_id."' AND uc.use_time='0' AND c.use_end_date>$now";
                break;
            case 'exceed':
                //已过期
                $sql = "SELECT 'exceed',c.coupon_money,c.min_money,c.coupon_img,c.use_start_date,c.use_end_date,c.coupon_title FROM ".M()->pre."user_coupon AS uc LEFT JOIN ".M()->pre."coupon AS c ON uc.coupon_id=c.id WHERE uc.user_id='".$this->user_id."' AND uc.use_time='0' AND c.use_end_date<$now";
                break;
        }
        $coupon_list = M()->query($sql);
        foreach ($coupon_list as $k=>$v){
            $v['coupon_img'] = substr(__ROOT__, 0, -6).$v['coupon_img'];
            $v['coupon_val'] = intval($v['coupon_money']);
            $v['min_money'] = intval($v['min_money']);
            $v['use_start_date'] = date('m/d',$v['use_start_date']);
            $v['use_end_date'] = date('m/d',$v['use_end_date']);

            if(isset($v['to_get']) && $v['to_get']=='to_get'){
                $v['get_url'] = url('coupon/async_get_coupon',array('id'=>$v['id']));
                $v['opt_text'] = '立即领取';
            }
            if(isset($v['to_get']) && $v['to_get']=='to_use'){
                $v['get_url'] = url('Category/top_all');
                $v['opt_text'] = '去使用';
                $v['type'] = 'unuse';
            }
            if(isset($v['exceed']) && $v['exceed']=='exceed'){
                $v['opt_text'] = '已过期';
            }

            $v['type'] = isset($v['type'])?$v['type']:$type;
            $coupon_list[$k] = $v;
        }
        $this->assign('page_title', C('shop_title').' 领取优惠券');
        $this->assign('coupon_list', $coupon_list);
        $this->assign('type',$type);
        $this->display('coupon_list.dwt');
    }

    /**
     * 异步领取优惠券
     */
    public function async_get_coupon(){

        $coupon_id = intval($_REQUEST['id']);
        $coupon = M()->query("SELECT * FROM ".M()->pre."coupon WHERE id='$coupon_id' LIMIT 1");
        if(!$coupon){
            echo json_encode(array('error'=>1,'info'=>'优惠券不存在'));die();
        }else{
            $coupon = $coupon[0];
        }
        /*验证优惠券发放时间*/
        $now = time();
        if($coupon['send_start_date']>$now){
            echo json_encode(array('error'=>1,'info'=>'优惠券还没开始发放,发放时间'.date('Y/m/d',$coupon['send_start_date']).'--'.date('Y-m-d',$coupon['send_end_date'])));die();
        }
        if($coupon['send_end_date']<$now){
            echo json_encode(array('error'=>1,'info'=>'优惠券已停止发放,发放时间'.date('Y/m/d',$coupon['send_start_date']).'--'.date('Y-m-d',$coupon['send_end_date'])));die();
        }
        /*验证用户是否已领取*/
        $have_get = M()->query("SELECT COUNT(id) AS num FROM ".M()->pre."user_coupon WHERE user_id='".$this->user_id."' AND coupon_id='$coupon_id'");
        $have_get_num = $have_get[0]['num'];
        if($have_get_num>0){
            echo json_encode(array('error'=>1,'info'=>'您已经领过该优惠券了','have_get'=>1));die();
        }
        /*验证优惠券数量*/
        if($coupon['coupon_num']>0){
            $have_send = M()->query("SELECT COUNT(id) AS num FROM ".M()->pre."user_coupon WHERE coupon_id='$coupon_id' LIMIT 1");
            $have_send_num = $have_send[0]['num'];
            if($have_send_num>=$coupon['coupon_num']){
                echo json_encode(array('error'=>1,'info'=>'优惠券已发放完了','send_out'=>1));die();
            }
        }
        $data = array(
            'user_id'=>$this->user_id,
            'coupon_id'=>$coupon_id,
            'get_time'=>time(),
            'sn'=>mb_substr(date('ymdHis',time()).$this->user_id.$coupon_id.rand(100,1000),0,20)
        );
        $insert_id = $this->model->table('user_coupon')->data($data)->insert();
        if($insert_id){
            $res = array(
                'error'=>0,
                'info'=>'已领取'
            );
        }else{
            $res = array(
                'error'=>1,
                'info'=>'领取失败'
            );
        }
        echo json_encode($res);exit();
    }

}