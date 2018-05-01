<?php
//游戏操作设置
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
admin_priv('yx_game_manage');
$gid = intval($_REQUEST['gid']);
if(!$gid){
    sys_msg('缺少游戏id参数', 1);
}
$game = $db->getRow("SELECT * FROM ".$ecs->table('games')." WHERE id='$gid'");
if(!$game){
    sys_msg('没找到游戏,请重新安装');
}
$smarty->assign('gid',$gid);

/*------------------------------------------------------ */
//-- 游戏日志列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $list = $db->getAll("SELECT * FROM ".$ecs->table('game_logs')." WHERE game_id='$gid'");
    $list = get_logs_list($gid);
    foreach ($list['list'] as $k=>$v){
        $v['addtime'] = local_date('Y-m-d H:i:s',$v['addtime']);
        if($v['awards_id']>0){
            $v['gottime'] = $v['gottime']>0?local_date('Y-m-d H:i:s',$v['gottime']):'<span style="color:#368636">未领取</span>';
        }else{
            $v['gottime']='<span style="color:#868686">未中奖</span>';
        }
        $list['list'][$k] = $v;
    }
//    $smarty->assign('logs_list', $list);
    $smarty->assign('ur_here', '游戏日志 -- '.$game['title']);
    $smarty->assign('action_link', array('href' => 'yxgames.php?act=list', 'text'=>'返回游戏列表'));

    $smarty->assign('logs_list',  $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    $awards = $db->getAll("SELECT id,title FROM ".$ecs->table('game_awards')." WHERE game_id=$gid");
    $smarty->assign('awards',$awards);
    assign_query_info();
    $smarty->assign('full_page',    1);
    $smarty->display('game_logs_list.htm');
}
elseif ($_REQUEST['act'] == 'query')
{
    $list = get_logs_list($gid);
    foreach ($list['list'] as $k=>$v){
        $v['addtime'] = local_date('Y-m-d H:i:s',$v['addtime']);
        if($v['awards_id']>0){
            $v['gottime'] = $v['gottime']>0?date('Y-m-d H:i:s',$v['gottime']):'未领取';
        }else{
            $v['gottime']='未中奖';
        }
        $list['list'][$k] = $v;
    }
    $smarty->assign('logs_list',  $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('game_logs_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

function get_logs_list($game_id){
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['gid'] = intval($game_id);
        $filter['username'] = empty($_REQUEST['username'])?'':addslashes(trim($_REQUEST['username']));
        $filter['award'] = empty($_REQUEST['award'])?0:intval($_REQUEST['award']);
        $filter['gotit'] = isset($_REQUEST['gotit'])?intval($_REQUEST['gotit']):-1;
        $filter['start_time'] = empty($_REQUEST['start_time'])?'':addslashes($_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time'])?'':addslashes($_REQUEST['end_time']);

        $where = ' WHERE 1 ';
        if($filter['gid']){
            $where .= " AND game_id='$game_id' ";
        }
        if($filter['username']){
            $uid = $GLOBALS['db']->getOne("SELECT user_id FROM ".$GLOBALS['ecs']->table('users')." WHERE user_name='".$filter['username']."' LIMIT 1");
            if($uid){
                $where .= " AND user_id='$uid'";
            }
        }
        if($filter['award']){
            $where .= " AND awards_id='$filter[award]'";
        }
        if($filter['gotit']!=-1){
            $where .= " AND gotit='$filter[gotit]'";
        }
        if($filter['start_time']){
            $start_time = local_strtotime($filter['start_time']);
            $where .= " AND addtime>'$start_time'";
        }
        if($filter['end_time']){
            $end_time = local_strtotime($filter['end_time']);
            $where .= " AND addtime<'$end_time'";
        }
        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('game_logs').$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('game_logs') . $where . " ORDER BY $filter[sort_by] $filter[sort_order]";
//        var_dump($sql);
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

    return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}
?>