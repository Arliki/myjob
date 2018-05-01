<?php

/**
 * ECSHOP 会员帐目管理(包括预付款，余额)
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user_account.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'out_list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 提现记录
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'out_list' || $_REQUEST['act'] == 'query')
{
    /* 权限判断 */
    admin_priv('gys_out_account');
    $smarty->assign('ur_here',       '提现记录');
    $smarty->assign('action_link',   array('text' => '申请提现', 'href'=>'gys_out_account.php?act=add'));
    $list = out_account_list();
    if($list === false){
        sys_msg('供应商账号绑定有误');
    }
    foreach ($list['list'] as $k=>$v){
        $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        $v['update_time'] = $v['update_time'] > 0 ? date('Y-m-d H:i:s', $v['update_time']) : '';
        $v['amount'] = '￥'.$v['amount'].'元';
        $v['is_paid'] = $v['is_paid'] == 1 ? '已处理': ($v['is_paid'] == -1 ? '已取消':'审核中');
        $list['list'][$k] = $v;
    }
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    if(check_authz('gys_out_account_check')){
        $smarty->assign('can_check_out_account', true);
    }
    if(isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1){
        $sort_flag  = sort_flag($list['filter']);
        $smarty->assign($sort_flag['tag'], $sort_flag['img']);
        make_json_result($smarty->fetch('gys_out_account_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));exit();
    }
    $smarty->assign('full_page',1);


    assign_query_info();
    $smarty->display('gys_out_account_list.htm');
}

if ($_REQUEST['act'] == 'add')
{
    admin_priv('gys_out_account');
    $smarty->assign('ur_here',       '申请提现');
    $smarty->assign('action_link',   array('text' => '提现记录', 'href'=>'gys_out_account.php?act=out_list'));
    $gys_total_money = $GLOBALS['db']->getOne("SELECT gys_account FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE user_id=".$_SESSION['admin_id']);
    $gys_id = get_gys_id();
    $frozen_money = $GLOBALS['db']->getOne("SELECT SUM(amount) FROM ".$GLOBALS['ecs']->table('gys_account')." WHERE gys_id='$gys_id' AND is_paid=0");
    $gys_can_use_money = (intval($gys_total_money*100)-intval($frozen_money*100))/100;
    $smarty->assign('gys_total_money', price_format($gys_total_money, false));
    $smarty->assign('frozen_money', price_format($frozen_money, false));
    $smarty->assign('gys_can_use_money', price_format($gys_can_use_money, false));
    $smarty->display('gys_out_account.htm');
}
if ($_REQUEST['act'] == 'insert')
{
    admin_priv('gys_out_account');
    if(!isGys()){
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
    $gys_id = get_gys_id();
    $gys_total_money = $GLOBALS['db']->getOne("SELECT gys_account FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE user_id=".$_SESSION['admin_id']);
    $frozen_money = $GLOBALS['db']->getOne("SELECT SUM(amount) FROM ".$GLOBALS['ecs']->table('gys_account')." WHERE gys_id='$gys_id' AND is_paid=0");
    if($_REQUEST['amount']*100 > (intval($gys_total_money*100)-intval($frozen_money*100))){
        sys_msg('您的账户里没有这么多钱');
    }
    $gys_note = htmlspecialchars($_REQUEST['gys_note']);
    $sql = "INSERT INTO " .$ecs->table('gys_account').
        " VALUES ('', '$gys_id', '0', '$_REQUEST[amount]', '".time()."', '0', '', '$gys_note', '0', '0')";
    $db->query($sql);
    $id = $db->insert_id();
    if($id>0){
        $link[0] = array(
            'text'=>'返回列表',
            'href'=>'gys_out_account.php?act=out_list'
        );
        sys_msg('提现申请已提交,请等待管理员审核打款',0,$link);
    }else{
        sys_msg('提现申请提交失败,请联系管理员',0,$link);
    }

}
if ($_REQUEST['act'] == 'check')
{
    admin_priv('gys_out_account_check');

    $smarty->assign('ur_here',       '审核提现');
    $smarty->assign('action_link',   array('text' => '提现记录', 'href'=>'gys_out_account.php?act=out_list'));

    $id = intval($_REQUEST['id']);
    if(!$id){
        sys_msg('缺少记录id参数');
    }
    $row = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('gys_account')." WHERE id='$id'");
    $frozen_money = $GLOBALS['db']->getOne("SELECT SUM(amount) FROM ".$GLOBALS['ecs']->table('gys_account')." WHERE gys_id='".$row['gys_id']."' AND is_paid=0");
    $gys_info = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('agency')." WHERE agency_id='$row[gys_id]'");
    $gys_account_money=getGysAccountByAcencyId($row['gys_id']);
    if($gys_account_money == '-999'){
        $smarty->assign('warning_info','供应商账号异常,请联系供应商管理员核对信息(账号绑定有误)');
    }else{
        $smarty->assign('gys_account_money', price_format($gys_account_money,false));
    }
    $smarty->assign('gys_info', $gys_info);
    $smarty->assign('frozen_money', price_format($frozen_money,false));
    $row['add_time'] = date('Y-m-d H:i:s', $row['add_time']);
    $row['amount'] = price_format($row['amount'], false);
    $smarty->assign('row_info', $row);
    $processed = $row['is_paid'] == 0 ? '':'processed';
    $smarty->assign('processed', $processed);
    $smarty->display('gys_out_account_check.htm');
}

if($_REQUEST['act'] == 'action'){
    admin_priv('gys_out_account_check');
    if(isset($_REQUEST['account_error'])){
        sys_msg('供应商账户异常,请通知供应商管理员检查账号');
    }
    $is_paid = intval($_REQUEST['is_paid']);
    $admin_note = htmlspecialchars($_REQUEST['admin_note']);
    $id = intval($_REQUEST['id']);
    if(!$is_paid && !$admin_note){
        sys_msg('请填写说明');
    }

    $out_money_info = $GLOBALS['db']->getRow("SELECT gys_id,amount,is_paid FROM ".$GLOBALS['ecs']->table('gys_account')." WHERE id='$id'");
    if($out_money_info['is_paid']!=0){
        sys_msg('已付款或已取消,不允许再次操作');
    }
    if($is_paid < 1){
        $sql_update_out_account = "UPDATE ".$GLOBALS['ecs']->table('gys_account')." SET is_paid='$is_paid',admin_note='$admin_note',update_time='".time()."' WHERE id='$id'";
        $res1 = $GLOBALS['db']->query($sql_update_out_account);
        $links[0] = array(
            'text'=>'返回列表',
            'href'=>'gys_out_account.php?act=out_list'
        );
        sys_msg('操作成功',0,$links);
    } else {

//        $out_money_info = $GLOBALS['db']->getRow("SELECT gys_id,amount FROM ".$GLOBALS['ecs']->table('gys_account')." WHERE id='$id'");
        if(intval($out_money_info['amount']*100)<1000){
            sys_msg('代付金额最少10元');
        }
        if(intval($out_money_info['amount']*100)>5000000){
            sys_msg('代付金额不能超过50000元');
        }
        $GLOBALS['db']->query("SET AUTOCOMMIT=0");

//    $agency_id = $GLOBALS['db']->getOne("SELECT gys_id FROM ".$GLOBALS['ecs']->table('gys_account')." WHERE id='$id'");
        $gys_account = getGysAccountByAcencyId($out_money_info['gys_id']);

        if($gys_account == '-999' || intval(100*$gys_account) < intval(100*$out_money_info['amount'])){
            $GLOBALS['db']->query('ROLLBACK');
            $links[0] = array(
                'text'=>'返回列表',
                'href'=>'gys_out_account.php?act=out_list'
            );
            sys_msg('操作失败，供应商账号异常, 请联系管理员'.$gys_account,0,$links);
        }else{
            $gys_info = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('agency')." WHERE agency_id='$out_money_info[gys_id]'");

            $sql_update_out_account = "UPDATE ".$GLOBALS['ecs']->table('gys_account')." SET is_paid='$is_paid',admin_note='$admin_note',update_time='".time()."' WHERE id='$id'";
            $res1 = $GLOBALS['db']->query($sql_update_out_account);

            $gys_account = (intval(100*$gys_account) - intval(100*$out_money_info['amount']))/100;
            $sql_update_gys_total_account = "UPDATE ".$GLOBALS['ecs']->table('admin_user')." SET gys_account='$gys_account' WHERE agency_id='".$out_money_info['gys_id']."'";
            $res2 = $GLOBALS['db']->query($sql_update_gys_total_account);

            if($res1 && $res2){
                //首信易代付
                require_once ROOT_PATH . 'includes/sxy.php';
                $sxy = new sxy();
                $pc_sn = $sxy->v_mid.'-'.date('Ymd',time()).'-'.str_pad($id, 10, '0', STR_PAD_LEFT);

//            $df_data = "1|1.00|10776-20170928-0000000001$6210812450006318715|张晓欢|中国建设银行|河南省|洛阳市|1.00|20170925ceshi|105100000017";
                $df_data = "1|".$out_money_info['amount']."|".$pc_sn."$".$gys_info['agency_account']."|".$gys_info['agency_account_name']."|".$gys_info['agency_bank_name']."|".$gys_info['agency_bank_province']."|".$gys_info['agency_bank_city']."|".$out_money_info['amount']."|".str_pad($id, 10, '0', STR_PAD_LEFT)."|".$gys_info['agency_bank_code'];
                $daifu_res = $sxy->sxy_daifu($df_data);
                if($daifu_res['message']['status']==0){
                    //代付成功
                    $daifu_data = array(
                        'amount'=>$out_money_info['amount'],
                        'sn'=>$pc_sn,
                        'card_no'=>$gys_info['agency_account'],
                        'card_user'=>$gys_info['agency_account_name'],
                        'bank'=>$gys_info['agency_bank_name'],
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
                            'href'=>'gys_out_account.php?act=out_list'
                        );
                        sys_msg('操作成功',0,$links);
                    }else{
                        $GLOBALS['db']->query('ROLLBACK');
                        $links[0] = array(
                            'text'=>'返回列表',
                            'href'=>'gys_out_account.php?act=out_list'
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
                    'href'=>'gys_out_account.php?act=out_list'
                );
                sys_msg('操作失败，请联系管理员',0,$links);
            }
        }
        $GLOBALS['db']->query("SET AUTOCOMMIT=1");
    }
}

function getGysAccountByAcencyId($agency_id){
    $rowNum = $GLOBALS['db']->getOne("SELECT COUNT(user_id) FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE agency_id='$agency_id'");
    if($rowNum==1){
        return $GLOBALS['db']->getOne("SELECT gys_account FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE agency_id='$agency_id'");
    }else{
        return '-999';
    }
}
function get_gys_id()
{
    return $GLOBALS['db']->getOne("SELECT agency_id FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE user_id=".$_SESSION['admin_id']);
}
/**
 *
 *
 * @access  public
 * @param
 *
 * @return void
 */
function out_account_list()
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

        if(isGys()){
            $sql_gys = "SELECT ag.agency_id FROM ".$GLOBALS['ecs']->table('admin_user')." AS ad LEFT JOIN ".
                $GLOBALS['ecs']->table('agency')." AS ag ON ad.agency_id=ag.agency_id ".
                "WHERE ad.user_id='".$_SESSION['admin_id']."'";
            $gys_id = $GLOBALS['db']->getOne($sql_gys);
            if(!$gys_id){
                return false;
            }
            $filter['gys_id'] = $gys_id;
        }

        $where = " WHERE 1 ";
//        if ($filter['keywords'])
//        {
//            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
//        }
        /*　时间过滤　*/
        if (!empty($filter['start_date']) && !empty($filter['end_date']))
        {
            $where .= "AND ga.add_time >= " . $filter['start_date']. " AND ga.add_time < '" . $filter['end_date'] . "'";
        }

        if(!empty($filter['gys_id'])){
            $where .= " AND ga.gys_id=".$filter['gys_id'];
        }

        $sql = "SELECT COUNT('ga.id') FROM " .$GLOBALS['ecs']->table('gys_account')." AS ga".
            $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询数据 */
        $sql  = 'SELECT ga.*,a.agency_name FROM ' .
            $GLOBALS['ecs']->table('gys_account')." AS ga LEFT JOIN ".
            $GLOBALS['ecs']->table('agency')." AS a ON ga.gys_id=a.agency_id ".
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