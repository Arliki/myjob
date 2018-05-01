<?php

/**
 * ECSHOP 管理中心办事处管理
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: agency.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$exc = new exchange($ecs->table('agency'), $db, 'agency_id', 'agency_name');

/*------------------------------------------------------ */
//-- 办事处列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('ur_here',      $_LANG['agency_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['add_agency'], 'href' => 'agency.php?act=add'));
    $smarty->assign('full_page',    1);

    $agency_list = get_agencylist();
    $smarty->assign('agency_list',  $agency_list['agency']);
    $smarty->assign('filter',       $agency_list['filter']);
    $smarty->assign('record_count', $agency_list['record_count']);
    $smarty->assign('page_count',   $agency_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($agency_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('agency_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $agency_list = get_agencylist();
    $smarty->assign('agency_list',  $agency_list['agency']);
    $smarty->assign('filter',       $agency_list['filter']);
    $smarty->assign('record_count', $agency_list['record_count']);
    $smarty->assign('page_count',   $agency_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($agency_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('agency_list.htm'), '',
        array('filter' => $agency_list['filter'], 'page_count' => $agency_list['page_count']));
}

/*------------------------------------------------------ */
//-- 列表页编辑名称
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_agency_name')
{
    check_authz_json('agency_manage');

    $id     = intval($_POST['id']);
    $name   = json_str_iconv(trim($_POST['val']));

    /* 检查名称是否重复 */
    if ($exc->num("agency_name", $name, $id) != 0)
    {
        make_json_error(sprintf($_LANG['agency_name_exist'], $name));
    }
    else
    {
        if ($exc->edit("agency_name = '$name'", $id))
        {
            admin_log($name, 'edit', 'agency');
            clear_cache_files();
            make_json_result(stripslashes($name));
        }
        else
        {
            make_json_result(sprintf($_LANG['agency_edit_fail'], $name));
        }
    }
}

/*------------------------------------------------------ */
//-- 删除办事处
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('agency_manage');

    $id = intval($_GET['id']);
    $name = $exc->get_name($id);


    $sql_agency_has_goods = "SELECT count(goods_id) FROM ".$ecs->table('goods')." WHERE gys_id=".$id;
    $hasGoods = $db->getCol($sql_agency_has_goods);
    if($hasGoods[0]>0){
        make_json_error('正在销售该供应商提供的商品,不允许删除');exit();
    }
    $exc->drop($id);

    /* 更新管理员、配送地区、发货单、退货单和订单关联的办事处 */
    $table_array = array('admin_user', 'region', 'order_info', 'delivery_order', 'back_order');
    foreach ($table_array as $value)
    {
        $sql = "UPDATE " . $ecs->table($value) . " SET agency_id = 0 WHERE agency_id = '$id'";
        $db->query($sql);
    }

    /* 记日志 */
    admin_log($name, 'remove', 'agency');

    /* 清除缓存 */
    clear_cache_files();

    $url = 'agency.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch')
{
    /* 取得要操作的记录编号 */
    if (empty($_POST['checkboxes']))
    {
        sys_msg($_LANG['no_record_selected']);
    }
    else
    {
        /* 检查权限 */
        admin_priv('agency_manage');

        $ids = $_POST['checkboxes'];

        if (isset($_POST['remove']))
        {
            sys_msg('不允许批量删除');
            /* 删除记录 */
            $sql = "DELETE FROM " . $ecs->table('agency') .
                    " WHERE agency_id " . db_create_in($ids);
            $db->query($sql);

            /* 更新管理员、配送地区、发货单、退货单和订单关联的办事处 */
            $table_array = array('admin_user', 'region', 'order_info', 'delivery_order', 'back_order');
            foreach ($table_array as $value)
            {
                $sql = "UPDATE " . $ecs->table($value) . " SET agency_id = 0 WHERE agency_id " . db_create_in($ids) . " ";
                $db->query($sql);
            }

            /* 记日志 */
            admin_log('', 'batch_remove', 'agency');

            /* 清除缓存 */
            clear_cache_files();

            sys_msg($_LANG['batch_drop_ok']);
        }
    }
}

/*------------------------------------------------------ */
//-- 添加、编辑办事处
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
{
    /* 检查权限 */
    admin_priv('agency_manage');

    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'add';
    $smarty->assign('form_action', $is_add ? 'insert' : 'update');

    /* 初始化、取得办事处信息 */
    if ($is_add)
    {
        $agency = array(
            'agency_id'     => 0,
            'agency_name'   => '',
            'agency_desc'   => '',
            'region_list'   => array()
        );
    }
    else
    {
        if (empty($_GET['id']))
        {
            sys_msg('invalid param');
        }

        $id = $_GET['id'];
        $sql = "SELECT * FROM " . $ecs->table('agency') . " WHERE agency_id = '$id'";
        $agency = $db->getRow($sql);
        if (empty($agency))
        {
            sys_msg('agency does not exist');
        }

        /* 关联的地区 */
        $sql = "SELECT region_id, region_name FROM " . $ecs->table('region') .
                " WHERE agency_id = '$id'";
        $agency['region_list'] = $db->getAll($sql);
    }

    /* 取得所有管理员，标注哪些是该办事处的('this')，哪些是空闲的('free')，哪些是别的办事处的('other') */
    $sql = "SELECT user_id, user_name, CASE " .
            "WHEN agency_id = 0 THEN 'free' " .
            "WHEN agency_id = '$agency[agency_id]' THEN 'this' " .
            "ELSE 'other' END " .
            "AS type " .
            "FROM " . $ecs->table('admin_user')." WHERE role_id=2";
    $agency['admin_list'] = $db->getAll($sql);

    $smarty->assign('agency', $agency);

    /* 取得地区 */
    $country_list = get_regions();
    $smarty->assign('countries', $country_list);

    $khh = array(
        array('code'=>'103100000026','name'=>'中国农业银行'),
        array('code'=>'105100000017','name'=>'中国建设银行'),
        array('code'=>'102100099996','name'=>'中国工商银行'),
        array('code'=>'308584000013','name'=>'招商银行'),
        array('code'=>'302100011000','name'=>'中信银行'),
        array('code'=>'303100000006','name'=>'中国光大银行')
    );
    $khh_province = array (
        0 => '北京市',
        1 => '上海市',
        2 => '广东省',
        3 => '安徽省',
        4 => '重庆市',
        5 => '福建省',
        6 => '甘肃省',
        7 => '广西自治区',
        8 => '贵州省',
        9 => '海南省',
        10 => '河北省',
        11 => '河南省',
        12 => '黑龙江省',
        13 => '湖北省',
        14 => '湖南省',
        15 => '江苏省',
        16 => '江西省',
        17 => '吉林省',
        18 => '辽宁省',
        19 => '内蒙古自治区',
        20 => '宁夏自治区',
        21 => '青海省',
        22 => '山东省',
        23 => '山西省',
        24 => '陕西省',
        25 => '四川省',
        26 => '天津市',
        27 => '新疆自治区',
        28 => '西藏自治区',
        29 => '云南省',
        30 => '浙江省',
    );
    $smarty->assign('khh', $khh);
    $smarty->assign('khh_province', $khh_province);
    /* 显示模板 */
    if ($is_add)
    {
        $smarty->assign('ur_here', $_LANG['add_agency']);
    }
    else
    {
        $smarty->assign('ur_here', $_LANG['edit_agency']);
    }
    if ($is_add)
    {
        $href = 'agency.php?act=list';
    }
    else
    {
        $href = 'agency.php?act=list&' . list_link_postfix();
    }
    $smarty->assign('action_link', array('href' => $href, 'text' => $_LANG['agency_list']));
    assign_query_info();
    $smarty->display('agency_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑办事处
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    /* 检查权限 */
    admin_priv('agency_manage');

    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'insert';

    if(!preg_match('/^\d+$/', $_POST['agency_account'])){
        sys_msg('供应商收款银行卡号格式不正确');
    }
    if(!preg_match('/^\d{12}|.+$/', $_POST['agency_bank_name'])){
        sys_msg('请选择开户行');
    }else{
        $agency_bank = explode('|',$_POST['agency_bank_name']);
    }
    if(!$_POST['agency_bank_province']){
        sys_msg('请选择开户行所在省份');
    }
    if(!$_POST['agency_bank_city']){
        sys_msg('请选择开户行所在城市');
    }
    if(!preg_match('/^1\d{10}$/', $_POST['agency_phone'])){
        sys_msg('供应商联系手机号格式不正确');
    }
    if(!in_array(intval($_POST['agency_pay_type']), array(0,1))){
        sys_msg('请选择供应商结算方式');
    }

    if(!(intval($_POST['agency_first_percent'])>0 && intval($_POST['agency_first_percent'])<100)){
        sys_msg('首付款比例必须为0-100之间的整数');
    }
    if(!$_POST['admins']){
        sys_msg('请指派供应商管理员账号');
    }
    /* 提交值 */
    $agency = array(
        'agency_id'     => intval($_POST['id']),
        'agency_name'   => sub_str($_POST['agency_name'], 255, false),
        'agency_desc'   => $_POST['agency_desc'],
        'agency_reg_addr'   => $_POST['agency_reg_addr'],
        'agency_owner'   => $_POST['agency_owner'],
        'agency_code'   => $_POST['agency_code'],
        'agency_account'   => $_POST['agency_account'],
        'agency_shop_addr'   => $_POST['agency_shop_addr'],
        'agency_contact'   => $_POST['agency_contact'],
        'agency_phone'   => $_POST['agency_phone'],
        'agency_account_name' => $_POST['agency_account_name'],
        'agency_pay_type' => intval($_POST['agency_pay_type']),
        'agency_first_percent' => intval($_POST['agency_first_percent']),
        'agency_bank_name' => $agency_bank[1],
        'agency_bank_code'=> $agency_bank[0],
        'agency_bank_province'=> $_POST['agency_bank_province'],
        'agency_bank_city'=> $_POST['agency_bank_city']
    );

    /* 判断名称是否重复 */
    if (!$exc->is_only('agency_name', $agency['agency_name'], $agency['agency_id']))
    {
        sys_msg($_LANG['agency_name_exist']);
    }

    /* 检查是否选择了地区 */
    if (empty($_POST['regions']))
    {
        sys_msg($_LANG['no_regions']);
    }

    /* 保存办事处信息 */
    if ($is_add)
    {
        $db->autoExecute($ecs->table('agency'), $agency, 'INSERT');
        $agency['agency_id'] = $db->insert_id();
    }
    else
    {
        $db->autoExecute($ecs->table('agency'), $agency, 'UPDATE', "agency_id = '$agency[agency_id]'");
    }

    /* 更新管理员表和地区表 */
    if (!$is_add)
    {
        $sql = "UPDATE " . $ecs->table('admin_user') . " SET agency_id = 0 WHERE agency_id = '$agency[agency_id]'";
        $db->query($sql);

        $sql = "UPDATE " . $ecs->table('region') . " SET agency_id = 0 WHERE agency_id = '$agency[agency_id]'";
        $db->query($sql);
    }

    if (isset($_POST['admins']))
    {
//        $sql = "UPDATE " . $ecs->table('admin_user') . " SET agency_id = '$agency[agency_id]' WHERE user_id " . db_create_in($_POST['admins']);
        $sql = "UPDATE " . $ecs->table('admin_user') . " SET agency_id = '$agency[agency_id]' WHERE user_id=" . intval($_POST['admins']);
        $db->query($sql);
    }

    if (isset($_POST['regions']))
    {
        $sql = "UPDATE " . $ecs->table('region') . " SET agency_id = '$agency[agency_id]' WHERE region_id " . db_create_in($_POST['regions']);
        $db->query($sql);
    }

    /* 记日志 */
    if ($is_add)
    {
        admin_log($agency['agency_name'], 'add', 'agency');
    }
    else
    {
        admin_log($agency['agency_name'], 'edit', 'agency');
    }

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    if ($is_add)
    {
        $links = array(
            array('href' => 'agency.php?act=add', 'text' => $_LANG['continue_add_agency']),
            array('href' => 'agency.php?act=list', 'text' => $_LANG['back_agency_list'])
        );
        sys_msg($_LANG['add_agency_ok'], 0, $links);
    }
    else
    {
        $links = array(
            array('href' => 'agency.php?act=list&' . list_link_postfix(), 'text' => $_LANG['back_agency_list'])
        );
        sys_msg($_LANG['edit_agency_ok'], 0, $links);
    }
}

/**
 * 取得办事处列表
 * @return  array
 */
function get_agencylist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'agency_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('agency');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT a.*,au.gys_account FROM " . $GLOBALS['ecs']->table('agency') . " AS a LEFT JOIN ".$GLOBALS['ecs']->table('admin_user')." AS au ON a.agency_id=au.agency_id ORDER BY $filter[sort_by] $filter[sort_order]";

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

    return array('agency' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>