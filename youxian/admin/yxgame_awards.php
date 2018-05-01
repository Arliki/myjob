<?php
//游戏操作设置
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
admin_priv('yx_game_manage');
$gid = intval($_REQUEST['gid']);
if(!$gid){
    sys_msg('缺少游戏id参数', 1);
}
$game = $db->getRow("SELECT * FROM ".$ecs->table('games')." WHERE id='$gid'");
if(!$game){
    sys_msg('没找到游戏,请重新安装');
}
$smarty->assign('gid',$gid);
$smarty->assign('game_base_number', $game['base_number']);
/*------------------------------------------------------ */
//-- 游戏奖项列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $list = $db->getAll("SELECT * FROM ".$ecs->table('game_awards')." WHERE game_id='$gid' ORDER BY rate ASC");
    foreach ($list as $k=>$v){
        if($v['awards_type']==1){
            $goods_name = $db->getOne("SELECT goods_name FROM ".$ecs->table('goods')." WHERE goods_id='".$v['awards_value']."'");
            $v['awards_value_text'] = $goods_name;
        }else{
            $v['awards_value_text'] = $v['awards_value'].$GLOBALS['_CFG']['integral_name'];
        }
        $list[$k] = $v;
    }
    $smarty->assign('awards_list', $list);
    $smarty->assign('ur_here', '游戏奖项设置 -- '.$game['title']);
    $smarty->assign('action_link', array('href' => 'yxgames.php?act=list', 'text'=>'返回游戏列表'));
    $smarty->assign('action_link2', array('href' => 'yxgame_awards.php?act=add&gid='.$gid, 'text'=>'添加奖项'));
    assign_query_info();
    $smarty->display('game_awards_list.htm');
}
/*------------------------------------------------------ */
//-- 添加、编辑游戏奖项
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
{
    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'add';
    $smarty->assign('form_action', $is_add ? 'insert' : 'update');

    /* 初始化、取得游戲操作信息 */
    if ($is_add)
    {
        $game_action = array(

        );
    }
    else
    {
        if (empty($_GET['id']))
        {
            sys_msg('invalid param');
        }
        $id = $_GET['id'];
        $sql = "SELECT * FROM " . $ecs->table('game_awards') . " WHERE id = '$id'";
        $game_award = $db->getRow($sql);
        if (empty($game_award))
        {
            sys_msg('游戏奖项不存在');
        }
    }
    $smarty->assign('game_award',$game_award);
    /* 显示模板 */
    if ($is_add)
    {
        $smarty->assign('ur_here', '游戏奖项设置 -- '.$game['title'].' -- 添加奖项');
    }
    else
    {
        $smarty->assign('ur_here',  '游戏奖项设置 -- '.$game['title'].' -- 编辑奖项');
    }
    $href = 'yxgame_awards.php?act=list&gid='.$gid;

    $smarty->assign('action_link', array('href' => $href, 'text' => '返回列表'));
    assign_query_info();
    $smarty->display('game_awards_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑游戏操作
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    include_once(ROOT_PATH . 'includes/cls_image.php');
    $image = new cls_image($_CFG['bgcolor']);
    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'insert';

    if(!isset($_POST['title']) || trim($_POST['title'])==''){
        sys_msg('请填写奖项名称');
    }
    if(!is_numeric($_POST['rate'])){
        sys_msg('请填写中奖概率,为整数');
    }
    $awards_type = intval($_POST['awards_type']);
    if($awards_type==0){
        $awards_value=intval($_POST['awards_value']);
    }else{
        $awards_value=intval($_POST['goods_id'])?intval($_POST['goods_id']):intval($_POST['awards_value']);
    }

    $award_img = $_POST['award_img_url'];

    if ((isset($_FILES['img']['error']) && $_FILES['img']['error'] == 0) || (!isset($_FILES['img']['error']) && isset($_FILES['img']['tmp_name'] ) && $_FILES['img']['tmp_name'] != 'none'))
    {
        $award_img = $image->upload_image($_FILES['img'], 'afficheimg');
    }

    /* 提交值 */
    $data = array(
        'id'     => intval($_POST['id']),
        'game_id'   => $gid,
        'title'   => $_POST['title'],
        'rate' => intval($_POST['rate']),
        'awards_type' => $awards_type,
        'awards_value' => $awards_value,
        'img'=>$award_img,
        'limit_num'=>intval($_POST['limit_num'])
    );

    /* 保存操作信息 */
    if ($is_add)
    {
        $db->autoExecute($ecs->table('game_awards'), $data, 'INSERT');
    }
    else
    {
        $game_award_id = intval($_POST['id']);
        $db->autoExecute($ecs->table('game_awards'), $data, 'UPDATE', "id = '$game_award_id'");
    }

    /* 记日志 */
    if ($is_add)
    {
        admin_log($game['title'], 'add', '设置游戏奖项');
    }
    else
    {
        admin_log($game['title'], 'edit', '设置游戏奖项');
    }

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    $links = array(
        array('href' => 'yxgame_awards.php?act=list&gid='.$gid, 'text' => '奖品列表')
    );
    sys_msg('操作成功', 0, $links);
}
/*------------------------------------------------------ */
//-- 奖项设置
/*------------------------------------------------------ */
elseif($_REQUEST['act'] == 'remove')
{
    $id = intval($_GET['id']);
    if(!$id){
        sys_msg('缺少参数', 1);
    }
    $res = $db->query("DELETE FROM " .$ecs->table('game_awards'). " WHERE id = '$id' AND game_id='$gid' LIMIT 1");
    if($res){
        $lnk[] = array('text' => $_LANG['go_back'], 'href' => 'yxgame_awards.php?act=list&gid='.$gid);
        sys_msg('操作成功', 0, $lnk);
    }else{
        sys_msg('操作失败', 1);
    }
}

?>