<?php

/**
 * 优惠券
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);

/*------------------------------------------------------ */
//-- 优惠券列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('coupon_manage');
    $smarty->assign('ur_here',      '优惠券列表');
    $smarty->assign('action_link',  array('text' => '添加优惠券', 'href' => 'coupon.php?act=add'));
    $smarty->assign('full_page',    1);

    $coupon_list = get_couponlist();

    foreach($coupon_list['coupon'] as $k=>$v){
        $v['send_start_date'] = date('Y/m/d', $v['send_start_date']);
        $v['send_end_date'] = date('Y/m/d', $v['send_end_date']);
        $v['use_start_date'] = date('Y/m/d', $v['use_start_date']);
        $v['use_end_date'] = date('Y/m/d', $v['use_end_date']);
        if($v['coupon_num']==0){
            $v['coupon_num'] = '不限量';
        }
        $coupon_list['coupon'][$k] = $v;
    }

    $smarty->assign('coupon_list',  $coupon_list['coupon']);
    $smarty->assign('filter',       $coupon_list['filter']);
    $smarty->assign('record_count', $coupon_list['record_count']);
    $smarty->assign('page_count',   $coupon_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($coupon_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('coupon_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $coupon_id = intval($_REQUEST['coupon_id']);
    if(!$coupon_id){
        $coupon_list = get_couponlist();
        $smarty->assign('coupon_list',  $coupon_list['coupon']);
        $smarty->assign('filter',       $coupon_list['filter']);
        $smarty->assign('record_count', $coupon_list['record_count']);
        $smarty->assign('page_count',   $coupon_list['page_count']);

        /* 排序标记 */
        $sort_flag  = sort_flag($coupon_list['filter']);
        $smarty->assign($sort_flag['tag'], $sort_flag['img']);

        make_json_result($smarty->fetch('coupon_list.htm'), '',
            array('filter' => $coupon_list['filter'], 'page_count' => $coupon_list['page_count']));
    }else{
        $coupon_list = get_coupon_user_list($coupon_id);
        foreach($coupon_list['coupon'] as $k=>$v){
            $v['get_time'] = date('Y/m/d', $v['get_time']);
            if($v['use_time'] > 0){
                $v['use_time'] = date('Y/m/d', $v['use_time']);
            }else{
                $v['use_time'] = '未使用';
            }

            $coupon_list['coupon'][$k] = $v;
        }
        $smarty->assign('coupon_list',  $coupon_list['coupon']);
        $smarty->assign('filter',       $coupon_list['filter']);
        $smarty->assign('record_count', $coupon_list['record_count']);
        $smarty->assign('page_count',   $coupon_list['page_count']);

        /* 排序标记 */
        $sort_flag  = sort_flag($coupon_list['filter']);
        $smarty->assign($sort_flag['tag'], $sort_flag['img']);

        make_json_result($smarty->fetch('coupon_user_list.htm'), '',
            array('filter' => $coupon_list['filter'], 'page_count' => $coupon_list['page_count']));
    }

}

/*------------------------------------------------------ */
//-- 添加、编辑优惠券
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
{
    /* 检查权限 */
    admin_priv('coupon_manage');

    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'add';
    $smarty->assign('form_action', $is_add ? 'insert' : 'update');

    /* 初始化、取得办事处信息 */
    if ($is_add)
    {
        $coupon = array(

        );
    }
    else
    {
        if (empty($_GET['coupon_id']))
        {
            sys_msg('invalid param');
        }

        $id = $_GET['coupon_id'];
        $sql = "SELECT * FROM " . $ecs->table('coupon') . " WHERE id = '$id'";
        $coupon = $db->getRow($sql);
        if (empty($coupon))
        {
            sys_msg('优惠券不存在');
        }else{
            $coupon['send_start_date'] = date('Y-m-d', $coupon['send_start_date']);
            $coupon['send_end_date'] = date('Y-m-d', $coupon['send_end_date']);
            $coupon['use_start_date'] = date('Y-m-d', $coupon['use_start_date']);
            $coupon['use_end_date'] = date('Y-m-d', $coupon['use_end_date']);
        }
    }
    $smarty->assign('coupon',$coupon);
    /* 显示模板 */
    if ($is_add)
    {
        $smarty->assign('ur_here', '添加优惠券');
    }
    else
    {
        $smarty->assign('ur_here', '编辑优惠券');
    }
    $href = 'coupon.php?act=list';

    $smarty->assign('action_link', array('href' => $href, 'text' => '返回列表'));
    assign_query_info();
    $smarty->display('coupon_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑代理商
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    /* 检查权限 */
    admin_priv('coupon_manage');

    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'insert';

    if(!isset($_POST['coupon_title']) || trim($_POST['coupon_title'])==''){
        sys_msg('请填写优惠券名称');
    }
    if(!is_numeric($_POST['coupon_money']) || $_POST['coupon_money']=='0'){
        sys_msg('请填写合法的优惠券面值,必须为大于0的数字');
    }
    if(!is_numeric($_POST['min_money'])){
        sys_msg('请填写合法的最小订单金额,必须为数字');
    }
    if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['send_start_date']) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['send_end_date']) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['use_start_date']) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['use_end_date'])
    ){
        sys_msg('发放日期或使用日期格式不正确');
    }
    if(!is_numeric($_POST['coupon_num'])){
        sys_msg('请填写合法的优惠券发放量,必须为数字');
    }

    $coupon_img = $_POST['coupon_img_url'];
    if ((isset($_FILES['coupon_img']['error']) && $_FILES['coupon_img']['error'] == 0) || (!isset($_FILES['coupon_img']['error']) && isset($_FILES['coupon_img']['tmp_name'] ) && $_FILES['coupon_img']['tmp_name'] != 'none'))
    {
        $coupon_img = $image->upload_image($_FILES['coupon_img'], 'afficheimg');
    }

    /* 提交值 */
    $coupon = array(
        'id'     => intval($_POST['coupon_id']),
        'coupon_title'   => sub_str(trim($_POST['coupon_title']), 255, false),
        'coupon_money'   => $_POST['coupon_money'],
        'min_money' => $_POST['min_money'],
        'send_start_date' => strtotime($_POST['send_start_date']),
        'send_end_date' => strtotime($_POST['send_end_date'].' 23:59:59'),
        'use_start_date'=> strtotime($_POST['use_start_date']),
        'use_end_date'=> strtotime($_POST['use_end_date'].' 23:59:59'),
        'coupon_num'=> intval($_POST['coupon_num']),
        'coupon_img'=>$coupon_img
    );
    /* 保存优惠券信息 */
    if ($is_add)
    {
        $db->autoExecute($ecs->table('coupon'), $coupon, 'INSERT');
        $coupon['id'] = $db->insert_id();
    }
    else
    {
        $coupon_id = intval($_POST['coupon_id']);
//        $phoneExist = $db->getCol("SELECT dls_id FROM ".$ecs->table('dls')." WHERE dls_id!=".$dls_id." AND phone='".$dls['phone']."'");
        $db->autoExecute($ecs->table('coupon'), $coupon, 'UPDATE', "id = '$coupon_id'");
    }

    /* 记日志 */
    if ($is_add)
    {
        admin_log($coupon['coupon_title'], 'add', '优惠券');
    }
    else
    {
        admin_log($coupon['coupon_title'], 'edit', '优惠券');
    }

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    if ($is_add)
    {
        $links = array(
            array('href' => 'coupon.php?act=list', 'text' => '优惠券列表')
        );
    }
    else
    {
        $links = array(
            array('href' => 'coupon.php?act=list', 'text' => '优惠券列表')
        );
    }
    sys_msg('操作成功', 0, $links);
}
elseif ($_REQUEST['act'] == 'remove'){
    $id = intval($_REQUEST['coupon_id']);
    if(!$id){
        sys_msg('缺少参数');
    }
    $record_num = $db->getOne("SELECT COUNT(id) FROM ".$ecs->table('user_coupon')." WHERE coupon_id='$id'");
    if($record_num>0){
        sys_msg('该优惠券有用户领取记录,不允许删除');
    }
    if($db->query("DELETE FROM ".$ecs->table('coupon')." WHERE id='$id'")){
        sys_msg('操作成功');
    }else{
        sys_msg('操作失败');
    }
}
elseif ($_REQUEST['act'] == 'user_list'){
    $coupon_id = intval($_REQUEST['coupon_id']);
    if(!$coupon_id){
        sys_msg('缺少参数');
    }
    $smarty->assign('ur_here',      '领取列表');
    $smarty->assign('action_link',  array('text' => '返回优惠券列表', 'href' => 'coupon.php?act=list'));
    $smarty->assign('full_page',    1);

    $coupon_list = get_coupon_user_list($coupon_id);

    foreach($coupon_list['coupon'] as $k=>$v){
        $v['get_time'] = date('Y/m/d', $v['get_time']);
        if($v['use_time'] > 0){
            $v['use_time'] = date('Y/m/d', $v['use_time']);
        }else{
            $v['use_time'] = '未使用';
        }

        $coupon_list['coupon'][$k] = $v;
    }

    $smarty->assign('coupon_list',  $coupon_list['coupon']);
    $smarty->assign('filter',       $coupon_list['filter']);
    $smarty->assign('record_count', $coupon_list['record_count']);
    $smarty->assign('page_count',   $coupon_list['page_count']);
    $coupon_title = $GLOBALS['db']->getOne("SELECT coupon_title FROM ".$GLOBALS['ecs']->table('coupon')." WHERE id='$coupon_id'");
    if($coupon_title){
        $smarty->assign('coupon_title',$coupon_title);
    }
    /* 排序标记 */
    $sort_flag  = sort_flag($coupon_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('coupon_user_list.htm');
}
/**
 * 取得优惠券列表
 * @return  array
 */
function get_couponlist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('coupon');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('coupon') . " ORDER BY $filter[sort_by] $filter[sort_order]";

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

    return array('coupon' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function get_coupon_user_list($coupon_id){
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['coupon_id'] = intval($coupon_id);
        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('user_coupon')." WHERE coupon_id='$coupon_id'";
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT uc.*,u.user_name,o.order_sn FROM " . $GLOBALS['ecs']->table('user_coupon') . " AS uc LEFT JOIN ".$GLOBALS['ecs']->table('users')." AS u ON uc.user_id=u.user_id  LEFT JOIN ".$GLOBALS['ecs']->table('order_info')." AS o ON uc.order_id=o.order_id  WHERE uc.coupon_id='$coupon_id' ORDER BY $filter[sort_by] $filter[sort_order]";

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

    return array('coupon' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}
?>