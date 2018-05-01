<?php
/**
 * 优鲜活动
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/22
 * Time: 13:54
 */
defined('IN_ECTOUCH') or die('Deny Access');

class YxactivityController extends CommonController
{
    public function index()
    {
        if(IS_POST){
            $name = I('post.name', '');
            $phone = I('post.phone', '');
            $addr = I('post.addr', '');
            $pic1 = I('pic1', '');
            $pic2 = I('pic2', '');
            $size = '均码';
            //TODO validate form data
            if(!$name){
                echo json_encode(array('error'=>1, 'info'=>'请填写收货人姓名'));die;
            }
            if(!$phone){
                echo json_encode(array('error'=>1, 'info'=>'请填写收货人手机号'));die;
            }
            if(!preg_match('/^1(\d){10}$/', $phone)){
                echo json_encode(array('error'=>1, 'info'=>'手机号格式不正确'));die;
            }
            if(!$addr){
                echo json_encode(array('error'=>1, 'info'=>'请填写收货地址'));die;
            }
            $str = md5($name.$phone);
            $exists = M()->query("SELECT id FROM ecs_activity_clothes WHERE md5str='$str'");
            if($exists){
                echo json_encode(array('error'=>1, 'info'=>'您已经提交过了'));die;
            }
            $pic = array();
            $pic[] = save_base64_pic('images/clothes/', $pic1);
            $pic[] = save_base64_pic('images/clothes/', $pic2);
            $data = array(
                'name' => $name,
                'phone' => $phone,
                'addr' => $addr,
                'pic1' => $pic[0],
                'pic2' => $pic[1],
                'size' => $size,
                'addtime' => time(),
                'md5str' => md5($name.$phone),
                'size' => $size
            );
            $res = model('ClipsBase')->insert_yx_activity_clothes($data);
            if($res){
                echo json_encode(array('error'=>0, 'info'=>'提交成功'));die;
            }else{
                echo json_encode(array('error'=>1, 'info'=>'提交失败'));die;
            }
        }else{
            echo json_encode(array('error'=>1, 'info'=>'请求错误'));die;
        }

    }
}