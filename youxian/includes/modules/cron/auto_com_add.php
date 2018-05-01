<?php
/*
* File: auto_com_add.php
* Author: Arliki
* Date: 2017-12-18 10:19
*
*/
if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
header("Content-type:text/html;charset=utf-8");
$cron_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/auto_com_add.php';
require_once(ROOT_PATH . 'includes/lib_order.php');
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
    $modules[$i]['desc']    = 'auto_com_desc';
    /* 作者 */
    $modules[$i]['author']  = 'Arliki';
    /* 网址 */
    $modules[$i]['website'] = '';
    /* 版本号 */
    $modules[$i]['version'] = '0.0.5';
    /* 配置信息 一般这一项通过serialize函数保存在cron表的中cron_config这个字段中*/
    $modules[$i]['config']  = array(
        array('name' => 'auto_com_id', 'type' => 'text', 'value' => "只需填写活动id,默认处理最后一个活动")
    );
    return;
}
$id = !empty($cron['auto_com_id']) ? $cron['auto_com_id'] : 0;
$arr=get_time(intval($id));
//获取无地址用户id
$userid=array();
$uid=$db->getAll("select user_id from ecs_order_info where add_time>'".$arr['btime']."' and add_time<'".$arr["etime"]."' and referer='自动抽奖' and country=0");
//删除空地址
$db->query("delete from ecs_user_address where country = 0");
for ($i=0;$i<count($uid);$i++){
    upd_address($uid[$i]["user_id"]);
}
function get_time($id=0){
    $db=$GLOBALS['db'];
    $all=$db->getAll("select act_id from ecs_goods_activity order by act_id desc limit 1");
    $last=$all[0]["act_id"];
    if ($id<=0 || $id >=$last){
        $id=$last;
    }
    file_put_contents('uids.json',$id."\r\n",FILE_APPEND);
    $time=$db->getAll("select start_time btime,end_time etime from ecs_goods_activity where act_id='$id'");
    $arr["btime"]=$time[0]["btime"];
    $arr["etime"]=$time[0]["etime"];
    return $arr;
}
function upd_address($userid){
    $db=$GLOBALS['db'];
    $sqladd=$db->getAll("select consignee,country,province,city,district,address,mobile from ecs_user_address where user_id='".$userid."' order by address_id desc");
    if(count($sqladd)>0){
        $sele=$sqladd[0];
        $upd="update ecs_order_info set consignee='".$sele["consignee"]."',country='".$sele["country"]."',province='".$sele["province"]."',city='".$sele["city"]."',district='".$sele["district"]."',address='".$sele["address"]."',mobile='".$sele["mobile"]."' where user_id=".$userid." and country=0";
        $db->query($upd);
        file_put_contents('upd_add.json',$upd."\r\n",FILE_APPEND);
    }
}

?>
