<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 入账明细
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list' || $_REQUEST['act'] == 'query')
{
    /* 权限判断 */
    admin_priv('cxs_earn_list');

    //是否显示 手动调节承销商账户 按钮
    if(admin_priv('fix_cxs_account','',false)){
        $smarty->assign('can_fix_cxs_account', true);
    }

    $smarty->assign('ur_here',       '承销商收入明细');

    $list = in_account_list();

    foreach ($list['list'] as $k=>$v){
        $v['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
        $v['amount'] = '￥'.$v['amount'].'元';
        $list['list'][$k] = $v;
    }

    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    if(isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1){
        $sort_flag  = sort_flag($list['filter']);
        $smarty->assign($sort_flag['tag'], $sort_flag['img']);
        make_json_result($smarty->fetch('cxs_earn.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));exit();
    }
    $smarty->assign('full_page',1);
    assign_query_info();
    $smarty->display('cxs_earn.htm');
}
elseif ($_REQUEST['act'] == 'add'){
    admin_priv('fix_cxs_account');

//    $smarty->assign('ur_here',       '手动调节供应商账户');
//    $smarty->assign('action_link', array('href' => 'gys_account.php?act=in_list', 'text' => '返回列表'));
//
//    $sql = "SELECT agency_id, agency_name FROM " . $ecs->table('agency');
//    $smarty->assign('agency_list', $db->getAll($sql));
//
//    $smarty->display('fix_gys_account.htm');

}
elseif ($_REQUEST['act'] == 'action'){
    admin_priv('fix_cxs_account');

/*    $gys_id = intval($_POST['gys_id']);
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
    $GLOBALS['db']->query("SET AUTOCOMMIT=1");*/
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
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : local_strtotime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (local_strtotime($_REQUEST['end_date']) + 86400);

        $where = " WHERE 1 ";
//        if ($filter['keywords'])
//        {
//            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
//        }
//        /*　时间过滤　*/
//        if (!empty($filter['start_date']) && !empty($filter['end_date']))
//        {
//            $where .= "AND l.addtime >= " . $filter['start_date']. " AND l.addtime < '" . $filter['end_date'] . "'";
//        }


        $sql = "SELECT COUNT('ce.id') FROM " .$GLOBALS['ecs']->table('cxs_earn'). " AS ce ".
            $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询数据 */
        $sql  = "SELECT ce.*,d.username FROM " .$GLOBALS['ecs']->table('cxs_earn'). " AS ce LEFT JOIN ".
            $GLOBALS['ecs']->table('dls')." AS d ON ce.dls_id=d.dls_id ".
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