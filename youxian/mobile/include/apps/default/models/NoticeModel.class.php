<?php
/*
* File: NoticeModel.class.php
* Author: Arliki
* Date: 2017-12-28 9:33
*
*/
defined('IN_ECTOUCH') or die('Deny Access');
class NoticeModel extends BaseModel{
    /**
     * 是否有可用优惠券
     */
    function check_coupon(){
        $user_id=$_SESSION['user_id'];
        if($user_id) {
            $ntime = time();
            //是否有该用户
            $check_id=M()->query("select id from ecs_user_notice where user_id='$user_id'");
            if($check_id) {
                $check_time = M()->query("select coupon_time from ecs_user_notice where user_id='$user_id'");
                if ($check_time[0]["coupon_time"] > $ntime) {
                    return 0;
                } else {
                    $check_cou = "select id from ecs_coupon where send_end_date>'$ntime' ";
                    $couid = M()->query($check_cou);
                    if ($couid) {
                        $couids = array();
                        foreach ($couid as $k => $v) {
                            array_push($couids, $v['id']);
                        }
                    } else {
                        return 0;
                    }
                    $check_user = "select coupon_id from ecs_user_coupon where user_id='$user_id' ";
                    $user_cou = M()->query($check_user);
                    if ($user_cou) {
                        $userids = array();
                        foreach ($user_cou as $k => $v) {
                            array_push($userids, $v['coupon_id']);
                        }
                    } else {
                        return 1;
                    }
                    $che = 0;
                    for ($i = 0; $i < count($couids); $i++) {
                        if ($che == 0) {
                            in_array($couids[$i], $userids) ? ' ' : $che = 1;
                        } else {
                            return $che;
                        }
                    }
                    return $che;
                }
            }else{
                M()->query("insert into ecs_user_notice (user_id) VALUES ('$user_id')");
                return 0;
            }
        }else{
            return 0;
        }
    }

    /**
     * 检查商城通知
     */
    function check_shop_notify(){
        $user_id=$_SESSION['user_id'];
        if ($user_id){
            $ulist=M()->query("select shop_notify from ecs_user_notice where user_id='$user_id'");
            $alist=M()->query("select count(nid) num from ecs_shop_notify");
            if (empty($ulist[0]['shop_notify']) && intval($alist[0]['num']!=0)){
                return 1;
            }
            $uid=explode(',',$ulist[0]['shop_notify']);
            if (count($uid)<intval($alist[0]['num']) && intval($alist[0]['num']!=0)){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }
    /**
     * 获取商城通知
     */
    function get_shop_notify($user_id){
        $unid=M()->query("select shop_notify from ecs_user_notice where user_id='$user_id'");
        $anid=M()->query("select nid,name,ctime,content from ecs_shop_notify order by nid desc");
        if (empty($unid[0]['shop_notify'])){
            $nunid=array();
        }else{
            $nunid=explode(',',$unid[0]['shop_notify']);
        }
        $res=array();
        count($anid)>count($nunid)?$res['update']=1:$res['update']=0;
        for ($i=0;$i<count($anid);$i++){
            if (in_array($anid[$i]['nid'],$nunid)){
                $anid[$i]['is_read']=1;
            }else{
                $anid[$i]['is_read']=0;
            }
            $anid[$i]['ctime']=date('Y-m-d H:i',$anid[$i]['ctime']);
            $anid[$i]['abs']=$this->hideStr($anid[$i]['content'],15,-1,1);
        }
        $_SESSION['up_id']=$anid[count($anid)-1]['nid'];
        $_SESSION['down_id']=$anid[0]['nid'];
        $res['con']=$anid;
        return $res;
    }
    function get_shop_detail($nid){
        $res=M()->query("select name,link,link_name,content,ctime from ecs_shop_notify where nid='$nid'");
        $res[0]['ctime']=date('Y-m-d H:i',$res[0]['ctime']);
        $user_id=$_SESSION['user_id'];
        $uid=M()->query("select shop_notify from ecs_user_notice where user_id='$user_id'");
        if(count($uid)!=0) {
            $uid = explode(',', $uid[0]['shop_notify']);
            if (in_array($nid,$uid)){
            }else{
                array_push($uid,$nid);
            }
            $uid=$uid.implode(',',$uid);
        }else{
            $uid=$nid;
        }
        M()->query("update ecs_user_notice set shop_notify='$uid' WHERE user_id='$user_id'");
        $data=array();
        $data['name']=$res[0]['name'];
        $data['content']=$res[0]['content'];
        $data['ctime']=$res[0]['ctime'];
        $data['link']=$res[0]['link'];
        $data['link_name']=$res[0]['link_name'];
        if($nid<$_SESSION['down_id']){
            $data['down_id']=$nid+1;
        }
        if($nid>$_SESSION['up_id']){
            $data['up_id']=$nid-1;
        }
        return $data;
    }
    function update_shop_notify($user_id){
        $anid=M()->query("select nid from ecs_shop_notify order by nid desc");
        $arr=array();
        for ($i=0;$i<count($anid);$i++){
            array_push($arr,$anid[$i]['nid']);
        }
        $str=implode(',',$arr);
        M()->query("update ecs_user_notice set shop_notify='$str' where user_id='$user_id'");
    }
    function hideStr($string, $begin=0, $len = 4, $type = 0, $glue = "@") {
        $old=$string;
        if (empty($string)){return false;}
        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }
        if ($len<0){
            $n=array();
            for ($i = 0; $i < $begin; $i++) {
                if (isset($array[$i]))
                    $n[$i]=$array[$i];
            }
            $string = implode("", $n);
            if (mb_strlen($old)>$begin*3) {
                $string .= "...";
            }
            return $string;
        }
        if ($type == 0) {
            for ($i = $begin; $i < ($begin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", $array);
        }else if ($type == 1) {
            for ($i = $begin; $i < ($begin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = ".";
            }
            $string = implode("", $array);
        }else if ($type == 2) {
            $array = explode($glue, $string);
            $array[0] = $this->hideStr($array[0], $begin, $len, 1);
            $string = implode($glue, $array);
        } else if ($type == 3) {
            $array = explode($glue, $string);
            $array[1] = $this->hideStr($array[1], $begin, $len, 0);
            $string = implode($glue, $array);
        } else if ($type == 4) {
            $left = $begin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($length - $right); $i++) {
                if (isset($array[$i]))
                    $tem[] = $i >= $left ? "X" : $array[$i];
            }
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                $tem[] = $array[$i];
            }
            $string = implode("", $tem);
        }
        return $string;
    }
}
