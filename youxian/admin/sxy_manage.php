<?php
/**
*/
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'in_list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}
/*------------------------------------------------------ */
//-- 充值记录
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'in_list' || $_REQUEST['act'] == 'query')
{
    /* 权限判断 */
    admin_priv('sxy_in_account');
    $smarty->assign('ur_here',       '充值记录');
    $smarty->assign('action_link',   array('text' => '账户充值', 'href'=>'sxy_manage.php?act=add'));
    $list = in_account_list();
    foreach ($list['list'] as $k=>$v){
        $v['order_time'] = date('Y-m-d H:i:s', $v['order_time']);
        $v['pb_time'] = $v['pb_time']>0?date('Y-m-d H:i:s', $v['pb_time']):'';
        $list['list'][$k] = $v;
    }
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    if(isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1){
        $sort_flag  = sort_flag($list['filter']);
        $smarty->assign($sort_flag['tag'], $sort_flag['img']);
        make_json_result($smarty->fetch('sxy_in_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));exit();
    }
    $smarty->assign('full_page',1);

    //查询代付余额
//    require_once ROOT_PATH . 'includes/sxy.php';
//    $sxy = new sxy();
//    $balance = $sxy->get_balance();
//    if($balance['balancemessage']['messagehead']['status']==0){
//        $smarty->assign('balance', $balance['balancemessage']['messagebody']['balance']);
//    }

    assign_query_info();
    $smarty->display('sxy_in_list.htm');
}
if ($_REQUEST['act'] == 'add')
{
    admin_priv('sxy_add_account');
    $smarty->assign('ur_here',       '代付账户充值');
    $smarty->assign('action_link',   array('text' => '充值记录', 'href'=>'sxy_manage.php?act=in_list'));
    $smarty->display('sxy_add_account.htm');
}
if($_REQUEST['act'] == 'test'){

}
if ($_REQUEST['act'] == 'insert')
{
    admin_priv('sxy_add_account');

    if(!$_REQUEST['amount']){
        sys_msg('请输入充值金额');
    }
    if(!preg_match('/^[\d\.]+$/', $_REQUEST['amount'])){
        sys_msg('充值金额格式不正确');
    }

    require_once ROOT_PATH . 'includes/sxy.php';
    $sxy = new sxy();
    $v_ymd=date('Ymd'); //订单产生日期，要求订单日期格式yyyymmdd.
    $v_mid=$sxy->v_mid;    //商户编号，和首信签约后获得,测试的商户编号444
    $v_date=date('His');
    $v_oid=$v_ymd .'-' . $v_mid . '-' .$v_date;
    $v_rcvname=$sxy->v_mid; //收货人姓名,建议用商户编号代替或者是英文数字。因为首信平台的编号是gb2312的
//    $v_rcvaddr=$_POST['v_rcvaddr']; //收货人地址，可用商户编号代替
//    $v_rcvtel=$_POST['v_rcvtel'];   //收货人电话
//    $v_rcvpost=$_POST['v_rcvpost'];  //收货人邮箱
    $v_amount=$_POST['amount']; //订单金额
    $v_orderstatus="1";//配货状态:0-未配齐，1-已配
//    $v_ordername=$v_rcvname;  //订货人姓名
    $v_moneytype="0";  //0为人民币，1为美元，2为欧元，3为英镑，4为日元，5为韩元，6为澳大利亚元，7为卢布(内卡商户币种只能为人民币)
    $v_url="https://m.cnxunluo.cn/api/sxy_pay_status.php";

    $data = $v_moneytype.$v_ymd.$v_amount.$v_rcvname.$v_oid.$v_mid.$v_url;//七个参数的拼串
    $v_md5info=$sxy->hmac($sxy->key, $data);

    $pay_order = array(
        'v_ymd'=>$v_ymd,
        'v_oid'=>$v_oid,
        'v_amount'=>$v_amount,
        'v_url'=>$v_url,
        'v_md5info'=>$v_md5info
    );

    $v_pstatus=1;
    $v_pstring = '待处理';
    $hasOrder = $GLOBALS['db']->getRow("select id from ".$GLOBALS['ecs']->table('sxy_account')." where oid='$v_oid'");

    if($hasOrder) {
        sys_msg('订单重复提交,请联系管理员');
    } else {
        $data = array(
            'amount'=>$v_amount,
            'oid'=>$v_oid,
            'pstatus'=>$v_pstatus,
            'moneytype'=>$v_moneytype,
            'pstring'=>$v_pstring,
            'pmode'=>'',
            'order_time'=>time(),
            'pay_time'=>'0'
        );
        $insert_id = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('sxy_account'),$data);
        if($insert_id){
            echo $sxy->make_order($pay_order);
            $links[0] = array(
                'text'=>'返回列表',
                'href'=>'sxy_manage.php?act=in_list'
            );
            sys_msg('充值订单记录已生成',0,$links);
        }else{
            sys_msg('充值订单记录生成失败,请联系管理员');
        }
    }
}

/**
 * @access  public
 * @param
 * @return void
 */
function in_account_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤列表 */
//        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'order_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : local_strtotime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (local_strtotime($_REQUEST['end_date']) + 86400);


        $where = " WHERE 1 ";

        /*　时间过滤　*/
        if (!empty($filter['start_date']) && !empty($filter['end_date']))
        {
            $where .= "AND order_time >= " . $filter['start_date']. " AND order_time < '" . $filter['end_date'] . "'";
        }

        $sql = "SELECT COUNT('id') FROM " .$GLOBALS['ecs']->table('sxy_account').
            $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询数据 */
        $sql  = 'SELECT * FROM ' .
            $GLOBALS['ecs']->table('sxy_account').
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