<?php

/**
 * ECSHOP 程序说明
 * ===========================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ==========================================================
 * $Author: liubo $
 * $Id: affiliate.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
admin_priv('dls_ratio');
$config = get_affiliate();

/*------------------------------------------------------ */
//-- 分成管理页
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }

    $smarty->assign('ur_here', '代理商分成设置');
    $smarty->assign('config', $config);
    $smarty->display('dls_ratio.htm');
}
elseif ($_REQUEST['act'] == 'query')
{
    $smarty->assign('ur_here', '分成设置');
    $smarty->assign('config', $config);
    make_json_result($smarty->fetch('dls_ratio.htm'), '', null);
}
/*------------------------------------------------------ */
//-- 增加下线分配方案
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    if (count($config) < 4)
    {
        //下线不能超过5层
        $_POST['step_money'] = (float)$_POST['step_money'];
        $_POST['step_ratio'] = (int)$_POST['step_ratio'];
        $items = array('step_money'=>$_POST['step_money'],'step_ratio'=>$_POST['step_ratio']);
        $config[] = $items;
//        $links[] = array('text' => '分成设置', 'href' => 'dls_ratio.php?act=list');
        put_affiliate($config);
    }
    else
    {
        make_json_error('最多不超过4层');
    }
//    make_json_result('已添加');
    ecs_header("Location: dls_ratio.php?act=query\n");
    exit;
}
/*------------------------------------------------------ */
//-- Ajax修改设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_money')
{
    /* 取得参数 */
    $key = trim($_POST['id']);
    $val = trim($_POST['val']);
    if($config[$key]){
        $maxmoney = $config[$key]['step_money'];
        $val > $maxmoney && $val = $maxmoney;
    }
    $config[$key-1]['step_money'] = $val;
    put_affiliate($config);
    make_json_result(stripcslashes($val));
}
/*------------------------------------------------------ */
//-- Ajax修改设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_ratio')
{
    $key = trim($_POST['id']);
    $val = (float)trim($_POST['val']);
    $maxratio = 100;
    if($config[$key]){
        $maxratio = $config[$key]['step_ratio'];
    }
    $val > $maxratio && $val = $maxratio;
    $config[$key-1]['step_ratio'] = $val;

    put_affiliate($config);
    make_json_result(stripcslashes($val));
}
/*------------------------------------------------------ */
//-- 删除下线分成
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'del')
{
    sys_msg('暂时不允许删除');
    $key = trim($_GET['id']) - 1;
    unset($config[$key]);
    $temp = array();
    foreach ($config as $key => $val)
    {
        $temp[] = $val;
    }
    $config = $temp;

    put_affiliate($config);
    ecs_header("Location: dls_ratio.php?act=list\n");
    exit;
}

function get_affiliate()
{
//    clear_all_files();
    $config = unserialize($GLOBALS['_CFG']['dls_ratio']);
    empty($config) && $config = array();
    return $config;
}

function put_affiliate($config)
{
    $temp = serialize($config);
    $sql = "UPDATE " . $GLOBALS['ecs']->table('shop_config') .
        "SET  value = '$temp'" .
        "WHERE code = 'dls_ratio'";
    $GLOBALS['db']->query($sql);
    clear_all_files();
}
?>