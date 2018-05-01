<?php
/**
 * 承销商提现
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
//-- 提现记录
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list' || $_REQUEST['act'] == 'query')
{
    /* 权限判断 */
    admin_priv('cxs_tixian_list');
    $smarty->assign('ur_here',       '提现记录');
    $smarty->assign('action_link',   array('text' => '申请提现', 'href'=>'cxs_tixian.php?act=add'));
    $list = tixian_list();
    $smarty->assign('full_page',1);
    foreach ($list['list'] as $k=>$v){
        $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        $v['update_time'] = $v['update_time'] > 0 ? date('Y-m-d H:i:s', $v['update_time']) : '';
        $v['amount'] = '￥'.$v['amount'].'元';
        $v['is_paid'] = $v['is_paid'] == 1 ? '已处理':'审核中';
        $list['list'][$k] = $v;
    }
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    if(check_authz('cxs_tixian_check')){
        $smarty->assign('can_check_out_account', true);
    }
    if(isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1){
        $sort_flag  = sort_flag($list['filter']);
        $smarty->assign($sort_flag['tag'], $sort_flag['img']);
        $smarty->assign('full_page',0);

        make_json_result($smarty->fetch('cxs_tixian_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));exit();
    }


    assign_query_info();
    $smarty->display('cxs_tixian_list.htm');
}

if ($_REQUEST['act'] == 'add')
{
    admin_priv('cxs_tixian');
    if(!isCxs()){
        sys_msg('没有权限');
    }
    $smarty->assign('ur_here',       '申请提现');
    $smarty->assign('action_link',   array('text' => '提现记录', 'href'=>'cxs_tixian.php?act=list'));

    $cxs_total_money = $GLOBALS['db']->getOne("SELECT cxs_account FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE user_id=".$_SESSION['admin_id']);
//    $cxs_id = get_cxs_id();
//    $frozen_money = $GLOBALS['db']->getOne("SELECT SUM(amount) FROM ".$GLOBALS['ecs']->table('cxs_tixian')." WHERE cxs_id='$cxs_id' AND is_paid=0");
//    $cxs_can_use_money = (intval($cxs_total_money*100)-intval($frozen_money*100))/100;
    $smarty->assign('cxs_total_money', price_format($cxs_total_money, false));
//    $smarty->assign('frozen_money', price_format($frozen_money, false));
//    $smarty->assign('cxs_can_use_money', price_format($cxs_can_use_money, false));
    $smarty->display('cxs_tixian.htm');
}
if ($_REQUEST['act'] == 'insert')
{
    admin_priv('cxs_tixian');
    if(!isCxs()){
        sys_msg('没有操作权限');
    }
    if(!$_REQUEST['amount']){
        sys_msg('请输入提现金额');
    }
    if(!preg_match('/^[\d\.]+$/', $_REQUEST['amount'])){
        sys_msg('提现金额格式不正确');
    }
    if(intval($_REQUEST['amount']*100)<1000){
        sys_msg('提现金额最少10元');
    }

    $cxs_total_money = $GLOBALS['db']->getOne("SELECT cxs_account FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE user_id=".$_SESSION['admin_id']);
//    $frozen_money = $GLOBALS['db']->getOne("SELECT SUM(amount) FROM ".$GLOBALS['ecs']->table('cxs_tixian')." WHERE cxs_id='$cxs_id' AND is_paid=0");-intval($frozen_money*100)
    if($_REQUEST['amount']*100 > intval($cxs_total_money*100)){
        sys_msg('您的账户里没有这么多钱');
    }
    $cxs_note = htmlspecialchars($_REQUEST['cxs_note']);
    $sql = "INSERT INTO " .$ecs->table('cxs_tixian').
        " VALUES ('', ".$_SESSION['admin_id'].", '0', '$_REQUEST[amount]', '".time()."', '0', '', '$cxs_note', '0', '0')";
    $db->query($sql);
    $id = $db->insert_id();
    if($id>0){
        $link[0] = array(
            'text'=>'返回列表',
            'href'=>'cxs_tixian.php?act=list'
        );
        sys_msg('提现申请已提交,请等待管理员审核打款',0,$link);
    }else{
        sys_msg('提现申请提交失败,请联系管理员',0,$link);
    }

}

if ($_REQUEST['act'] == 'check')
{
    admin_priv('cxs_tixian_check');

    $smarty->assign('ur_here',       '审核提现');
    $smarty->assign('action_link',   array('text' => '提现记录', 'href'=>'cxs_tixian.php?act=list'));

    $id = intval($_REQUEST['id']);
    if(!$id){
        sys_msg('缺少记录id参数');
    }

    $row = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('cxs_tixian')." WHERE id='$id'");

    $cxs_info = $GLOBALS['db']->getRow("SELECT ca.*,a.cxs_account AS cxs_money_account,a.user_name FROM ".$GLOBALS['ecs']->table('cxs_account')." AS ca LEFT JOIN ".$GLOBALS['ecs']->table('admin_user')." AS a ON ca.admin_id=a.user_id WHERE a.user_id='$row[cxs_id]'");

    $smarty->assign('cxs_account_money', price_format($cxs_info['cxs_money_account'],false));

    $smarty->assign('cxs_info', $cxs_info);

    $row['add_time'] = date('Y-m-d H:i:s', $row['add_time']);
    $row['amount'] = price_format($row['amount'], false);
    $smarty->assign('row_info', $row);
    $smarty->display('cxs_tixian_check.htm');
}

if($_REQUEST['act'] == 'action'){
    admin_priv('cxs_tixian_check');
    $is_paid = intval($_REQUEST['is_paid']);
    $admin_note = htmlspecialchars($_REQUEST['admin_note']);
    $id = intval($_REQUEST['id']);
    if(!$is_paid && !$admin_note){
        sys_msg('请填写说明');
    }
    $out_money_info = $GLOBALS['db']->getRow("SELECT cxs_id,amount,is_paid FROM ".$GLOBALS['ecs']->table('cxs_tixian')." WHERE id='$id'");
    if($out_money_info['is_paid']=='1'){
        sys_msg('已付款,不允许再次操作');
    }
    if($is_paid == 0){
        $sql_update_cxs_tixian = "UPDATE ".$GLOBALS['ecs']->table('cxs_tixian')." SET is_paid='$is_paid',admin_note='$admin_note' WHERE id='$id'";
        $res1 = $GLOBALS['db']->query($sql_update_cxs_tixian);
        $links[0] = array(
            'text'=>'返回列表',
            'href'=>'cxs_tixian.php?act=list'
        );
        sys_msg('操作成功',0,$links);
    }else{
        if(intval($out_money_info['amount']*100)<1000){
            sys_msg('代付金额最少10元');
        }
        if(intval($out_money_info['amount']*100)>5000000){
            sys_msg('代付金额不能超过50000元');
        }
        $GLOBALS['db']->query("SET AUTOCOMMIT=0");

        $cxs_info = $GLOBALS['db']->getRow("SELECT ca.*,a.cxs_account AS cxs_money_account,a.user_name FROM ".$GLOBALS['ecs']->table('cxs_account')." AS ca LEFT JOIN ".$GLOBALS['ecs']->table('admin_user')." AS a ON ca.admin_id=a.user_id WHERE a.user_id='$out_money_info[cxs_id]'");


        if(intval(100*$cxs_info['cxs_money_account']) < intval(100*$out_money_info['amount'])){
            $GLOBALS['db']->query('ROLLBACK');
            $links[0] = array(
                'text'=>'返回列表',
                'href'=>'cxs_tixian.php?act=list'
            );
            sys_msg('操作失败，供应商账号异常, 请联系管理员',0,$links);
        }else{
//            $gys_info = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('agency')." WHERE agency_id='$out_money_info[gys_id]'");

            $sql_update_out_account = "UPDATE ".$GLOBALS['ecs']->table('cxs_tixian')." SET is_paid='$is_paid',admin_note='$admin_note',update_time='".time()."' WHERE id='$id'";
            $res1 = $GLOBALS['db']->query($sql_update_out_account);

            $cxs_account = (intval(100*$cxs_info['cxs_money_account']) - intval(100*$out_money_info['amount']))/100;
            $sql_update_cxs_total_account = "UPDATE ".$GLOBALS['ecs']->table('admin_user')." SET cxs_account='$cxs_account' WHERE user_id='".$out_money_info['cxs_id']."'";
            $res2 = $GLOBALS['db']->query($sql_update_cxs_total_account);

            if($res1 && $res2){
                //首信易代付
                require_once ROOT_PATH . 'includes/sxy.php';
                $sxy = new sxy();
                $pc_sn = $sxy->v_mid.'-'.date('Ymd',time()).'-C'.str_pad($id, 10, '0', STR_PAD_LEFT);

//            $df_data = "1|1.00|10776-20170928-0000000001$6210812450006318715|张晓欢|中国建设银行|河南省|洛阳市|1.00|20170925ceshi|105100000017";
                $df_data = "1|".$out_money_info['amount']."|".$pc_sn."$".$cxs_info['cxs_account']."|".$cxs_info['cxs_account_name']."|".$cxs_info['cx_bank_name']."|".$cxs_info['cxs_bank_province']."|".$cxs_info['cxs_bank_city']."|".$out_money_info['amount']."|C".str_pad($id, 10, '0', STR_PAD_LEFT)."|".$cxs_info['cxs_bank_code'];
                $daifu_res = $sxy->sxy_daifu($df_data);
                if($daifu_res['message']['status']==0){
                    //代付成功
                    $daifu_data = array(
                        'amount'=>$out_money_info['amount'],
                        'sn'=>$pc_sn,
                        'card_no'=>$cxs_info['cxs_account'],
                        'card_user'=>$cxs_info['cxs_account_name'],
                        'bank'=>$cxs_info['cxs_bank_name'],
                        'status'=>$daifu_res['message']['status'],
                        'statusdesc'=>$daifu_res['message']['statusdesc'],
                        'addtime'=>time(),
                        'tid'=>$id
                    );
                    $daifu_log = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('sxy_daifu'), $daifu_data, 'INSERT');
                    if($daifu_log){
                        $GLOBALS['db']->query('COMMIT');
                        $links[0] = array(
                            'text'=>'返回列表',
                            'href'=>'cxs_tixian.php?act=list'
                        );
                        sys_msg('操作成功',0,$links);
                    }else{
                        $GLOBALS['db']->query('ROLLBACK');
                        $links[0] = array(
                            'text'=>'返回列表',
                            'href'=>'cxs_tixian.php?act=list'
                        );
                        sys_msg('操作失败，请联系管理员',0,$links);
                    }
                }else{
                    //代付失败
                    $GLOBALS['db']->query('ROLLBACK');
                    echo '代付失败,请联系管理员.错误代码：'.$daifu_res['message']['statusdesc'];
                }
                //首信易代付END
            }else{
                $GLOBALS['db']->query('ROLLBACK');
                $links[0] = array(
                    'text'=>'返回列表',
                    'href'=>'cxs_tixian.php?act=list'
                );
                sys_msg('操作失败，请联系管理员',0,$links);
            }
        }
        $GLOBALS['db']->query("SET AUTOCOMMIT=1");
    }
}
/**
 *
 *
 * @access  public
 * @param
 *
 * @return void
 */
function tixian_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤列表 */
//        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : local_strtotime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (local_strtotime($_REQUEST['end_date']) + 86400);

        if(isCxs()){
//            $sql_cxs = "SELECT ag.agency_id FROM ".$GLOBALS['ecs']->table('admin_user')." AS ad LEFT JOIN ".
//                $GLOBALS['ecs']->table('agency')." AS ag ON ad.agency_id=ag.agency_id ".
//                "WHERE ad.user_id='".$_SESSION['admin_id']."'";
//            $cxs_id = $GLOBALS['db']->getOne($sql_cxs);
            $cxs_id = $_SESSION['admin_id'];
            if(!$cxs_id){
                return false;
            }
            $filter['cxs_id'] = $cxs_id;
        }

        $where = " WHERE 1 ";
//        if ($filter['keywords'])
//        {
//            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
//        }
        /*　时间过滤　*/
//        if (!empty($filter['start_date']) && !empty($filter['end_date']))
//        {
//            $where .= "AND ga.add_time >= " . $filter['start_date']. " AND ga.add_time < '" . $filter['end_date'] . "'";
//        }

        if(!empty($filter['cxs_id'])){
            $where .= " AND ct.cxs_id=".$filter['cxs_id'];
        }

        $sql = "SELECT COUNT('ct.id') FROM " .$GLOBALS['ecs']->table('cxs_tixian')." AS ct".
            $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询数据 */
        $sql  = 'SELECT ct.*,a.user_name FROM ' .
            $GLOBALS['ecs']->table('cxs_tixian')." AS ct LEFT JOIN ".
            $GLOBALS['ecs']->table('admin_user')." AS a ON ct.cxs_id=a.user_id ".
            $where . " ORDER by " . $filter['sort_by'] . " " .$filter['sort_order']. " LIMIT ".$filter['start'].", ".$filter['page_size'];
        $filter['keywords'] = stripslashes($filter['keywords']);
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