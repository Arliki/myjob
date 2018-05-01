<?php

/**
 * 承销商
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$canAddCxs = admin_priv('cxs_add','',false);
$canEditCxs = admin_priv('cxs_edit','',false);
/*------------------------------------------------------ */
//-- 承销商列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('cxs_list');
    $smarty->assign('ur_here',      '承销商列表');

    if($canAddCxs){
        $smarty->assign('action_link',  array('text' => '添加承销商', 'href' => 'cxs_account.php?act=add'));
    }
    $smarty->assign('full_page',    1);

    $smarty->assign('canAddCxs', $canAddCxs);
    $smarty->assign('canEditCxs', $canEditCxs);

    $cxs_list = get_cxslist();
//    var_dump($dls_list);
    $smarty->assign('cxs_list',  $cxs_list['cxs']);
    $smarty->assign('filter',       $cxs_list['filter']);
    $smarty->assign('record_count', $cxs_list['record_count']);
    $smarty->assign('page_count',   $cxs_list['page_count']);

//    $can_export_dls = admin_priv('export_dls', '', false);
//    $smarty->assign('can_export_dls', $can_export_dls);

    /* 排序标记 */
    $sort_flag  = sort_flag($cxs_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('cxs_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $cxs_list = get_cxslist();
    $smarty->assign('cxs_list',  $cxs_list['cxs']);
    $smarty->assign('filter',       $cxs_list['filter']);
    $smarty->assign('record_count', $cxs_list['record_count']);
    $smarty->assign('page_count',   $cxs_list['page_count']);

    $smarty->assign('canAddCxs', $canAddCxs);
    $smarty->assign('canEditCxs', $canEditCxs);

    /* 排序标记 */
    $sort_flag  = sort_flag($cxs_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('cxs_list.htm'), '',
        array('filter' => $cxs_list['filter'], 'page_count' => $cxs_list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加、编辑代理商
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
{
    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'add';
    $smarty->assign('form_action', $is_add ? 'insert' : 'update');

    /* 初始化、取得办事处信息 */
    if ($is_add)
    {
        admin_priv('cxs_add');
        $cxs = array(

        );
    }
    else
    {
        admin_priv('cxs_edit');
        if (empty($_GET['id']))
        {
            sys_msg('invalid param');
        }

        $id = $_GET['id'];
        $sql = "SELECT * FROM " . $ecs->table('cxs_account') . " WHERE id = '$id'";
        $cxs = $db->getRow($sql);
        if (empty($cxs))
        {
            sys_msg('承销商不存在');
        }
    }
    $smarty->assign('cxs',$cxs);
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
    /*承销商*/
    $cxs_list = $db->getAll("SELECT user_id,user_name FROM ".$ecs->table('admin_user')." WHERE role_id=10 OR role_id=11");
    $smarty->assign('cxs_list', $cxs_list);
    /* 显示模板 */
    if ($is_add)
    {
        $smarty->assign('ur_here', '添加承销商');
    }
    else
    {
        $smarty->assign('ur_here', '编辑承销商');
    }
    $href = 'cxs_manage.php?act=list';

    $smarty->assign('action_link', array('href' => $href, 'text' => '返回列表'));
    assign_query_info();
    $smarty->display('cxs_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑代理商
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'insert';
    if($is_add){
        admin_priv('cxs_add');
    }else{
        admin_priv('cxs_edit');
    }

    if(!preg_match('/^\d+$/', $_POST['cxs_account'])){
        sys_msg('收款银行卡号格式不正确');
    }
    if(!preg_match('/^\d{12}|.+$/', $_POST['cxs_bank_name'])){
        sys_msg('请选择开户行');
    }else{
        $cxs_bank = explode('|',$_POST['cxs_bank_name']);
    }
    if(!$_POST['cxs_bank_province']){
        sys_msg('请选择开户行所在省份');
    }
    if(!$_POST['cxs_bank_city']){
        sys_msg('请选择开户行所在城市');
    }
    if(!preg_match('/^1\d{10}$/', $_POST['cxs_phone'])){
        sys_msg('手机号格式不正确');
    }

    /* 提交值 */
    $cxs = array(
        'id'     => intval($_POST['id']),
        'cxs_phone'   => $_POST['cxs_phone'],
        'cxs_account' => $_POST['cxs_account'],
        'cxs_account_name' => $_POST['cxs_account_name'],
        'cxs_bank_name' => $cxs_bank[1],
        'cxs_bank_code'=> $cxs_bank[0],
        'cxs_bank_province'=> $_POST['cxs_bank_province'],
        'cxs_bank_city'=> $_POST['cxs_bank_city'],
        'admin_id' => intval($_POST['admin_id'])
    );

    /* 保存代理商信息 */
    if ($is_add)
    {
        $phoneExist = $db->getCol("SELECT id FROM ".$ecs->table('cxs_account')." WHERE cxs_phone='".$cxs['cxs_phone']."'");
        if($phoneExist){
            sys_msg('手机号已存在');
        }
        $db->autoExecute($ecs->table('cxs_account'), $cxs, 'INSERT');
        $cxs['id'] = $db->insert_id();
    }
    else
    {
        $cxs_id = intval($_POST['id']);
        $phoneExist = $db->getCol("SELECT id FROM ".$ecs->table('cxs_account')." WHERE id!=".$cxs_id." AND cxs_phone='".$cxs['cxs_phone']."'");
        if($phoneExist){
            sys_msg('手机号已存在');
        }

        $db->autoExecute($ecs->table('cxs_account'), $cxs, 'UPDATE', "id = '$cxs_id'");
    }

    /* 记日志 */
    if ($is_add)
    {
        admin_log($cxs['cxs_account_name'], 'add', '承销商');
    }
    else
    {
        admin_log($cxs['cxs_account_name'], 'edit', '承销商');
    }

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    if ($is_add)
    {
        $links = array(
            array('href' => 'cxs_account.php?act=list', 'text' => '承销商列表')
        );
    }
    else
    {
        $links = array(
            array('href' => 'cxs_account.php?act=list', 'text' => '承销商列表')
        );
    }
    sys_msg('操作成功', 0, $links);
}

/**
 * 取得代理商列表
 * @return  array
 */
function get_cxslist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['cxs_account_name'] = trim($_REQUEST['username']);
        $where = " WHERE 1 ";
        if($filter['cxs_account_name']){
          $like_str = "'%".$filter['cxs_account_name']."%'";
            $where .= " AND (c.cxs_account_name LIKE $like_str OR c.cxs_phone LIKE $like_str OR a.user_name LIKE $like_str) ";
        }

        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('cxs_account')." AS c LEFT JOIN ".$GLOBALS['ecs']->table('admin_user')." AS a ON c.admin_id=a.user_id ".$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT c.*,a.cxs_account,a.user_name FROM " . $GLOBALS['ecs']->table('cxs_account') . " AS c LEFT JOIN ".$GLOBALS['ecs']->table('admin_user')." AS a ON c.admin_id=a.user_id ".$where." ORDER BY $filter[sort_by] $filter[sort_order]";
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

    return array('cxs' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>