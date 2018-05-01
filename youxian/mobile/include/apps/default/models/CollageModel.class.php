<?php
/*
* File: CollageModel.class.php
* Author: Arliki
* Date: 2018-01-11 16:16a
*/
class CollageModel extends BaseModel {
    function collage_pay($g_id,$c_id,$u_id,$w_os,$os,$p){
        $ntime=time();
        $check_sql=M()->query("select add_time from ecs_collage_pay where order_sn='$os' and wx_order_sn='$w_os'");
        if ($check_sql[0]["add_time"]){
        }else{
            if($c_id) {
                $sql = "insert into ecs_collage_pay (user_id, collage_id, wx_order_sn, order_sn, add_time, money) VALUES ('$u_id','$c_id','$w_os','$os','$ntime','$p')";
                if (M()->query($sql)) {
                    $old = M()->query("select users_id,max from ecs_collage_list where id='$c_id'");
                    if ($old[0]["users_id"]) {
                        $check_user = explode(',', $old[0]["users_id"]);
                        if (in_array($u_id,$check_user)) {
                        }else{
                            $users_id = $old[0]['users_id'] . ",$u_id";
                            $users = explode(',', $users_id);
                            if (count($users) > $old[0]['max']) {
                                $sql3 = "insert into ecs_wxrefun (user_id, price, order_sn, add_time) VALUES ('$u_id','$p','$os','$ntime')";
                                M()->query($sql3);
                            } else {
                                $sql2 = "update ecs_collage_list set users_id='$users_id' where id='$c_id'";
                                if (M()->query($sql2)) {
                                    if ($old[0]['max'] - count($users) >= 1) {
                                        $sql3="update ecs_collage_index set surplus='".($old[0]['max']-count($users))."' where collage_id='$c_id'";
                                    } else {
                                        $sql3 = "delete from ecs_collage_index where collage_id='$c_id'";
                                        M()->query("update  ecs_collage_list set status=3 where id='$c_id'");
                                    }
                                    M()->query($sql3);
                                    $a1=array($g_id,$c_id,$u_id,$w_os,$os,$p);
                                    write_log('collage_insert_ok.json',json_encode($a1),1);
                                } else {
                                    write_log('collage_insert_error.json',$sql2,1);
                                }
                            }
                        }
                    }
                }
            }else{
                $need=M()->query("select collage_max,collage_time from ecs_goods where goods_id='$g_id'");
                $max=$need[0]['collage_max'];
                $end_time=$need[0]['collage_time']*3600+$ntime;
                $sql2="insert into ecs_collage_list (goods_id, users_id,max, end_time) VALUES ('$g_id','$u_id','$max','$end_time')";
                $a1=array($g_id,$c_id,$u_id,$w_os,$os,$p);
                if(M()->query($sql2)) {
                    $co_id = M()->query("select id from ecs_collage_list where goods_id='$g_id' and end_time='$end_time' and status='0' order by id desc");
                    $co_id = $co_id[0]['id'];
                    M()->query("insert into ecs_collage_pay (user_id, collage_id, wx_order_sn, order_sn, add_time, money) VALUES ('$u_id','$co_id','$w_os','$os','$ntime','$p')");
                    $goods = M()->query("select goods_name,collage_max from ecs_goods where goods_id='$g_id'");
                    $sql3 = "insert into ecs_collage_index (collage_id, goods_id, goods_name, end_time, surplus) VALUES ('$co_id','$g_id','" . $goods[0]['goods_name'] . "','$end_time','" . ($goods[0]['collage_max'] - 1) . "')";
                    if(M()->query($sql3)){
                        write_log('collage_insert_ok.json', "fir--" . $sql2, 1);
                    }else{
                        write_log('collage_insert_error.json', "indes_err".$sql3, 1);
                    }
                }else{
                    write_log('collage_insert_error.json',"list_err".$sql2, 1);
                }
            }
        }
    }
    function get_head($users_id){
        if (is_array($users_id)){
            $new=array();
            for($i=0;$i<count($users_id);$i++){
                $a=M()->query("select nickname,headimgurl from ecs_wechat_user where ect_uid='$users_id[$i]'");
                $new[$i]['head']=$a[0]['headimgurl'];
                if(count(str_split($a[0]['nickname']))>6) {
                    $new[$i]['nick'] = $this->hideStr($a[0]['nickname'], 6, 10, 1);
                }else{
                    $new[$i]['nick']=$a[0]['nickname'];
                }
            }
            return $new;
        }else{
            return false;
        }
    }
    function hideStr($string, $begin=0, $len = 4, $type = 0, $glue = "@") {
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
