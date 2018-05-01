<?php
if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$cron_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/auto_super_bingo.php';
if (file_exists($cron_lang)) {
    global $_LANG;
    include_once($cron_lang);
}
/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;
    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');
    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'auto_super_desc';
    /* 作者 */
    $modules[$i]['author']  = 'Arliki';
    /* 网址 */
    $modules[$i]['website'] = '';
    /* 版本号 */
    $modules[$i]['version'] = '0.2.8';
    /* 配置信息 一般这一项通过serialize函数保存在cron表的中cron_config这个字段中*/
    $modules[$i]['config']  = array(
        array('name' => 'auto_super_rule', 'type' => 'select', 'value' => '1')
    );
    //name：计划任务的名称，type：类型(text,textarea,select…)，value：默认值
    return;
}
$now_time=gmtime();
$low_time=$now_time-3600*12;
$rules = !empty($cron['auto_super_rule']) ? $cron['auto_super_rule'] : 1;
//获取中奖基本信息。
$pre_order="select count(act_id) num,super,bid_price,act_id from ecs_pre_order group by act_id order by act_id desc";
$super=$db->getAll($pre_order);
$super_id=$super[0]['super'];//商品号
$act_id=$super[0]['act_id'];//活动号
$super_num=$super[0]['num'];//所有人数
$super_price=$super[0]['bid_price'];//价格
//检测该活动是否已经抽奖
$check1=$db->getAll("select luck from ecs_goods_activity where act_id='$act_id'");
//删除空地址
$db->query("delete from ecs_user_address where country = 0");
if($check1[0]['luck']==1){
}else {
    //写入日志记录
    write_log('insert_order.json','',-1);
    write_log('insert_goods.json','',-1);
    //删除重复操作用户
    $rep=$db->getAll("select userid,num from (select count(userid) num,userid from ecs_pre_order where act_id='$act_id' group by userid) as t where num>1");
    if($rep[0]["num"]){
        for ($i=0;$i<count($rep);$i++){
            $db->query("delete from ecs_pre_order where  userid='".$rep[$i]["userid"]."' order by id desc limit ".($rep[$i]["num"]-1));
        }
    }
    $check1=$db->query("update ecs_goods_activity set luck=1 where act_id='$act_id'");
//↑↑↑↑↑↑↑↑检测结束并更新luck值
    $goods_att = "select goods_sn,goods_name,goods_attr_id from ecs_goods AS goods LEFT JOIN ecs_goods_attr AS attr ON goods.goods_id=attr.goods_id WHERE goods.goods_id=" . $super_id;
    $goods_attr = $db->getAll($goods_att);
    $goods_sn = $goods_attr[0]['goods_sn'];
    $goods_name = $goods_attr[0]['goods_name'];
    $goods_attr_id = $goods_attr[0]['goods_attr_id'];
//获取最大数量和价格a吧
    $persons = "select max_num from ecs_goods_activity where act_id=" . $act_id . " order by end_time desc";
    $person = $db->getAll($persons);
//获取用户组
    $userids = "select userid,order_sn from ecs_pre_order where super='$super_id' and bid_price='$super_price' and act_id='$act_id' ";
    $user_ids = $db->getAll($userids);
    $olduser = $user_ids;
    $new_user = array();
    $white = array(
        '2192', '2604', '4132', '6376', '2792', '261','4133','1032','3504','7141'
    );
    $wnum = 0;
    for ($t = 0; $t < count($user_ids); $t++) {
        if (in_array($user_ids[$t]['userid'], $white)) {
            $step0 = goto_order_info($user_ids[$t]['userid'], $super_price, $act_id, $super_id, $goods_sn, $goods_name, $goods_attr_id);
            $user_ids[$t]['userid'] = -$t;
            $wnum += $step0;
        }
    }
    $wnum > 0?write_log('white.json',"数量:" . $wnum . "名单:" . json_encode($white) . "活动:" . $act_id,1) :'';
    $ch = 0;
    for ($i = 0; $i < count($user_ids); $i++) {
        $user_ids[$i]['userid'] > 0?'':$ch += 1;
        if ($user_ids[$i + $ch]["userid"] > 0) {
            $new_user[$user_ids[$i + $ch]["userid"]] = "a";
            $ch = 0;
        }
    }
    $last_num=$person[0]['max_num'] - $wnum;
    $info = (array)array_rand($new_user, $last_num);
    $num = 0;
//插入中奖信息
    for ($k = 0; $k < count($info); $k++) {
        if ($info[$k] > 0) {
            $step = goto_order_info($info[$k], $super_price, $act_id, $super_id, $goods_sn, $goods_name, $goods_attr_id);
            $num += $step;
        }
    }
    $num2 = 0;
//未中奖记录
    for ($j = 0; $j < count($olduser); $j++) {
        $user=array();
        if (in_array($olduser[$j]['userid'], $info) || in_array($olduser[$j]['userid'], $white)) {
            $step5 = 0;
        } else {
            //登记退款表
            $GLOBALS['db']->query("insert into ecs_wxrefun (user_id, order_sn, add_time, price, act_id, super) VALUES ('".$olduser[$j]['userid']."','".$olduser[$j]['order_sn']."','".gmtime()."','$super_price','$act_id','$super_id')");
            $step5 = 1;
        }
        $num2 += $step5;
    }
    $content="重复插入用户：" . json_encode($rep) .  "所有用户:" . json_encode($olduser) . "候选用户id：" . json_encode($new_user) . "中奖ID:" . json_encode($info) . "中奖数:".$num."+".$wnum."返还数:" . $num2;
    write_log('super_all.json',$content,1);
}
function goto_order_info($userid,$price,$act_id,$super_id,$goods_sn,$goods_name,$goods_attr_id){
    $addre="select consignee,country,province,city,district,address,mobile from ecs_user_address where user_id=".$userid." order by address_id desc";
    $addre=$GLOBALS['db']->getAll($addre);
    $user_ids="select wx_order_sn,add_time from ecs_pre_order where userid='$userid' and super='$super_id' and bid_price='$price' and act_id='$act_id' order by add_time desc";
    $users=$GLOBALS['db']->getAll($user_ids);
    $order_sn=$users[0]["wx_order_sn"];
    $consignee =$addre[0]["consignee"];
    $country =$addre[0]["country"];
    $province =$addre[0]["province"];
    $city =$addre[0]["city"];
    $district =$addre[0]["district"];
    $address =$addre[0]["address"];
    $mobile =$addre[0]["mobile"];
    $times=$users[0]["add_time"];
    //插入订单
    $insert_order="insert into ecs_order_info (order_sn, user_id, order_status, shipping_status, pay_status, consignee, country, province, city, district, address, mobile, pay_id, pay_name, how_oos,goods_amount, money_paid, referer, add_time, confirm_time, pay_time) VALUES ('$order_sn','$userid','1','0','2','$consignee','$country','$province','$city','$district','$address','$mobile','1','微信支付','等待所有商品备齐后再发','$price','$price','自动抽奖','$times','$times','$times') ";
    $step1=$GLOBALS['db']->query($insert_order);
    $GLOBALS['db']->query("update ecs_pre_order set bingo='1' where wx_order_sn='$order_sn' and userid='$userid'");
    $order_ida="select order_id from ecs_order_info where user_id='$userid' and order_sn='$order_sn'";
    $order_id=$GLOBALS['db']->getall($order_ida);
    $order_id=$order_id[0]['order_id'];
    $content=$order_ida."++".$insert_order;
    write_log('insert_order.json',$content,0);
    //插入商品表
    $insert_goods="insert into ecs_order_goods (order_id, goods_id, goods_name, goods_sn, goods_number, market_price, goods_price, goods_attr,is_real, goods_attr_id) VALUES ('$order_id','$super_id','$goods_name','$goods_sn','1','$price','$price','抽奖活动','1','$goods_attr_id')";
    $step2=$GLOBALS['db']->query($insert_goods);
    $contents=$insert_goods;
    write_log('insert_goods.json',$contents,0);
    if ($step1 && $step2){
        return 1;
    }else{
        return -1;
    }
}
?>