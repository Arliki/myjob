<?php

/**
 * 充值卡
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
//include_once(ROOT_PATH . 'includes/cls_image.php');
//$image = new cls_image($_CFG['bgcolor']);
$face_value = array(
    array('code'=>'A','text'=>'￥5000元','value'=>5000),
    array('code'=>'B','text'=>'￥2000元','value'=>2000),
    array('code'=>'C','text'=>'￥1000元','value'=>1000),
    array('code'=>'D','text'=>'￥500元','value'=>500),
    array('code'=>'E','text'=>'￥200元','value'=>200),
    array('code'=>'Z','text'=>'￥5元','value'=>5)
);
/*------------------------------------------------------ */
//-- 充值卡列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('recharge_card_manage');
    $smarty->assign('ur_here',      '充值卡列表');
    $smarty->assign('action_link',  array('text' => '生成卡密', 'href' => 'recharge_card.php?act=add'));
    $smarty->assign('full_page',    1);

    $card_list = get_cardlist();
    foreach($card_list['cards'] as $k=>$v){
        $v['price'] = price_format($v['price']);
        $v['addtime'] = date('Y/m/d H:i:s', $v['addtime']);
        $v['usetime'] = $v['usetime'] > 0 ? date('Y/m/d H:i:s', $v['usetime']) : '未使用';
        $card_list['cards'][$k] = $v;
    }

    $smarty->assign('card_list',  $card_list['cards']);
    $smarty->assign('filter',       $card_list['filter']);
    $smarty->assign('record_count', $card_list['record_count']);
    $smarty->assign('page_count',   $card_list['page_count']);
    $smarty->assign('face_value', $face_value);
    /* 排序标记 */
    $sort_flag  = sort_flag($card_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    $can_export_recharge_card = admin_priv('export_recharge_cards','',false);
    $smarty->assign('can_export_recharge_card', $can_export_recharge_card);

    assign_query_info();
    $smarty->display('recharge_card_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('recharge_card_manage');
    $card_list = get_cardlist();
    foreach($card_list['cards'] as $k=>$v){
        $v['price'] = price_format($v['price']);
        $v['addtime'] = date('Y/m/d H:i:s', $v['addtime']);
        $v['usetime'] = $v['usetime'] > 0 ? date('Y/m/d H:i:s', $v['usetime']) : '未使用';
        $card_list['cards'][$k] = $v;
    }
    $smarty->assign('card_list',  $card_list['cards']);
    $smarty->assign('filter',       $card_list['filter']);
    $smarty->assign('record_count', $card_list['record_count']);
    $smarty->assign('page_count',   $card_list['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($card_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('recharge_card_list.htm'), '',
        array('filter' => $card_list['filter'], 'page_count' => $card_list['page_count']));
}

/*------------------------------------------------------ */
//-- 生成卡密
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 检查权限 */
    admin_priv('create_recharge_card');
    $smarty->assign('form_action', 'insert');
    /* 显示模板 */
    $smarty->assign('ur_here', '生成充值卡');
    $href = 'recharge_card.php?act=list';
    $smarty->assign('action_link', array('href' => $href, 'text' => '返回列表'));
    $smarty->assign('face_value', $face_value);
    assign_query_info();
    $smarty->display('create_recharge_card.htm');
}

/*------------------------------------------------------ */
//-- 提交添加
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert')
{
    /* 检查权限 */
    admin_priv('create_recharge_card');
    $number = intval($_POST['number']);
    $price = intval($_POST['price']);
    $canuse = intval($_POST['canuse']);
    $multiuse = intval($_POST['multiuse']);
    $onlyNum = intval($_POST['onlyNum']);
    if(!$number || !$price){
        echo json_encode(array('status'=>0,'info'=>'请选择面值,输入数量'));die;
    }
    $price_code = '';
    foreach ($face_value as $v){
        if($v['value'] == $price){
            $price_code = $v['code'];
            break;
        }
    }

    if(!$price_code){
        echo json_encode(array('status'=>0,'info'=>'面值参数有误'));die;
    }
    $cards = array();
    $_SESSION['recharge_card_fail_time'] = 0;
    for($i=0;$i<$number;$i++){
        $card_num = generate_recharge_card_num($price_code);
        if(!$card_num){
            echo json_encode(array('status'=>0,'info'=>'生成卡号失败'));die;
//            sys_msg('生成卡号失败');break;
        }
        if(is_array($card_num) && $card_num['error']==1){
            echo json_encode(array('status'=>0,'info'=>'本次生成卡数量'.$i.';'.$card_num['info']));die;
//            sys_msg('本次生成卡数量'.$i.';'.$card_num['info']);break;
        }
        $card = array(
            'card_num' => $card_num,
            'password' => generate_rand_str(10,(bool)$onlyNum),
            'price' => $price,
            'addtime' => time(),
            'canuse' => $canuse,
            'watchout' => 0,
            'multiuse' => $multiuse
        );
        $db->autoExecute($ecs->table('recharge_card'), $card, 'INSERT');
    }

    /* 记日志 */
    admin_log('生成数量:'.$i+1, 'add', '充值卡');
    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */

    $links = array(
        array('href' => 'recharge_card.php?act=list', 'text' => '充值卡列表')
    );
    echo json_encode(array('status'=>1,'info'=>'操作成功','url'=>'recharge_card.php?act=list'));die;

//    sys_msg('操作成功', 0, $links);
}
elseif ($_REQUEST['act'] == 'change_state'){
    admin_priv('recharge_card_manage');
    $id = intval($_REQUEST['id']);
    if(!$id){
        sys_msg('缺少参数');
    }
    $canuse = intval($_REQUEST['canuse']);
    $links[0]['text'] = '返回列表';
    $links[0]['href'] = 'recharge_card.php?act=list';
    if($db->query("UPDATE ".$ecs->table('recharge_card')." SET canuse=".$canuse." WHERE id='$id'")){
        sys_msg('操作成功',0,$links);
    }else{
        sys_msg('操作失败',0,$links);
    }
}
elseif ($_REQUEST['act'] == 'export_card'){
    admin_priv('export_recharge_cards');
    include_once('includes/cls_phpzip.php');
    $zip = new PHPZip;
    $res = get_cardlist();
    $res = $res['cards'];
    /* csv文件数组 */
    $cards = array();
    $cards['card_num'] = '""';
    $cards['password'] = '""';
    $theader = array('编号','密码');
    $content = '"' . implode('","', $theader) . "\"\n";
    foreach ($res as $v){
        $cards['card_num'] = $v['card_num'];
        $cards['password'] = $v['password'];
        $content .= implode(",", $cards) . "\n";
    }
    $charset = 'gb2312';
    $export_file_name = date('Ymd',time()).'卡密列表';
    $zip->add_file(ecs_iconv(EC_CHARSET, $charset, $content), ecs_iconv(EC_CHARSET, $charset, $export_file_name).'.csv');

    header("Content-Disposition: attachment; filename=".ecs_iconv(EC_CHARSET, $charset, $export_file_name).".zip");
    header("Content-Type: application/unknown;charset=gb2312");
    die($zip->file());
}
/**
 * 取得优惠券列表
 * @return  array
 */
function get_cardlist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 初始化分页参数 */
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['card_num'] = isset($_REQUEST['card_num']) ? trim($_REQUEST['card_num']) : '';
        $filter['price'] = isset($_REQUEST['price']) ? intval($_REQUEST['price']) : 0;
        $filter['canuse'] = isset($_REQUEST['canuse']) ? intval($_REQUEST['canuse']) : -1;
        $filter['usestatus'] = isset($_REQUEST['usestatus']) ? intval($_REQUEST['usestatus']) : -1;
        $filter['warning'] = isset($_REQUEST['warning']) ? intval($_REQUEST['warning']) : -1;

        $where = ' WHERE 1';
        if($filter['card_num']){
            $where .= " AND card_num LIKE '%".$filter['card_num']."%'";
        }
        if($filter['price']){
            $where .= " AND price=".$filter['price'];
        }
        if(isset($filter['canuse']) && $filter['canuse'] != -1){
            $where .= " AND canuse=".$filter['canuse'];
        }
        if(isset($filter['usestatus']) && $filter['usestatus'] != -1){
            if($filter['usestatus'] == 0){
                $where .= " AND usetime=0";
            }
            if($filter['usestatus'] == 1){
                $where .= " AND usetime>0";
            }
        }

        if(isset($filter['warning']) && $filter['warning'] != -1){
            $where .= " AND watchout=".$filter['warning'];
        }

        /* 查询记录总数，计算分页数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('recharge_card'). $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('recharge_card') .$where. " ORDER BY $filter[sort_by] $filter[sort_order]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
//    var_dump($filter);
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $arr[] = $rows;
    }

    return array('cards' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

/**
 * 生成充值卡卡号
 */
function generate_recharge_card_num($code) {
    static $fail_time = 0;
    if($fail_time>5000){
        return array('error'=>1,'info'=>'可用号段内卡号即将用完或已用完,请扩展卡号长度');
    }
    $card_num = $code.'YX';
//    $rand_num = str_pad(mt_rand(1,999), 3, 0, STR_PAD_LEFT).str_pad(rand(1,999), 3, 0, STR_PAD_LEFT);
    $rand_num = str_pad(mt_rand(1,999999), 6, 0, STR_PAD_LEFT);
    $card_num .= $rand_num;
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('recharge_card')." WHERE card_num='$card_num'";
    $is_exist = $GLOBALS['db']->getOne($sql);
    if($is_exist){
        $fail_time++;
        return generate_recharge_card_num($code);
    }else{
        return $card_num;
    }

}

function generate_rand_str($num, $onlyNum = false){
    $seeds = $onlyNum===true?'0123456789':'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz0123456789';
    $str = '';
    for($i=0;$i<$num;$i++){
        $str .= $seeds[mt_rand(0, strlen($seeds)-1)];
    }
    if($onlyNum && $str[0]=='0'){
        $str = '1'.substr($str,1);
    }
    return $str;
}
?>