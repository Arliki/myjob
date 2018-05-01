<?php
if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
header("Content-type:text/html;charset=utf-8");
$cron_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/auto_order_confirm.php';
require_once(ROOT_PATH . 'includes/lib_order.php');
if (file_exists($cron_lang)) {
    global $_LANG;
    include_once($cron_lang);
}
$api="a3afbfe8423c49c52fb6cbdaac09ff89";
/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;
    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');
    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'auto_order_desc';
    /* 作者 */
    $modules[$i]['author']  = 'Arliki';
    /* 网址 */
    $modules[$i]['website'] = '';
    /* 版本号 */
    $modules[$i]['version'] = '0.0.1';
    /* 配置信息 一般这一项通过serialize函数保存在cron表的中cron_config这个字段中*/
    $modules[$i]['config']  = array(
        array('name' => 'auto_order_days', 'type' => 'select', 'value' => 7)
    );
    //name：计划任务的名称，type：类型(text,textarea,select…)，value：默认值
    return;
}
$now_time=gmtime();
$days = !empty($cron['auto_order_days']) ? $cron['auto_order_days'] : 7;
$hold_time=($days+2)*24*3600;
$select_sql = 'SELECT order_id,pay_id,shipping_status,invoice_no,sp_api_name,shipping_time FROM '.$ecs->table('order_info').' order_info INNER JOIN '.$ecs->table('shipping').' shipping ON order_info.shipping_id=shipping.shipping_id where shipping_status=1 and pay_status=2 ';
$order_val=$db->getAll($select_sql);
if (empty($order_val)) {
    return false;
}
foreach ($order_val as $key => $value) {
    if ($now_time-$value["shipping_time"]>=$hold_time) {
        $order_id = $value['order_id'];
        $invoice_no = $value['invoice_no'];
        $pay_id = $value['pay_id'];
        preg_match('/([a-zA-Z0-9]+)/', $invoice_no, $match1);
        $invoice_no = $match1[1];
        $apiname = $value['sp_api_name'];
        $temp = rand(10000, 99999);
        //快递查询 3902757987412 韵达
        $url1 = "http://api.kuaidi.com/openapi.html?id=" . $api . "&com=" . $apiname . "&nu=" . $invoice_no . "&show=0&muti=1&order=desc";
        //快递100查询
        $url2 = "http://m.kuaidi100.com/query?type=" . $apiname . "&postid=" . $invoice_no . "&id=1&valicode=&temp=0.90644506498" . $temp;
        get_json_url($url1,$url2,$order_id,$pay_id,$now_time,$hold_time);
    }
}
function get_json_url($url1,$url2,$order_id,$pay_id,$now_time,$hold_time){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result1 = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result1,true);
    if ($result["success"]){
        $day_diff = $now_time - strtotime($result["data"][0]["time"]);
        if ($day_diff >= $hold_time){
            is_ok_do($order_id,$pay_id);
        }
    }else{
        $cha = curl_init();
        curl_setopt($cha, CURLOPT_URL, $url2);
        curl_setopt($cha, CURLOPT_HEADER, false);
        curl_setopt($cha, CURLOPT_RETURNTRANSFER, 1);
        $results = curl_exec($cha);
        curl_close($cha);
        $resultsa = json_decode($results,true);
        file_put_contents('mytest.txt',$order_id.$url2.$results.PHP_EOL,FILE_APPEND);
        if ($resultsa["state"] == 3) {
            $day_diff = $now_time - strtotime($results["data"][0]["time"]);
            if ($day_diff >= $hold_time) {
                is_ok_do($order_id, $pay_id);
            }
        }
    }
}
function is_ok_do($order_id,$pay_id){
    global $_LANG;
    $order = order_info($order_id);
    /* 标记订单为“收货确认”，如果是货到付款，同时修改订单为已付款 */
    $arr = array('shipping_status' => SS_RECEIVED);
    $payment = payment_info($pay_id);
    if ($payment['is_cod'])
    {
        $arr['pay_status'] = PS_PAYED;
        $order['pay_status'] = PS_PAYED;
    }
    update_order($order_id, $arr);
    received_and_pay_end($order_id);
    //自动评论
    auto_comment($order_id);
    /* 记录log */
    order_action($order['order_sn'], $order['order_status'], SS_RECEIVED, $order['pay_status'], $_LANG['action_note']);
}
function auto_comment($order_id){
    $select_sql= "select goods.goods_id,users.user_id,users.user_name,orders.order_id,users.email from ".$GLOBALS['ecs']->table('order_goods')." goods inner join ".$GLOBALS['ecs']->table('order_info')." orders ON goods.order_id=orders.order_id inner join ".$GLOBALS['ecs']->table('users')." users ON orders.user_id=users.user_id where orders.order_id = ".$order_id." ";
    $valuea=$GLOBALS['db']->getRow($select_sql);
    $goods_id=$valuea["goods_id"];
    $user_id=$valuea["user_id"];
    $user_name=$valuea["user_name"];
    $email=$valuea["email"];
    $num=rand(0,5);
    $con="select content from ".$GLOBALS['ecs']->table('auto_comment')." where comment_id = '$num'";
    $con=$GLOBALS['db']->getRow($con);
    $data["id_value"]=$goods_id;
    $data["email"]=$email;
    $data["user_name"]=$user_name;
    $data["content"]=$con["content"];
    $data["comment_rank"]=5;
    $data["add_time"]=gmtime();
    $data["ip_address"]=real_ip();
    $data["status"]=1;
    $data["user_id"]=$user_id;
    $insert_sql=$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('comment'), $data, 'INSERT');
}
?>