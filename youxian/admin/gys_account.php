<?php

/**
 * ECSHOP 会员帐目管理(包括预付款，余额)
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user_account.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'in_list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 入账明细
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'in_list' || $_REQUEST['act'] == 'query')
{
    /* 权限判断 */
    admin_priv('gys_in_account');

    //是否显示 手动调节供应商账户 按钮
    if(admin_priv('fix_gys_account','',false)){
        $smarty->assign('can_fix_gys_account', true);
    }

    $smarty->assign('ur_here',       '供应商入账明细');

    $list = in_account_list();
    if($list === false){
        sys_msg('供应商账号绑定有误');
    }
    foreach ($list['list'] as $k=>$v){
        $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        $v['amount'] = '￥'.$v['amount'].'元';
//        $v['process_type'] = $v['process_type'] == 1 ?'尾款'.(100-$v['agency_first_percent']).'%':'首期款'.$v['agency_first_percent'].'%';
//        $v['process_type'] = $v['process_type'] == 1 ?'尾款':'首期款';
        $v['is_paid'] = $v['is_paid'] == 1 ? '成功':'失败';
        $list['list'][$k] = $v;
    }

    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    if(isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1){
        $sort_flag  = sort_flag($list['filter']);
        $smarty->assign($sort_flag['tag'], $sort_flag['img']);
        make_json_result($smarty->fetch('gys_in_account_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));exit();
    }
    $smarty->assign('full_page',1);

    $where = " WHERE add_time> ".strtotime(date('Y-m-d',time()));
    if(isGys()){
        $agency = $GLOBALS['db']->getRow("SELECT gys_account,agency_id FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE user_id='$_SESSION[admin_id]'");
        $where .= " AND gys_id='$agency[agency_id]'";
        $smarty->assign('gys_account', price_format($agency['gys_account'], false));
        $smarty->assign('text_gys_account', '账户余额');
        $smarty->assign('text_today_in_money', '今日入账');
    }
    if(check_authz('gys_in_account')){
        $gys_account = $GLOBALS['db']->getOne("SELECT SUM(gys_account) FROM ".$GLOBALS['ecs']->table('admin_user'));
        $smarty->assign('gys_account', price_format($gys_account, false));
        $smarty->assign('text_gys_account', '供应商账户总余额');
        $smarty->assign('text_today_in_money', '供应商今日总入账');
    }
    $sql = "SELECT SUM(amount) FROM ".$GLOBALS['ecs']->table('gys_earn_log').$where;
    $todayInMoney = $GLOBALS['db']->getOne($sql);
    $smarty->assign('today_in_money', price_format($todayInMoney, false));
    assign_query_info();
    $smarty->display('gys_in_account_list.htm');
}
elseif ($_REQUEST['act'] == 'add'){
    admin_priv('fix_gys_account');

    $smarty->assign('ur_here',       '手动调节供应商账户');
    $smarty->assign('action_link', array('href' => 'gys_account.php?act=in_list', 'text' => '返回列表'));

    $sql = "SELECT agency_id, agency_name FROM " . $ecs->table('agency');
    $smarty->assign('agency_list', $db->getAll($sql));

    $smarty->display('fix_gys_account.htm');

}
elseif ($_REQUEST['act'] == 'action'){
    admin_priv('fix_gys_account');

    $gys_id = intval($_POST['gys_id']);
    if(!$gys_id){
        sys_msg('请选择供应商');
    }
    $gys_exist = $db->getOne("SELECT COUNT(*) FROM ".$ecs->table('agency')." WHERE agency_id='".$gys_id."'");
    if($gys_exist==0){
        sys_msg('供应商不存在');
    }
    if(!in_array($_POST['opt'],array(1,-1))){
        sys_msg('参数错误[增加/减少]');
    }
    if(!is_numeric($_POST['amount'])){
        sys_msg('金额有误,必须为数字');
    }
    $amount = round($_POST['opt']*$_POST['amount'],2);
    if(!$_POST['admin_note']){
        sys_msg('请填写备注信息');
    }
    $_POST['admin_note'] = '[管理员调节账户]'.$_POST['admin_note'];
    $remark = stripslashes(trim($_POST['admin_note']));
    $in_out = $_POST['opt']==1?0:1;
    $data = array(
        'gys_id' => $gys_id,
        'order_id' => '0',
        'amount' => $amount,
        'add_time' => time(),
        'process_type' => $remark,
        'is_paid' => 1,
        'in_out' => $in_out,
        'f_step'=>2
    );

    $GLOBALS['db']->query('SET AUTOCOMMIT=0');
    $gys = $GLOBALS['db']->getRow("SELECT user_id,gys_account FROM " . $GLOBALS['ecs']->table('admin_user') . " WHERE agency_id='".$gys_id."'");

    $gys_account = $gys['gys_account'] + $amount;
    $gys_adminid = $gys['user_id'];

    $insert_id = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('gys_earn_log'), $data, 'INSERT');

    $update_account = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('admin_user'), array("gys_account" => $gys_account), 'UPDATE', "user_id='$gys_adminid'");

    $links[] = array('text' => '返回列表', 'href' => 'gys_account.php?act=in_list');
    if ($insert_id && $update_account) {
        $GLOBALS['db']->query('COMMIT');

        sys_msg('操作成功',0,$links);
    } else {
        $GLOBALS['db']->query('ROLLBACK');
        sys_msg('操作失败',1,$links);
    }
    $GLOBALS['db']->query("SET AUTOCOMMIT=1");
}
/**
 *
 *
 * @access  public
 * @param
 *
 * @return void
 */
function in_account_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤列表 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : local_strtotime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (local_strtotime($_REQUEST['end_date']) + 86400);

        if(isGys()){
            $sql_gys = "SELECT ag.agency_id FROM ".$GLOBALS['ecs']->table('admin_user')." AS ad LEFT JOIN ".
                $GLOBALS['ecs']->table('agency')." AS ag ON ad.agency_id=ag.agency_id ".
                "WHERE ad.user_id='".$_SESSION['admin_id']."'";
            $gys_id = $GLOBALS['db']->getOne($sql_gys);
            if(!$gys_id){
                return false;
            }
            $filter['gys_id'] = $gys_id;
        }

        $where = " WHERE 1 ";
        if ($filter['keywords'])
        {
            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
        }
        /*　时间过滤　*/
        if (!empty($filter['start_date']) && !empty($filter['end_date']))
        {
            $where .= "AND l.add_time >= " . $filter['start_date']. " AND l.add_time < '" . $filter['end_date'] . "'";
        }

        if(!empty($filter['gys_id'])){
            $where .= " AND a.agency_id=".$filter['gys_id'];
        }

        $sql = "SELECT COUNT('l.id') FROM " .$GLOBALS['ecs']->table('gys_earn_log'). " AS l LEFT JOIN ".
            $GLOBALS['ecs']->table('order_info')." AS o ON l.order_id=o.order_id LEFT JOIN ".
            $GLOBALS['ecs']->table('agency')." AS a ON l.gys_id=a.agency_id ".
            $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询数据 */
        $sql  = 'SELECT l.*,o.order_sn,a.agency_name,a.agency_first_percent FROM ' .
            $GLOBALS['ecs']->table('gys_earn_log')." AS l LEFT JOIN ".
            $GLOBALS['ecs']->table('order_info')." AS o ON l.order_id=o.order_id LEFT JOIN ".
            $GLOBALS['ecs']->table('agency')." AS a ON l.gys_id=a.agency_id ".
            $where . " ORDER by " . $filter['sort_by'] . " " .$filter['sort_order']. " LIMIT ".$filter['start'].", ".$filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $list = $GLOBALS['db']->getAll($sql);

    $arr = array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>