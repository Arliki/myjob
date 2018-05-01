<?php

/**
 * 代理商
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$dls_id = intval($_REQUEST['dls_id']);
if(!$dls_id){
    sys_msg('缺少参数');
}
admin_priv('dls_jiesuan');
$_REQUEST['act'] = empty($_REQUEST['act']) ? 'list':trim($_REQUEST['act']);
$dls_info = $db->getRow("SELECT * FROM ".$ecs->table('dls')." WHERE dls_id='$dls_id'");

if(!$dls_info){
    sys_msg('没找到代理商信息');
}
/*------------------------------------------------------ */
//-- 代理商结算列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('ur_here',      '代理商 '.$dls_info['username'].' 结算列表');
    $smarty->assign('action_link',  array('text' => '返回代理商列表', 'href' => 'dls_manage.php?act=list&dls_id='.$dls_id));
    $smarty->assign('action_link2',  array('text' => '添加结算', 'href' => 'dls_jiesuan.php?act=add&dls_id='.$dls_id));
    $smarty->assign('full_page',    1);

    $jiesuan_list = $db->getAll("SELECT * FROM ".$ecs->table('dls_jiesuan')." WHERE dls_id='$dls_id'");
    foreach ($jiesuan_list as $k=>$v){
        $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        $v['status'] = $v['status']==1?'成功':'失败';
        $jiesuan_list[$k] = $v;
    }
    $smarty->assign('jiesuan_list', $jiesuan_list);

    assign_query_info();
    $smarty->display('dls_jiesuan_list.htm');
}

/*------------------------------------------------------ */
//-- 添加代理商结算记录
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 是否添加 */
    $smarty->assign('form_action', 'insert');

    $smarty->assign('ur_here', '新增结算记录');

    $href = 'dls_jiesuan.php?act=list&dls_id='.$dls_id;

    $smarty->assign('action_link', array('href' => $href, 'text' => '返回列表'));
    $smarty->assign('dls_info', $dls_info);
    assign_query_info();
    $smarty->display('dls_jiesuan_add.htm');
}
elseif ($_REQUEST['act'] == 'insert'){
    if($_POST['start_day'] > $_POST['end_day']){
        sys_msg('开始日期不能大于结束日期');
    }
    if($_POST['end_day'] >= date('Y-m-d',time())){
        sys_msg('结束日期不能超过昨天');
    }
    $amount = trim($_POST['amount']);
    if(!$amount){
        sys_msg('缺少提成金额');
    }
    if($amount<10 || $amount>50000){
        sys_msg('单次结算金额为10元-50000元');
    }
    $data = array(
        'dls_id'=>$dls_id,
        'admin_id'=>$_SESSION['admin_id'],
        'amount'=>$amount,
        'add_time'=>time(),
        'admin_note'=>stripslashes($_POST['admin_note']),
        'start_day'=>$_POST['start_day'],
        'end_day'=>$_POST['end_day']
    );

    $db->autoExecute($ecs->table('dls_jiesuan'), $data, 'INSERT');
    $id = $db->insert_id();
    //首信易代付
    require_once ROOT_PATH . 'includes/sxy.php';
    $sxy = new sxy();
    $format_id = '1'.str_pad($id, 10, '0', STR_PAD_LEFT);
    $pc_sn = $sxy->v_mid.'-'.date('Ymd',time()).'-'.$format_id;

//            $df_data = "1|1.00|10776-20170928-0000000001$6210812450006318715|张晓欢|中国建设银行|河南省|洛阳市|1.00|20170925ceshi|105100000017";
    $df_data = "1|".$amount."|".$pc_sn."$".$dls_info['dls_bank_card']."|".$dls_info['dls_account_name']."|".$dls_info['dls_bank_name']."|".$dls_info['dls_bank_province']."|".$dls_info['dls_bank_city']."|".$amount."|".$format_id."|".$dls_info['dls_bank_code'];

    $daifu_res = $sxy->sxy_daifu($df_data);
    if($daifu_res['message']['status']==0){
        //代付成功
        $daifu_data = array(
            'amount'=>$amount,
            'sn'=>$pc_sn,
            'card_no'=>$dls_info['dls_bank_card'],
            'card_user'=>$dls_info['dls_account_name'],
            'bank'=>$dls_info['dls_bank_name'],
            'status'=>$daifu_res['message']['status'],
            'statusdesc'=>$daifu_res['message']['statusdesc'],
            'addtime'=>time(),
            'tid'=>$format_id
        );
        $db->query("SET AUTOCOMMIT=0");
        $daifu_log = $db->autoExecute($ecs->table('sxy_daifu'), $daifu_data, 'INSERT');
        $update_jiesuan_item = $db->autoExecute($ecs->table('dls_jiesuan'), array('status'=>1), 'UPDATE', "id='$id'");
        if($daifu_log && $update_jiesuan_item){
            $db->query("COMMIT");
            $links[0] = array(
                'text'=>'返回列表',
                'href'=>'dls_jiesuan.php?act=list'
            );
            sys_msg('操作成功',0,$links);
        }else{
            $db->query("ROLLBACK");
            $links[0] = array(
                'text'=>'返回列表',
                'href'=>'dls_jiesuan.php?act=list'
            );
            sys_msg('操作失败，请联系管理员',0,$links);
        }
        $db->query("SET AUTOCOMMIT=1");
    }else{
        //代付失败
        echo '代付失败,请联系管理员.错误代码：'.$daifu_res['message']['statusdesc'];
    }
    //首信易代付END
}
elseif ($_REQUEST['act'] == 'calc_money'){
    $start_day = $_REQUEST['start_day'];
    $end_day = $_REQUEST['end_day'];
    $start_time = strtotime($start_day);
    $end_time = strtotime($end_day.' + 1 day');
    if($start_time>=$end_time){
        make_json_error('开始时间必须小于结束时间');
    }
    $last_jiesuan_end_day = $db->getOne("SELECT end_day FROM ".$ecs->table('dls_jiesuan')." WHERE dls_id='$dls_id' AND status=1 ORDER BY end_day DESC LIMIT 1");
    if($last_jiesuan_end_day && strtotime($last_jiesuan_end_day.'+ 1 day')>$start_time){
        make_json_error('您选择的结算日期与上次结算时间段有交叉，上次已结算至'.$last_jiesuan_end_day);
    }
//    $sql = "SELECT SUM(goods_amount) FROM ".$ecs->table('order_info')." WHERE dls_id='$dls_id' AND shipping_time>='$start_time' AND shipping_time<'$end_time' AND pay_status=2";
    $sql = "SELECT SUM(goods_amount) FROM ".$ecs->table('order_info')." WHERE dls_id='$dls_id' AND pay_time>='$start_time' AND pay_time<'$end_time' AND pay_status=2 AND (shipping_status=1 OR shipping_status=2)";
    $total_sale = $db->getOne($sql);
    $total_sale = $total_sale ? $total_sale : 0;
    $money = calc_money_by_ratio($total_sale);
    if($money){
        $money['total_sale'] = $total_sale;
        $money['intro'] = "总销售额：".$total_sale."元; ".$money['str'];
    }
    make_json_result('','',$money);
}

function calc_money_by_ratio($money){
    $config = unserialize($GLOBALS['_CFG']['dls_ratio']);
    if(empty($config)){
        return false;
    }
    if($money==0){
        return array('str'=>'', 'total_ticheng'=>0.00);
    }
    $left = $money;
    $res = 0.00;
    $res_str = '总提成:';
    for($i=0;$i<count($config);$i++){
        if($i==count($config)-1){
            $res += $left*$config[$i]['step_ratio']/100;
            $res_str .= $left."x".$config[$i]['step_ratio']."%=".$res."元";
        }else{
            $step_money = $config[$i+1]['step_money']*10000-$config[$i]['step_money']*10000;
            if($left > $step_money){
                $res += $step_money*$config[$i]['step_ratio']/100;
                $left -= $step_money;
                $res_str .= $step_money."x".$config[$i]['step_ratio']."%+";
            }else{
                $res += $left*$config[$i]['step_ratio']/100;
                $res_str .= $left."x".$config[$i]['step_ratio']."%=".$res."元";
                break;
            }
        }
    }
    return array('str'=>$res_str, 'total_ticheng'=>round($res,2));
}

?>