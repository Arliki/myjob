<?php
/*
* File: auto_wxrefun.php
* Author: Arliki
* Date: 2017-12-22 10:43
*
*/
if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$cron_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/auto_wxrefun.php';
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
    $modules[$i]['desc']    = 'auto_wxrefun_desc';
    /* 作者 */
    $modules[$i]['author']  = 'Arliki';
    /* 网址 */
    $modules[$i]['website'] = '';
    /* 版本号 */
    $modules[$i]['version'] = '0.2.1';
    /* 配置信息 一般这一项通过serialize函数保存在cron表的中cron_config这个字段中*/
    $modules[$i]['config']  = array(
        array('name' => 'auto_wxrefun_rule', 'type' => 'select', 'value' => '2')
    );
    //name：计划任务的名称，type：类型(text,textarea,select…)，value：默认值
    return;
}
$now_time=gmtime();
$rules = !empty($cron['auto_wxrefun_rule']) ? $cron['auto_wxrefun_rule'] : 2;
if($rules==1) {
    $sele1 = "select user_id,price,act_id from ecs_wxrefun where status='0' and refun_time='000'";
    $sele2=$db->getAll($sele1);
    if($sele2){
        write_log('upd_user.json','',-1);
        for ($i=0;$i<count($sele2);$i++){
            refun_user($sele2[$i]["user_id"],$sele2[$i]["price"],$sele2[$i]["act_id"]);
        }
    }
}else if($rules==2){
    $cmmond="cd ../../../python && python wxpub.py";
    shell_exec($cmmond);
    //获取微信配置信息s
//    $refun="select pay_config from ecs_payment where pay_id=1";
//    $refun=$db->getAll($refun);
//    include_once(ROOT_PATH.'mobile/plugins/payment/wxrefund.php');
//    $payment=unserconfig($refun[0]['pay_config']);
//    $wxrefund=new wxrefund();
//    $sele1 = "select order_sn,price from ecs_wxrefun where status='0' and refun_time='000'";
//    $sele2=$db->getAll($sele1);
//    if($sele2){
//        for ($i=0;$i<count($sele2);$i++){
//            $user["order_sn"]=$sele2[$i]['order_sn'];
//            $user["super_price"]=$sele2[$i]['price']*100;
//            $resu=$wxrefund->refund($user,$payment);    //直接退款到账户微信余额
//            if($resu["return_code"]=="SUCCESS") {
//                $db->query("update ecs_wxrefun set status='12',refun_time='" . gmtime() . "' where order_sn='" . $sele2[$i]["order_sn"] . "' and status='0'");
//            }
//        }
//    }
}else if($rules==3){
    $sele1 = "select order_sn,price,act_id from ecs_wxrefun where status='0' and refun_time='000'";
    $sele2=$db->getAll($sele1);
    $downname=ROOT_PATH."data/w_logs/super_".$sele2[0]['act_id'].".txt";
    if($sele2){
        for ($i=0;$i<count($sele2);$i++){
            file_put_contents($downname, $sele2[$i]['order_sn'] . "," . ($sele2[$i]['price']) . ",未中奖退款,\r\n", FILE_APPEND);
            $db->query("update ecs_wxrefun set status='13',refun_time='".gmtime()."' where order_sn='".$sele2[$i]["order_sn"]."' and status='0'");
        }
        refun_down($downname);
    }
}
function refun_user($userid,$price,$act_id){
    $change_time=gmtime();
    $acc_log="insert into ecs_account_log (user_id, user_money, change_time, change_desc, change_type) VALUES ('$userid','$price','$change_time','第".$act_id."号预购活动未中奖返还','99')";
    $GLOBALS['db']->query($acc_log);
    $GLOBALS['db']->query("update ecs_wxrefun set status='11',refun_time='".gmtime()."' where user_id='$userid' and act_id='$act_id' and status='0'");
    //修改账户余额
    $upd="update ecs_users set user_money = user_money + ('$price') WHERE user_id = '$userid' LIMIT 1";
    $GLOBALS['db']->query($upd);
    write_log('upd_user.json',$userid."账户:".$acc_log,0);
}
function refun_down($names){
    $filename = $names; //文件路径
    $mime = 'application/force-download';
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private',false);
    header('Content-Type: '.$mime);
    header('Content-Disposition: attachment; filename="'.basename($filename).'"');
    header('Content-Transfer-Encoding: binary');
    header('Connection: close');
    readfile($filename);
    die;
}
function unserconfig($cfg)
{
    if (is_string($cfg) && ($arr = unserialize($cfg)) !== false)
    {
        $config = array();
        foreach ($arr AS $key => $val)
        {
            $config[$val['name']] = $val['value'];
        }
        return $config;
    }
    else
    {
        return false;
    }
}
?>