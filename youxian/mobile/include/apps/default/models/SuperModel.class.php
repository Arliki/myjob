<?php
/*
* File: SuperModel.class.php
* Author: Arliki
* Date: 2017-12-01 10:55
*
*/
defined('IN_ECTOUCH') or die('Deny Access');

class SuperModel extends BaseModel {
    function get_super($id){
        $sql = "select * from " .$this->pre."goods_activity where act_id = ".$id;
        $super=M()->query($sql);
        return $super;
    }
    function get_int($str,$len){
        $new=0;
        $k=0;
        $check=1;
        do{
            if(isset($str[$len])){
                $len+=2;
                $check=1;
            }else{
                $check=0;
            }
        }while($check);
        for ($i = $len;$i>=0;$i--){
            if (isset($str[$i]) && $str[$i]!='"'){
                $new+=floor($str[$i])*pow(10,$k);
                $k++;
            }
        }
        return $new;
    }
    function check_node(){
        $select="select bid_price from ecs_snatch_log where snatch_id= '".$_SESSION['act_id']."' and user_id='".$_SESSION['user_id']."' ";
        $checkad=M()->query($select);
        $bid_price=$checkad[0]["bid_price"];
        if (isset($bid_price)) {
            switch ($bid_price) {
                case $bid_price < 0:
                    $node = "You're on the blacklist!";
                    break;
                case $bid_price > 0:
                    $node = '您已经参与过该活动咯';
                    break;
                default:
                    $node = 0;
            }
        }else{
            $node = 0;
        }
        return $node;
    }
    function hideStr($string, $begin=0, $len = 4, $type = 0, $glue = "@") {
        if (empty($string)){return false;}
        $chang=strlen($string);
        if($chang<$begin+$len){
            if($chang>$len+1){
                $begin=floor(($chang-$len)/2);
            }else{
                $begin=floor($begin/2);
                $len=floor($len/2);
            }
        }
        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }
        if ($type == 0) {
            for ($i = $begin; $i < ($begin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", $array);
        }else if ($type == 1) {
            $array = array_reverse($array);
            for ($i = $begin; $i < ($begin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = ".";
            }
            $string = implode("", array_reverse($array));
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
    function pre_order($userid,$act_id,$super,$price,$wx_order_sn,$order_sn,$disa){
        if($disa=="disw"){
            $che="select add_time from ecs_pre_order where act_id='$act_id' and userid='$userid' and super='$super'";
            if(M()->query($che)) {
            }else {
                $sql = "insert into ecs_pre_order (userid, add_time, bid_price, super, wx_order_sn, act_id, order_sn) VALUES ('$userid','" . gmtime() . "','$price','$super','$wx_order_sn','$act_id','$order_sn')";
                if (M()->query($sql)) {
                    write_log('super_alert_sql.json',"sql:" . "disw_ok--" . $sql,0);
                } else {
                    write_log('super_alert_sql.json',"sql:" . "disw_error--" . $sql,0);
                }
            }
        }else if($disa=="disa"){
            $che2="select add_time from ecs_wxrefun where act_id='$act_id' and user_id='$userid' ";
            if(M()->query($che2)) {
            }else {
                M()->query("select id from ecs_wxrefun where user_id='$userid' and act_id='$act_id' and super='$super'") ? '' : M()->query("insert into ecs_wxrefun (user_id, order_sn, add_time, act_id, super, price) VALUES ('$userid','$order_sn','" . gmtime() . "','$act_id','$super','$price')");
            }
        }else if($disa=="disb"){
            $che="select add_time from ecs_pre_order where act_id='$act_id' and userid='$userid' and super='$super'";
            if(M()->query($che)) {
            }else {
                $sql = "insert into ecs_pre_order (userid, add_time, bid_price, super, wx_order_sn, act_id, order_sn) VALUES ('$userid','" . gmtime() . "','$price','$super','$wx_order_sn','$act_id','$order_sn')";
                if (M()->query($sql)) {
                    write_log('super_alert_sql.json',"sql:" . "disw_ok--" . $sql,0);
                } else {
                    write_log('super_alert_sql.json',"sql:" . "disw_error--" . $sql,0);
                }
            }
        }else{}
//        $che="select add_time from ecs_pre_order where act_id='$act_id' and userid='$userid' and super='$super'";
//        if(M()->query($che)) {
//        }else {
//            $black1 = $black2 = array();
//            //抽奖黑名单
//            //$black1 = array('6822', '6913', '6548', '2188', '5783', '830', '2819', '780', '1345', '7091', '6577', '1598', '3834', '4132', '5666', '5930');//暂时黑名单
//            $black2 = array('5814', '223');//永久黑名单
//            if (in_array($userid, $black1) || in_array($userid, $black2)) {
//                M()->query("select id from ecs_wxrefun where user_id='$userid' and act_id='$act_id' and super='$super'") ? '' : M()->query("insert into ecs_wxrefun (user_id, order_sn, add_time, act_id, super, price) VALUES ('$userid','$order_sn','" . gmtime() . "','$act_id','$super','$price')");
//            } else {
//                $sql = "insert into ecs_pre_order (userid, add_time, bid_price, super, wx_order_sn, act_id, order_sn) VALUES ('$userid','" . gmtime() . "','$price','$super','$wx_order_sn','$act_id','$order_sn')";
//        if (M()->query($sql)) {
//            write_log('super_alert_sql.json',"sql:" . "ok--" . $sql,0);
//        } else {
//            write_log('super_alert_sql.json',"sql:" . "error--" . $sql,0);
//        }
//            }
//        }
    }
}
