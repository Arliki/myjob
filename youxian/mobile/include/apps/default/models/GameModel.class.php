<?php
defined('IN_ECTOUCH') or die('Deny Access');

class GameModel extends BaseModel {
    public function get_user_info($user_id){
        $sql = "SELECT wu.*,u.pay_points as guodou,u.is_validated AS user_validate,u.mobile_phone " .
            "FROM " . $this->pre . "wechat_user AS wu " .
            "LEFT JOIN " . $this->pre . "users AS u ON wu.ect_uid=u.user_id " .
            "WHERE wu.ect_uid = '$user_id' ";
        $res = $this->row($sql);
        return $res;
    }

    public function agreed_txt(){
        return $this->row("SELECT * FROM ".$this->pre."game_deals WHERE user_id=$_SESSION[user_id]");
    }
    public function phone_exist($phone)
    {
        $sql = "SELECT COUNT(*) as count FROM " . $this->pre . "users WHERE mobile_phone = '$phone'";
        $res = $this->row($sql);
        return $res['count'];
    }
    public function update_user_phone($phone)
    {
        $user_phone = $this->row("SELECT is_validated,mobile_phone FROM ".$this->pre."users WHERE user_id=$_SESSION[user_id]");
        if(($user_phone['is_validated'] & USER_MOBILE_VALID) && $user_phone['mobile_phone']==$phone){
            return array('success'=>1,'info'=>'手机号已认证过了');
        }
        $is_validated = ($user_phone['is_validated'] & USER_MOBILE_VALID)?$user_phone['is_validated']:$user_phone['is_validated']+USER_MOBILE_VALID;
        $data = array(
            'is_validated'=> $is_validated,
            'mobile_phone'=>$phone
        );
        $this->table = 'users';
        $res = $this->update("user_id=$_SESSION[user_id]", $data);
        if($res){
            return array('success'=>1, 'info'=>'操作成功');
        }else{
            return array('success'=>0, 'info'=>'操作失败');
        }
    }
    public function agree_txt(){
        if($this->agreed_txt()){
            return 1;
        }
        $data = array(
            'addtime'=>gmtime(),
            'ip'=>get_client_ip(),
            'user_id'=>$_SESSION['user_id'],
            'ua'=>stripslashes($_SERVER['HTTP_USER_AGENT'])
        );
        $this->table = 'game_deals';
        return $this->insert($data);
    }

    public function get_user_awards($uid,$gid){
        $this->table = 'game_logs';
        $res = $this->query("SELECT l.id,l.addtime,l.gotit,l.gottime,a.title,a.img FROM ".$this->pre."game_logs AS l LEFT JOIN ".$this->pre."game_awards AS a ON l.awards_id=a.id WHERE l.game_id='$gid' AND l.user_id='$uid' AND l.awards_id>0 ORDER BY l.gotit ASC,l.addtime DESC");
        return $res;
    }

    public function get_games($all = false){
        $where = $all===true?"":" WHERE disabled=0 ";
        $sort = " ORDER BY sort_order ASC";
        $sql = "SELECT * FROM ".$this->pre."games ".$where.$sort;
        return $this->query($sql);
    }

    public function get_game_info($gid){
        $sql = "SELECT * FROM ".$this->pre."games where id='$gid'";
        return $this->row($sql);
    }

    public function get_game_act($act_id,$game_id){
        $sql = "SELECT id,title,costs,game_id FROM ".$this->pre."game_actions where id='$act_id'";
        $act = $this->row($sql);
        return $act['game_id']==$game_id?$act:false;
    }

    public function get_game_acts($game_id, $multi_act){
        $sql = "SELECT id,title,costs,game_id FROM ".$this->pre."game_actions WHERE game_id='$game_id' ORDER BY costs ASC";
        if($multi_act==1){
            return $this->query($sql);
        }
        return $this->row($sql);
    }

    public function get_game_awards($game_id){
        $sql = "SELECT * FROM ".$this->pre."game_awards WHERE game_id='$game_id' ORDER BY rate ASC";
        return $this->query($sql);
    }

    public function get_award_info($award_id){
        return $this->row("SELECT * FROM ".$this->pre."game_awards WHERE id='$award_id'");
    }

    public function lucky_play($game,$user_id,$game_act,$game_award){
        $game_id = $game['id'];
        $this->query('SET AUTOCOMMIT=0');
        //减少积分
        $old_pay_point = $this->row('SELECT pay_points FROM '.$this->pre."users WHERE user_id='$user_id'");
        $old_pay_point = $old_pay_point['pay_points'];
        if($old_pay_point*1<$game_act['costs']*1){
            $this->query('SET AUTOCOMMIT=1');
            return array('error'=>1,'info'=>'积分不足');
        }
        $pay_point = $old_pay_point*1-$game_act['costs']*1;
        $sql_update_integral = "UPDATE ".$this->pre."users SET pay_points='$pay_point' WHERE user_id='$user_id'";
        $update_integral = $this->query($sql_update_integral);
        //记录积分明细
        $this->table = 'account_log';
        $integral_data = array(
            'user_id'=>$user_id,
            'pay_points'=>-1*$game_act['costs'],
            'change_time'=>gmtime(),
            'change_desc'=>'游戏消耗('.$game['title'].')',
            'change_type'=>99
        );
        $insert_integral_log = $this->insert($integral_data);
        //增加操作记录
        $award_txt = $game_award?
            $game_award['title'].'('.($game_award['awards_type']==1?'商品ID':'果豆').$game_award['awards_value'].')'
            :'未中奖';
        $remark = $game_act['title']."(-".$game_act['costs'].");奖品:".$award_txt;
        $log_data = array(
            'game_id'=>$game_id,
            'user_id'=>$user_id,
            'action_id'=>$game_act['id'],
            'awards_id'=>$game_award['id']?$game_award['id']:0,
            'addtime'=>gmtime(),
            'remark'=>$remark
        );
        $this->table = 'game_logs';
        $insert_game_log = $this->insert($log_data);

        if($update_integral && $insert_game_log && $insert_integral_log){
            $this->query('COMMIT');
            $ret = array('success'=>1,'info'=>'操作成功','new_guodou'=>$pay_point,'log_id'=>$insert_game_log);
        }else{
            $this->query('ROLLBACK');
            $ret = array('error'=>1,'info'=>'更新数据失败');
        }
        $this->query("SET AUTOCOMMIT=1");
        return $ret;
    }

    public function get_game_log($log_id){
        $sql = "SELECT * FROM ".$this->pre."game_logs WHERE id='$log_id'";
        return $this->row($sql);
    }
    public function get_award_send_count($award_id)
    {
        $sql = "SELECT COUNT(id) AS c FROM ".$this->pre."game_logs"." WHERE awards_id='$award_id'";
        $count = $this->row($sql);
        return $count?$count['c']:0;
    }
    //领取奖品
    public function got_award($log, $award){
//        $award = $this->row("SELECT * FROM ".$this->pre."game_awards WHERE id='".$log['awards_id']."'");
        $game = $this->get_game_info($log['game_id']);
        $this->query("SET AUTOCOMMIT=0");
        if($award['awards_type']==0){
            //积分奖励
            //积分明细
            $this->table = 'account_log';
            $integral_data = array(
                'user_id'=>$log['user_id'],
                'pay_points'=>1*$award['awards_value'],
                'change_time'=>gmtime(),
                'change_desc'=>'游戏奖励('.$game['title'].')',
                'change_type'=>99
            );
            $insert_integral_log = $this->insert($integral_data);
            //积分余额增加
            $old_pay_point = $this->row('SELECT pay_points FROM '.$this->pre."users WHERE user_id='".$log['user_id']."'");
            $old_pay_point = $old_pay_point['pay_points'];
            $pay_point = $old_pay_point*1+$award['awards_value']*1;
            $sql_update_integral = "UPDATE ".$this->pre."users SET pay_points='$pay_point' WHERE user_id='".$log['user_id']."'";
            $update_integral = $this->query($sql_update_integral);
            //更改领奖状态
            $update_log_state = $this->query("UPDATE ".$this->pre."game_logs SET gotit='1',gottime='".gmtime()."' WHERE id='".$log['id']."'");
            if($insert_integral_log && $update_integral && $update_log_state){
                $this->query("COMMIT");
                $ret = array('success'=>'1','info'=>'操作成功');
            }else{
                $this->query("ROLLBACK");
                $ret = array('error'=>1,'info'=>'数据库更新失败');
            }
        }else{
            //实物奖励
            //插入订单表
            //获取用户默认收货人地址
            $default_address_id = $this->row("SELECT address_id FROM ".$this->pre."users WHERE user_id='".$log['user_id']."' LIMIT 1");
            if(!$default_address_id){
                $consignee = $this->row("select consignee,country,province,city,district,address,mobile from ".$this->pre."user_address where user_id=".$log['user_id']." ORDER BY address_id DESC LIMIT 1");
            }else{
                $default_address_id = $default_address_id['address_id'];
                $consignee = $this->row("select consignee,country,province,city,district,address,mobile from ".$this->pre."user_address where address_id=".$default_address_id." LIMIT 1");
                if(!$consignee){
                    $consignee = $this->row("select consignee,country,province,city,district,address,mobile from ".$this->pre."user_address where user_id=".$log['user_id']." ORDER BY address_id DESC LIMIT 1");
                }
            }
            $order_data = array(
                'order_sn'=>get_order_sn(),
                'user_id'=>$log['user_id'],
                'order_status'=>1,
                'shipping_status'=>0,
                'pay_status'=>2,
                'consignee'=>$consignee['consignee'],
                'country'=>$consignee['country'],
                'province'=>$consignee['province'],
                'city'=>$consignee['city'],
                'district'=>$consignee['district'],
                'address'=>$consignee['address'],
                'mobile'=>$consignee['mobile'],
                'pay_id'=>1,
                'pay_name'=>'微信支付',
                'how_oos'=>'等商品备齐后再发货',
                'goods_amount'=>0,
                'money_paid'=>0,
                'referer'=>'游戏中奖('.$game['title'].')',
                'add_time'=>gmtime(),
                'confirm_time'=>gmtime(),
                'pay_time'=>gmtime()
            );
            $this->table = 'order_info';
            $new_order_id = $this->insert($order_data);
            //插入订单商品表
            $goods_info = $this->row("SELECT * FROM ".$this->pre."goods WHERE goods_id='".$award['awards_value']."'");
            $default_goods_attr = array();
            $order_goods_data = array(
                'order_id'=>$new_order_id,
                'goods_id'=>$award['awards_value'],
                'goods_name'=>$goods_info['goods_name'],
                'goods_sn'=>$goods_info['goods_sn'],
                'goods_number'=>1,
                'market_price'=>0,
                'goods_price'=>0,
                'goods_attr'=>$default_goods_attr['attr_name'],
                'is_real'=>$goods_info['is_real'],
                'goods_attr_id'=>$default_goods_attr['attr_id']
            );
            $this->table = 'order_goods';
            $insert_order_goods = $this->insert($order_goods_data);
            //更改领奖状态
            $update_log_state = $this->query("UPDATE ".$this->pre."game_logs SET gotit='1',gottime='".gmtime()."' WHERE id='".$log['id']."'");
            if($insert_order_goods && $new_order_id && $update_log_state){
                $this->query("COMMIT");
                $ret = array('success'=>'1','info'=>'操作成功');
            }else{
                $this->query("ROLLBACK");
                $ret = array('error'=>1,'info'=>'数据库更新失败');
            }
        }
        $this->query("SET AUTOCOMMIT=1");
        return $ret;
    }
}