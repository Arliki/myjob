<?php

/**
 * 订单支付记录列表
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
//include_once(ROOT_PATH . 'includes/cls_image.php');
//$image = new cls_image($_CFG['bgcolor']);
admin_priv('order_pay_log');
/*------------------------------------------------------ */
//-- 订单支付记录列表
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list')
{

    $smarty->assign('ur_here',      '订单支付记录列表');
//    $smarty->assign('action_link',  array('text' => '添加优惠券', 'href' => 'coupon.php?act=add'));
    $smarty->assign('full_page',    1);

    $pay_log_list = get_pay_log_list();

    foreach($pay_log_list['pay_list'] as $k=>$v){
       $v['is_paid'] = $v['is_paid']==1?'已支付':'未支付';
       $v['order_amount'] = price_format($v['order_amount']);
       $pay_log_list['pay_list'][$k] = $v;
    }

    $smarty->assign('pay_list',     $pay_log_list['pay_list']);
    $smarty->assign('filter',       $pay_log_list['filter']);
    $smarty->assign('record_count', $pay_log_list['record_count']);
    $smarty->assign('page_count',   $pay_log_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($pay_log_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('pay_log_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $pay_log_list = get_pay_log_list();
    foreach($pay_log_list['pay_list'] as $k=>$v){
        $v['is_paid'] = $v['is_paid']==1?'已支付':'未支付';
        $v['order_amount'] = price_format($v['order_amount']);
        $pay_log_list['pay_list'][$k] = $v;
    }
    $smarty->assign('pay_list',     $pay_log_list['pay_list']);
    $smarty->assign('filter',       $pay_log_list['filter']);
    $smarty->assign('record_count', $pay_log_list['record_count']);
    $smarty->assign('page_count',   $pay_log_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($pay_log_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('pay_log_list.htm'), '',
        array('filter' => $pay_log_list['filter'], 'page_count' => $pay_log_list['page_count']));
    exit();
}
/**
 * 取得订单支付列表
 * @return  array
 */
function get_pay_log_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'log_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $where = " WHERE 1 ";
        if ($filter['keywords'])
        {
            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
        }
        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(pl.log_id) FROM " . $GLOBALS['ecs']->table('pay_log')." AS pl LEFT JOIN ".$GLOBALS['ecs']->table('order_info')." AS o ON pl.order_id=o.order_id ".$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        $where .= " AND pl.order_amount>0 ";
        /* 查询记录 */
        $sql = "SELECT pl.*,o.order_sn FROM " . $GLOBALS['ecs']->table('pay_log') . " AS pl LEFT JOIN ".$GLOBALS['ecs']->table('order_info')." AS o ON pl.order_id=o.order_id ".$where." ORDER BY $filter[sort_by] $filter[sort_order]";
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $arr[] = $rows;
    }

    return array('pay_list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>