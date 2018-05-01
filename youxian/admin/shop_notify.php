<?php
/*
* File: shop_notify.php
* Author: Arliki
* Date: 2018-02-05 10:44
*/
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
$act=$_REQUEST['act'];
if ($act=='list'){
    $list=$db->getAll("select * from ecs_shop_notify");
    $new_list=array();
    for($i=0;$i<count($list);$i++){
        $new_list[$i]['cid']=$list[$i]['cid'];
        $new_list[$i]['name']=$list[$i]['name'];
        $new_list[$i]['content']=hideStr($list[$i]['content'],3,-1,1);
        $new_list[$i]['ctime']=$list[$i]['ctime'];
    }
    $con=get_notifylist();
    $smarty->assign('all_list',$con['notify']);
    $smarty->assign('full_page',1);
    $smarty->assign('filter',$con['filter']);
    $smarty->assign('record_count', $con['record_count']);
    $smarty->assign('page_count',   $con['page_count']);

    $smarty->assign('ur_here',$_LANG['notify_list']);
    $smarty->assign('action_link',array('text' => $_LANG['notify_new'], 'href'=>'shop_notify.php?act=insert&' . list_link_postfix()));
    $smarty->assign('form_action','update');
    assign_query_info();
    $smarty->display('shop_notify.htm');

}elseif ($act == 'query')
{
    $con = get_notifylist();
    $smarty->assign('all_list',  $con['notify']);
    $smarty->assign('filter',       $con['filter']);
    $smarty->assign('record_count', $con['record_count']);
    $smarty->assign('page_count',   $con['page_count']);

    $sort_flag  = sort_flag($con['filter']);
    make_json_result($smarty->fetch('shop_notify.htm'), '',
        array('filter' => $con['filter'], 'page_count' => $con['page_count']));
}elseif ($act=='edit'){
    admin_priv('shop_notify');
    $notify=get_notify_info($_REQUEST['nid']);
    $smarty->assign('notify',$notify);
    $smarty->assign('ur_here',$_LANG['notify_edit']);
    $smarty->assign('action_link',array('text' => $_LANG['notify_list'], 'href'=>'shop_notify.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action','save_notify');
    assign_query_info();
    $smarty->display('shop_noticy_info.htm');
}elseif ($act=='save_notify'){
    $name=$_POST['notify_name'];
    $content=$_POST['content'];
    $ctime=strtotime($_POST['ctime']);
    $n_link=$_POST['link'];
    $link_name=$_POST['link_name'];
    $nid=empty($_REQUEST['id'])?0:$_REQUEST['id'];
    notify_save($name,$content,$ctime,$n_link,$link_name,$nid);
    $link[] = array('text' => $_LANG['notify_list'], 'href'=>'shop_notify.php?act=list');
    $link[] = array('text' => $_LANG['notify_new'], 'href'=>'shop_notify.php?act=insert');
    sys_msg($_LANG['modify'],0,$link);
}elseif ($act=='insert'){
    admin_priv('shop_notify');
    $smarty->assign('ur_here',$_LANG['notify_edit']);
    $smarty->assign('action_link',array('text' => $_LANG['notify_list'], 'href'=>'shop_notify.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action','save_notify');
    assign_query_info();
    $smarty->display('shop_noticy_info.htm');
}elseif ($act=='view'){
    $notify=get_notify_info($_REQUEST['nid']);
    $smarty->assign('notify',$notify);
    $smarty->assign('ur_here',$_LANG['notify_view']);
    $smarty->assign('is_view',1);
    $smarty->assign('action_link',array('text' => $_LANG['notify_list'], 'href'=>'shop_notify.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action','save_notify');
    assign_query_info();
    $smarty->display('shop_noticy_info.htm');
}

function get_notifylist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 查询条件 */
        $filter['keywords']   = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $where = (!empty($filter['keywords'])) ? " AND name like '%". mysql_like_quote($filter['keywords']) ."%'" : '';

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('shop_notify') .
            " WHERE 1". $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获活动数据 */
        $sql = "SELECT nid,name,content,ctime,link,link_name".
            " FROM " . $GLOBALS['ecs']->table('shop_notify') .
            " WHERE 1". $where .
            " ORDER by nid desc LIMIT ". $filter['start'] .", " . $filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {
        $row[$key]['ctime']=date('Y-m-d H:i',$val['ctime']);
        $row[$key]['content']=hideStr($val['content'],30,-1,1);
    }

    $arr = array('notify' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

function hideStr($string, $begin=0, $len = 4, $type = 0, $glue = "@") {
    $old=$string;
    if (empty($string)){return false;}
    $array = array();
    if ($type == 0 || $type == 1 || $type == 4) {
        $strlen = $length = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "utf8");
            $string = mb_substr($string, 1, $strlen, "utf8");
            $strlen = mb_strlen($string);
        }
    }
    if ($len<0){
        $n=array();
        for ($i = 0; $i < $begin; $i++) {
            if (isset($array[$i]))
                $n[$i]=$array[$i];
        }
        $string = implode("", $n);
        if (mb_strlen($old)>$begin*3) {
            $string .= "...";
        }
        return $string;
    }
    if ($type == 0) {
        for ($i = $begin; $i < ($begin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = "*";
        }
        $string = implode("", $array);
    }else if ($type == 1) {
        for ($i = $begin; $i < ($begin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = ".";
        }
        $string = implode("", $array);
    }else if ($type == 2) {
        $array = explode($glue, $string);
        $array[0] = $this->hideStr($array[0], $begin, $len, 1);
        $string = implode($glue, $array);
    } else if ($type == 3) {
        $array = explode($glue, $string);
        $array[1] = $this->hideStr($array[1], $begin, $len, 0);
        $string = implode($glue, $array);
    } else if ($type == 4) {
        $left = $begin;
        $right = $len;
        $tem = array();
        for ($i = 0; $i < ($length - $right); $i++) {
            if (isset($array[$i]))
                $tem[] = $i >= $left ? "X" : $array[$i];
        }
        $array = array_chunk(array_reverse($array), $right);
        $array = array_reverse($array[0]);
        for ($i = 0; $i < $right; $i++) {
            $tem[] = $array[$i];
        }
        $string = implode("", $tem);
    }
    return $string;
}

function get_notify_info($id){
    global $db;
    $sql="select nid,name,content,ctime,link,link_name from ecs_shop_notify where nid='$id'";
    $list=$db->getRow($sql);
    $list['ctime']=date('Y-m-d H:i',$list['ctime']);
    return $list;
}

function notify_save($name,$content,$ctime,$link,$link_name,$nid=0){
    global $db;
    if (strlen($link)==1){
        $link=(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://m.cnxunluo.cn/" : "http://m.cnxunluo.cn/mobile/index.php?m=default&c=coupon&a=coupon_list";
    }elseif (strlen($link)==0){
        $link=null;
    }
    if ($nid>0){
        $sql="update ecs_shop_notify set name='$name', content='$content', ctime='$ctime',link='$link',link_name='$link_name' where nid='$nid'";
        $db->query($sql);
    }else{
        $sql="insert into ecs_shop_notify (name, content, ctime,link,link_name) VALUES ('$name','$content','$ctime','$link','$link_name')";
        $db->query($sql);
    }
}