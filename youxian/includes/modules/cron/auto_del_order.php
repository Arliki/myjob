<?php
if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$cron_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/auto_del_order.php';
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
    $modules[$i]['desc']    = 'auto_del_desc';
    /* 作者 */
    $modules[$i]['author']  = 'Arliki';
    /* 网址 */
    $modules[$i]['website'] = '';
    /* 版本号 */
    $modules[$i]['version'] = '0.0.1';
    /* 配置信息 一般这一项通过serialize函数保存在cron表的中cron_config这个字段中*/
    $modules[$i]['config']  = array(
        array('name' => 'auto_del_days', 'type' => 'text', 'value' => "auto_del_days_range")
    );
    //name：计划任务的名称，type：类型(text,textarea,select…)，value：默认值
    return;
}
$now_time=gmtime();
$days = !empty($cron['auto_del_days']) ? $cron['auto_del_days'] : 24;
$ecs=$GLOBALS['ecs'];
$order_sql= 'SELECT add_time,order_id,order_sn,pay_status FROM '.$ecs->table('order_info').' where pay_status=0 and shipping_status=0 and order_status != 4';
$select_sql = 'SELECT add_time,order_id,order_sn,order_status FROM '.$ecs->table('order_info').' where order_status=0 or order_status=2 or order_status=3 and order_status != 4';
$pay_val=$db->getAll($order_sql);
$order_val=$db->getAll($select_sql);
if (!empty($order_val)) {
    foreach ($order_val as $key => $value) {
        if ($now_time-$value['add_time']>=$days*3*3600) {
            $sql = "DELETE from " . $ecs->table('order_info') . " WHERE order_id=".$value['order_id'];
            $db->query($sql);
            admin_log($_LANG['auto_note'].$value['order_sn'],'drop','order');
        }
    }
}
if (!empty($pay_val)){
    foreach ($pay_val as $key => $value) {
        if ($now_time-$value['add_time']>=$days*3*3600) {
            $order_status=$value['order_status'];
            $update = "UPDATE ".$ecs->table('order_info')." SET order_status = 2 WHERE order_id =".$value['order_id'];
            $db->query($update);
            order_action($value['order_sn'],$order_status, SS_UNSHIPPED ,0,$_LANG['action_note']);
        }
    }
}

?>