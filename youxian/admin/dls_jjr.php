<?php

/**
 * 代理商经纪人
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
admin_priv('dls_jjr');
$dls_id = intval($_GET['dls_id']);
if(!$dls_id){
    $referer = $_SERVER['HTTP_REFERER'];
    preg_match('/dls_id=(\d+)$/', $referer, $match);
    if($match){
        $dls_id = $match[1];
    }else{
        sys_msg('缺少代理商参数');
    }
}
$dls_info = $db->getRow("SELECT username,cxs_id FROM ".$ecs->table('dls')." WHERE dls_id=$dls_id");
if(!$dls_info){
    sys_msg('没找到代理商信息');
}

if(isCxs() && preg_match('/^(\d){5}/', $_SESSION['admin_name'])){
    //如果是承销商，判断代理商是否属于该承销商。
//    $startCode = mb_substr($_SESSION['admin_name'], 0, 2);
    //$startCode != mb_substr($dls_info['username'], 0, 2) &&
    if($dls_info['cxs_id'] != $_SESSION['admin_id']){
        sys_msg('不是您的代理商,没有查看权限');
    }
}
/*------------------------------------------------------ */
//-- 经纪人列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{

    $smarty->assign('ur_here',      '代理商['.$dls_info['username'].']经纪人列表');
    $smarty->assign('full_page',    1);
    $jjr_list = get_jjrlist($dls_id);

    $smarty->assign('jjr_list',  $jjr_list['jjr']);
    $smarty->assign('filter',       $jjr_list['filter']);
    $smarty->assign('record_count', $jjr_list['record_count']);
    $smarty->assign('page_count',   $jjr_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($dls_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('dls_jjr.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $jjr_list = get_jjrlist($dls_id);
    $smarty->assign('jjr_list',  $jjr_list['jjr']);
    $smarty->assign('filter',       $jjr_list['filter']);
    $smarty->assign('record_count', $jjr_list['record_count']);
    $smarty->assign('page_count',   $jjr_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($jjr_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('dls_jjr.htm'), '',
        array('filter' => $jjr_list['filter'], 'page_count' => $jjr_list['page_count']));
}
/**
 * 取得代理商经纪人列表
 * @param $dls_id 代理商id
 * @return  array
 */
function get_jjrlist($dls_id)
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'user_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = " WHERE 1 ";

        $where .= "AND user_rank=6 AND dls_id=$dls_id";

        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('users').$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);


        /* 查询记录 */
        $sql = "SELECT user_id,user_name,(SELECT nickname FROM ".$GLOBALS['ecs']->table('wechat_user')." WHERE ect_uid=u.user_id) AS nickname,(SELECT headimgurl FROM ".$GLOBALS['ecs']->table('wechat_user')." WHERE ect_uid=u.user_id) AS headimgurl,(SELECT SUM(goods_amount) FROM ".$GLOBALS['ecs']->table('order_info')." WHERE jjr_id=u.user_id AND dls_id=$dls_id AND pay_status=2 AND (shipping_status=1 OR shipping_status=2)) AS total_sale FROM " . $GLOBALS['ecs']->table('users') . " AS u ".$where." ORDER BY $filter[sort_by] $filter[sort_order]";

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

    return array('jjr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>