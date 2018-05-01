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
/*------------------------------------------------------ */
//-- 游戏操作列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $list = $db->getAll("SELECT * FROM ".$ecs->table('game_actions')." WHERE game_id='$gid'");
    $smarty->assign('action_list', $list);
    $smarty->assign('ur_here', '游戏操作设置 -- '.$game['title']);
    $smarty->assign('action_link', array('href' => 'yxgames.php?act=list', 'text'=>'返回游戏列表'));
    $smarty->assign('action_link2', array('href' => 'yxgame_action.php?act=add&gid='.$gid, 'text'=>'添加操作'));
    assign_query_info();
    $smarty->display('game_action_list.htm');
}
/*------------------------------------------------------ */
//-- 添加、编辑游戏操作
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
        $sql = "SELECT * FROM " . $ecs->table('game_actions') . " WHERE id = '$id'";
        $game_action = $db->getRow($sql);
        if (empty($game_action))
        {
            sys_msg('游戏操作不存在');
        }
    }
    $smarty->assign('game_action',$game_action);
    /* 显示模板 */
    if ($is_add)
    {
        $smarty->assign('ur_here', '游戏操作设置 -- '.$game['title'].' -- 添加操作');
    }
    else
    {
        $smarty->assign('ur_here',  '游戏操作设置 -- '.$game['title'].' -- 编辑操作');
    }
    $href = 'yxgame_action.php?act=list&gid='.$gid;

    $smarty->assign('action_link', array('href' => $href, 'text' => '返回列表'));
    assign_query_info();
    $smarty->display('game_action_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑游戏操作
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    /* 是否添加 */
    $is_add = $_REQUEST['act'] == 'insert';

    if(!isset($_POST['title']) || trim($_POST['title'])==''){
        sys_msg('请填写操作名称');
    }
    if(!is_numeric($_POST['costs'])){
        sys_msg('请填写每次操作需要消耗的果豆数量');
    }
    if(!is_numeric($_POST['weight'])){
        sys_msg('增加中奖权重值必须为数字');
    }

    /* 提交值 */
    $data = array(
        'id'     => intval($_POST['id']),
        'game_id'   => $gid,
        'title'   => $_POST['title'],
        'costs' => $_POST['costs'],
        'weight' => $_POST['weight']
    );
    /* 保存操作信息 */
    if ($is_add)
    {
        $db->autoExecute($ecs->table('game_actions'), $data, 'INSERT');
        $game_action['id'] = $db->insert_id();
    }
    else
    {
        $game_action_id = intval($_POST['id']);
        $db->autoExecute($ecs->table('game_actions'), $data, 'UPDATE', "id = '$game_action_id'");
    }

    /* 记日志 */
    if ($is_add)
    {
        admin_log($game['title'], 'add', '设置游戏操作');
    }
    else
    {
        admin_log($game['title'], 'edit', '设置游戏操作');
    }

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    $links = array(
        array('href' => 'yxgame_action.php?act=list&gid='.$gid, 'text' => '操作列表')
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
    $res = $db->query("DELETE FROM " .$ecs->table('game_actions'). " WHERE id = '$id' AND game_id='$gid' LIMIT 1");
    if($res){
        $lnk[] = array('text' => $_LANG['go_back'], 'href' => 'yxgame_action.php?act=list&gid='.$gid);
        sys_msg('操作成功', 0, $lnk);
    }else{
        sys_msg('操作失败', 1);
    }
}

?>