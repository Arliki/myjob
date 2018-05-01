<?php
/**
*/
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
//-- 代付记录
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list' || $_REQUEST['act'] == 'query')
{
    /* 权限判断 */
    admin_priv('sxy_out_account');
    $smarty->assign('ur_here',       '代付记录');

    $list = out_account_list();
    foreach ($list['list'] as $k=>$v){
        $v['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
        $v['status'] = $v['status']==0?'已处理':'待处理';
        $list['list'][$k] = $v;
    }
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    if(isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1){
        $sort_flag  = sort_flag($list['filter']);
        $smarty->assign($sort_flag['tag'], $sort_flag['img']);
        make_json_result($smarty->fetch('sxy_out_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));exit();
    }
    $smarty->assign('full_page',1);

    assign_query_info();
    $smarty->display('sxy_out_list.htm');
}

if ($_REQUEST['act'] == 'status_check')
{
    admin_priv('sxy_out_account');
    if(preg_match('/^[1|U|C]\d{10}$/', $_REQUEST['tid'])){
        $tid = $_REQUEST['tid'];
    }else{
        $tid = intval($_REQUEST['tid']);
        $tid = str_pad($tid, 10,'0', STR_PAD_LEFT);
    }
    require_once ROOT_PATH . 'includes/sxy.php';
    $sxy = new sxy();
    $status = $sxy->daifu_check($tid);
    $smarty->assign('ur_here',       '代付状态查询');
    $smarty->assign('action_link', array('href'=>'sxy_manage_df.php?act=list', 'text' => '返回列表'));
    $status['message']['status_info'] = $sxy::$errCode['daifu_check'][$status['message']['status']];
    $smarty->assign('status', $status['message']);
    $smarty->display('sxy_daifu_check.htm');
}

/**
 * @access  public
 * @param
 * @return void
 */
function out_account_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤列表 */
//        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'addtime' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : local_strtotime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (local_strtotime($_REQUEST['end_date']) + 86400);


        $where = " WHERE 1 ";

        /*　时间过滤　*/
        if (!empty($filter['start_date']) && !empty($filter['end_date']))
        {
            $where .= "AND addtime >= " . $filter['start_date']. " AND addtime < '" . $filter['end_date'] . "'";
        }

        $sql = "SELECT COUNT('id') FROM " .$GLOBALS['ecs']->table('sxy_daifu').
            $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询数据 */
        $sql  = 'SELECT * FROM ' .
            $GLOBALS['ecs']->table('sxy_daifu').
            $where . " ORDER by " . $filter['sort_by'] . " " .$filter['sort_order']. " LIMIT ".$filter['start'].", ".$filter['page_size'];
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