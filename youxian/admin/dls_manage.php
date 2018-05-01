<?php

/**
 * 代理商
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
include(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/order.php');
$canAddDls = admin_priv('dls_add','',false);
$canEditDls = admin_priv('dls_edit','',false);
$canJiesuanDls = admin_priv('dls_jiesuan','',false);
/*------------------------------------------------------ */
//-- 代理商列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('dls_list');
    $smarty->assign('ur_here',      '代理商列表');

    if($canAddDls){
        $smarty->assign('action_link',  array('text' => '添加代理商', 'href' => 'dls_manage.php?act=add'));
    }
    $smarty->assign('full_page',    1);

    $smarty->assign('canAddDls', $canAddDls);
    $smarty->assign('canEditDls', $canEditDls);
    $smarty->assign('canJiesuanDls', $canJiesuanDls);

    $dls_list = get_dlslist();
//    var_dump($dls_list);
    $smarty->assign('dls_list',  $dls_list['dls']);
    $smarty->assign('filter',       $dls_list['filter']);
    $smarty->assign('record_count', $dls_list['record_count']);
    $smarty->assign('page_count',   $dls_list['page_count']);

    $can_export_dls = admin_priv('export_dls', '', false);
    $smarty->assign('can_export_dls', $can_export_dls);

    /* 排序标记 */
    $sort_flag  = sort_flag($dls_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('dls_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $dls_list = get_dlslist();
    $smarty->assign('dls_list',  $dls_list['dls']);
    $smarty->assign('filter',       $dls_list['filter']);
    $smarty->assign('record_count', $dls_list['record_count']);
    $smarty->assign('page_count',   $dls_list['page_count']);

    $smarty->assign('canAddDls', $canAddDls);
    $smarty->assign('canEditDls', $canEditDls);
    $smarty->assign('canJiesuanDls', $canJiesuanDls);

    /* 排序标记 */
    $sort_flag  = sort_flag($dls_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('dls_list.htm'), '',
        array('filter' => $dls_list['filter'], 'page_count' => $dls_list['page_count']));
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
        admin_priv('dls_add');
        $dls = array(

        );
    }
    else
    {
        admin_priv('dls_edit');
        if (empty($_GET['id']))
        {
            sys_msg('invalid param');
        }

        $id = $_GET['id'];
        $sql = "SELECT * FROM " . $ecs->table('dls') . " WHERE dls_id = '$id'";
        $dls = $db->getRow($sql);
        if (empty($dls))
        {
            sys_msg('代理商不存在');
        }
    }
    $smarty->assign('dls',$dls);
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
    if(isCxs()){
        $cxs_list = array(
            array('user_id'=>$_SESSION['admin_id'], 'user_name'=>$_SESSION['admin_name'])
        );
        $dls_list = $db->getAll("SELECT dls_id,username FROM ".$ecs->table('dls')." WHERE cxs_id=".$_SESSION['admin_id']);
    }else{
        $cxs_list = $db->getAll("SELECT user_id,user_name FROM ".$ecs->table('admin_user')." WHERE role_id=10 OR role_id=11");
        $dls_list = $db->getAll("SELECT dls_id,username FROM ".$ecs->table('dls'));
    }
    $smarty->assign('cxs_list', $cxs_list);
    $smarty->assign('dls_list', $dls_list);
    /* 显示模板 */
    if ($is_add)
    {
        $smarty->assign('ur_here', '添加代理商');
    }
    else
    {
        $smarty->assign('ur_here', '编辑代理商');
    }
    $href = 'dls_manage.php?act=list';

    $smarty->assign('action_link', array('href' => $href, 'text' => '返回列表'));
    assign_query_info();
    $smarty->display('dls_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑代理商
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'insert';
    if($is_add){
        admin_priv('dls_add');
    }else{
        admin_priv('dls_edit');
    }

    if(!preg_match('/^\d+$/', $_POST['dls_bank_card'])){
        sys_msg('收款银行卡号格式不正确');
    }
    if(!preg_match('/^\d{12}|.+$/', $_POST['dls_bank_name'])){
        sys_msg('请选择开户行');
    }else{
        $dls_bank = explode('|',$_POST['dls_bank_name']);
    }
    if(!$_POST['dls_bank_province']){
        sys_msg('请选择开户行所在省份');
    }
    if(!$_POST['dls_bank_city']){
        sys_msg('请选择开户行所在城市');
    }
    if(!preg_match('/^1\d{10}$/', $_POST['phone'])){
        sys_msg('手机号格式不正确');
    }

    /* 提交值 */
    $dls = array(
        'dls_id'     => intval($_POST['dls_id']),
        'username'   => sub_str($_POST['username'], 255, false),
        'phone'   => $_POST['phone'],
        'dls_bank_card' => $_POST['dls_bank_card'],
        'dls_account_name' => $_POST['dls_account_name'],
        'dls_bank_name' => $dls_bank[1],
        'dls_bank_code'=> $dls_bank[0],
        'dls_bank_province'=> $_POST['dls_bank_province'],
        'dls_bank_city'=> $_POST['dls_bank_city'],
        'cxs_id' => intval($_POST['cxs_id']),
        'parent_id'=>intval($_POST['parent_id']),
        'is_validated'=>1,
        'dls_deposit'=>intval($_POST['dls_deposit'])
    );
//    var_dump($dls);die;
    /* 保存代理商信息 */
    if ($is_add)
    {
        if(!$_POST['password']){
            sys_msg('请填写登录密码');
        }
        $dls['salt'] = rand(100000,999999);
        $dls['password'] = sha1(trim($_POST['password']).$dls['salt']);

        $phoneExist = $db->getCol("SELECT dls_id FROM ".$ecs->table('dls')." WHERE phone='".$dls['phone']."'");
        if($phoneExist){
            sys_msg('手机号已存在');
        }
        $db->autoExecute($ecs->table('dls'), $dls, 'INSERT');
        $dls['dls_id'] = $db->insert_id();
    }
    else
    {
        $dls_id = intval($_POST['dls_id']);
        $phoneExist = $db->getCol("SELECT dls_id FROM ".$ecs->table('dls')." WHERE dls_id!=".$dls_id." AND phone='".$dls['phone']."'");
        if($phoneExist){
            sys_msg('手机号已存在');
        }

        if(isset($_POST['password']) && trim($_POST['password'])!=''){
            $salt = $phoneExist = $db->getOne("SELECT salt FROM ".$ecs->table('dls')." WHERE dls_id='".$dls_id."'");
            $dls['password'] = sha1(trim($_POST['password']).$salt);
        }
        $db->autoExecute($ecs->table('dls'), $dls, 'UPDATE', "dls_id = '$dls_id'");
    }

    /* 记日志 */
    if ($is_add)
    {
        admin_log($dls['username'], 'add', '代理商');
    }
    else
    {
        admin_log($dls['username'], 'edit', '代理商');
    }

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    if ($is_add)
    {
        $links = array(
            array('href' => 'dls_manage.php?act=list', 'text' => '代理商列表')
        );
    }
    else
    {
        $links = array(
            array('href' => 'dls_manage.php?act=list', 'text' => '代理商列表')
        );
    }
    sys_msg('操作成功', 0, $links);
}

elseif ($_REQUEST['act'] == 'export_dls'){
    admin_priv('export_dls');

    include_once('includes/cls_phpzip.php');
    $zip = new PHPZip;
    $dls_list = get_dlslist();
    $res = $dls_list['dls'];

    $dls = array(
        'ID' => '""',
        'username' => '""',
        'phone' => '""',
        'deposit' => '""',
        'verified' => '""',
        'introducer' => '""',
        'team' => '""',
        'salecount' => '""',
        'yijiesuan' => '""',
        'last_jiesuan_date' => '""'
    );

    $theader = array('编号','代理商','电话','保证金','审核','介绍人','团队','总销量','已结算','上次结算日期');

    $content = '"' . implode('","', $theader) . "\"\n";

    foreach ($res as $row) {
        $dls['ID'] = '"'.$row['dls_id'].'"';
        $dls['username'] = '"'.$row['username'].'"';
        $dls['phone'] = '"'.$row['phone'].'"';
        $dls['deposit'] = '"'.($row['dls_deposit']==1?'是':'否').'"';
        $dls['verified'] = '"'.($row['is_validated']==1?'是':'否').'"';
        $dls['introducer'] = '"'.$row['parent_name'].'"';
        $dls['team'] = '"'.$row['team_num'].'人[经纪人'.$row['jjr_num'].'人,注册会员'.$row['reguser_num'].'人]"';
        $dls['salecount'] = '"'.($row['total_sale']?$row['total_sale']:0).'元"';
        $dls['yijiesuan'] = '"'.($row['money_yijiesuan']?$row['money_yijiesuan']:0).'"';
        $dls['last_jiesuan_date'] = '"'.$row['last_jiesuan_date'].'"';
        $content .= implode(",", $dls) . "\n";
    }
    $charset = 'gb2312';
    $export_file_name = date('Ymd',time()).'代理商列表';
    $zip->add_file(ecs_iconv(EC_CHARSET, $charset, $content), ecs_iconv(EC_CHARSET, $charset, $export_file_name).'.csv');

    header("Content-Disposition: attachment; filename=".ecs_iconv(EC_CHARSET, $charset, $export_file_name).".zip");
    header("Content-Type: application/unknown;charset=gb2312");
    die($zip->file());

}
elseif ($_REQUEST['act'] == 'export_dls_sale_detail'){
    admin_priv('export_dls');
    $dls_id = intval($_REQUEST['dls_id']);
    if(!$dls_id){
        sys_msg('缺少代理商id');
    }
    include_once('includes/cls_phpzip.php');
    $zip = new PHPZip;

    $res = get_dls_orders($dls_id);
    foreach($res as $k=>$v){
        $v['order_sn'] = "'".$v['order_sn'];
        $v['status'] = preg_replace('/<\/?[^>]+>/','',$_LANG['os'][$v['order_status']].','.$_LANG['ps'][$v['pay_status']].','.$_LANG['ss'][$v['shipping_status']]);
        $v['goods_title'] = preg_replace('/<br>/',"\r\n", $v['goods_title']);
        $v['total_fee'] = '￥'.$v['total_fee'].'元';
        $res[$k] = $v;
    }
    $dls = array(
        'order_sn' => '""',
        'goods_title' => '""',
        'total_fee' => '""',
        'status' => '""',
        'username' => '""'
    );

    $theader = array('订单号','商品名称','总金额','订单状态','会员名');

    $content = '"' . implode('","', $theader) . "\"\n";

    foreach ($res as $row) {
        $dls['order_sn'] = '"'.$row['order_sn'].'"';
        $dls['goods_title'] = '"'.$row['goods_title'].'"';
        $dls['total_fee'] = '"'.$row['total_fee'].'"';
        $dls['status'] = '"'.$row['status'].'"';
        $dls['username'] = '"'.$row['username'].'"';

        $content .= implode(",", $dls) . "\n";
    }

    $charset = 'gb2312';
    $export_file_name = date('Ymd',time()).'代理商'.$dls_id.'销售明细';
    $zip->add_file(ecs_iconv(EC_CHARSET, $charset, $content), ecs_iconv(EC_CHARSET, $charset, $export_file_name).'.csv');

    header("Content-Disposition: attachment; filename=".ecs_iconv(EC_CHARSET, $charset, $export_file_name).".zip");
    header("Content-Type: application/unknown;charset=gb2312");
    die($zip->file());

}
elseif ($_REQUEST['act'] == 'sale_detail'){
    $dls_id = intval($_REQUEST['dls_id']);
    if(!$dls_id){
        make_json_error('缺少代理商id');
    }
    $res = get_dls_orders($dls_id);
    foreach($res as $k=>$v){
        $v['status'] = $_LANG['os'][$v['order_status']].','.$_LANG['ps'][$v['pay_status']].','.$_LANG['ss'][$v['shipping_status']];
        $res[$k] = $v;
    }
    echo json_encode(array('success'=>1,'export_url'=>'dls_manage.php?act=export_dls_sale_detail&dls_id='.$dls_id,'data'=>$res));die;

}
function get_dls_orders($dls_id)
{
    $sql = "SELECT order_id,order_sn,(".order_amount_field().") AS total_fee,order_status,pay_status,shipping_status,(SELECT group_concat(concat(goods_name,' : ￥', goods_price) separator '<br>') FROM ".$GLOBALS['ecs']->table('order_goods')." WHERE order_id=o.order_id) AS goods_title,(SELECT user_name FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id=o.user_id) AS username FROM ".$GLOBALS['ecs']->table('order_info')." AS o WHERE dls_id=".$dls_id." ORDER BY order_id DESC";
    return $GLOBALS['db']->getAll($sql);
}
/**
 * 取得代理商列表
 * @return  array
 */
function get_dlslist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'dls_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['username'] = trim($_REQUEST['username']);
        $where = " WHERE 1 ";
        if($filter['username']){
            $where .= " AND username LIKE '%".$filter['username']."%' ";
        }
        if(isCxs()){
            $where .= " AND cxs_id=".$_SESSION['admin_id']." ";
        }
        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('dls').$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT *,(SELECT SUM(amount) FROM ".$GLOBALS['ecs']->table('dls_jiesuan')." WHERE dls_id=d.dls_id) AS money_yijiesuan,(SELECT concat(start_day,'--',end_day) FROM ".$GLOBALS['ecs']->table('dls_jiesuan')." WHERE dls_id=d.dls_id ORDER BY end_day DESC LIMIT 1) AS last_jiesuan_date,IFNULL((SELECT username FROM ".$GLOBALS['ecs']->table('dls')." WHERE dls_id=d.parent_id),'无') AS parent_name,(SELECT COUNT(user_id) FROM ".$GLOBALS['ecs']->table('users')." WHERE dls_id=d.dls_id) AS team_num,(SELECT COUNT(user_id) FROM ".$GLOBALS['ecs']->table('users')." WHERE dls_id=d.dls_id AND user_rank=6) AS jjr_num,(SELECT COUNT(user_id) FROM ".$GLOBALS['ecs']->table('users')." WHERE dls_id=d.dls_id AND user_rank<>6) AS reguser_num,(SELECT SUM(goods_amount) FROM ".$GLOBALS['ecs']->table('order_info')." WHERE dls_id=d.dls_id AND pay_status=2 AND (shipping_status=1 OR shipping_status=2)) AS total_sale FROM " . $GLOBALS['ecs']->table('dls') . " AS d ".$where." ORDER BY $filter[sort_by] $filter[sort_order]";

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

    return array('dls' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>