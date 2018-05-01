<?php
/**
 * 充值卡使用记录
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
/*------------------------------------------------------ */
//-- 充值卡列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list' || $_REQUEST['act'] == 'query')
{
    admin_priv('recharge_card_log');

    $smarty->assign('ur_here',      '充值卡使用记录');
    $smarty->assign('action_link',  array('text' => '返回充值卡列表', 'href' => 'recharge_card.php?act=list'));
    $smarty->assign('full_page',    1);

    $logs = get_card_use_list();

    foreach($logs['log'] as $k=>$v){
        $v['usetime'] = date('Y-m-d H:i:s', $v['usetime']);

        $logs['log'][$k] = $v;
    }
    $smarty->assign('logs',  $logs['log']);
    $smarty->assign('filter',       $logs['filter']);
    $smarty->assign('record_count', $logs['record_count']);
    $smarty->assign('page_count',   $logs['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($logs['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    if($_REQUEST['is_ajax']){
        $smarty->assign('full_page', 0);
        make_json_result($smarty->fetch('recharge_card_log.htm'), '',
            array('filter' => $logs['filter'], 'page_count' => $logs['page_count']));
    }
    assign_query_info();
    $smarty->display('recharge_card_log.htm');
}

/**
 * 取得优惠券列表
 * @return  array
 */
function get_card_use_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'cl.id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['card_id'] = isset($_REQUEST['card_id']) ? trim($_REQUEST['card_id']) : '';
        $filter['card_num'] = isset($_REQUEST['card_num']) ? trim($_REQUEST['card_num']) : '';
        $where = ' WHERE 1';
        if($filter['card_id']){
            $where .= " AND cl.card_id =".$filter['card_id'];
        }
        if($filter['card_num']){
            $where .= " AND c.card_num LIKE '%".$filter['card_num']."%'";
        }
        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(cl.id) FROM " . $GLOBALS['ecs']->table('recharge_card_log')."AS cl LEFT JOIN ".$GLOBALS['ecs']->table('recharge_card')." AS c ON cl.card_id=c.id". $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT cl.*,c.card_num,u.user_name FROM " . $GLOBALS['ecs']->table('recharge_card_log')." AS cl LEFT JOIN ".$GLOBALS['ecs']->table('recharge_card')." AS c ON cl.card_id=c.id LEFT JOIN ".$GLOBALS['ecs']->table('users')." AS u ON cl.user_id=u.user_id " .$where. " ORDER BY $filter[sort_by] $filter[sort_order]";
//var_dump($sql);die;
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

    return array('log' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}
?>