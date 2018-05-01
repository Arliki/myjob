<?php

/**
 * 转发集赞送衣服
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
admin_priv('activity_clothes');
/*------------------------------------------------------ */
//-- 记录列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('ur_here',      '集赞转发送衣服');
    $smarty->assign('full_page',    1);

    $user_list = get_list();

    foreach($user_list['user'] as $k=>$v){
        $v['addtime'] = date('Y/m/d', $v['addtime']);
        $v['pic1'] = $v['pic1']?'../mobile/'.$v['pic1']:'';
        $v['pic2'] = $v['pic2']?'../mobile/'.$v['pic2']:'';
        $user_list['user'][$k] = $v;
    }
    $smarty->assign('user_list',  $user_list['user']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($coupon_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('activity_clothes.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
        $user_list = get_list();
        foreach($user_list['user'] as $k=>$v){
            $v['addtime'] = date('Y/m/d', $v['addtime']);
            $v['pic1'] = $v['pic1']?'../mobile/'.$v['pic1']:'';
            $v['pic2'] = $v['pic2']?'../mobile/'.$v['pic2']:'';
            $user_list['user'][$k] = $v;
        }
        $smarty->assign('user_list',  $user_list['user']);
        $smarty->assign('filter',       $user_list['filter']);
        $smarty->assign('record_count', $user_list['record_count']);
        $smarty->assign('page_count',   $user_list['page_count']);

        /* 排序标记 */
        $sort_flag  = sort_flag($user_list['filter']);
        $smarty->assign($sort_flag['tag'], $sort_flag['img']);

        make_json_result($smarty->fetch('activity_clothes.htm'), '',
            array('filter' => $user_list['filter'], 'page_count' => $user_list['page_count']));
}
elseif ($_REQUEST['act'] == 'export_users'){
    include_once('includes/cls_phpzip.php');
    $zip = new PHPZip;
    $res = get_list();
    $res = $res['user'];
    /* csv文件数组 */
    $users = array();
    $users['name'] = '""';
    $users['phone'] = '""';
    $users['addr'] = '""';
    $users['addtime'] = '""';
    $theader = array('姓名','手机','地址','时间');
    $content = '"' . implode('","', $theader) . "\"\n";
    foreach ($res as $v){
        $users['name'] = $v['name'];
        $users['phone'] = $v['phone'];
        $users['addr'] = $v['addr'];
        $users['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
        $content .= implode(",", $users) . "\n";
    }
    $charset = 'gb2312';
    $export_file_name = date('Ymd',time()).'转发集赞活动报名列表';
    $zip->add_file(ecs_iconv(EC_CHARSET, $charset, $content), ecs_iconv(EC_CHARSET, $charset, $export_file_name).'.csv');

    header("Content-Disposition: attachment; filename=".ecs_iconv(EC_CHARSET, $charset, $export_file_name).".zip");
    header("Content-Type: application/unknown;charset=gb2312");
    die($zip->file());
}


/**
 * 取得记录列表
 * @return  array
 */
function get_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('activity_clothes');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('activity_clothes') . " ORDER BY $filter[sort_by] $filter[sort_order]";

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

    return array('user' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>