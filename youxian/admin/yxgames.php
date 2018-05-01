<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($ecs->table('games'), $db, 'id', 'name');
/*------------------------------------------------------ */
//-- 游戏列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('yx_game_manage');
    $modules = read_modules('../includes/modules/games');

    for ($i = 0; $i < count($modules); $i++)
    {
        /* 检查该插件是否已经安装 */
        $sql = "SELECT * FROM " .$ecs->table('games'). " WHERE name='" .$modules[$i]['name']. "' ORDER BY sort_order";
        $row = $db->GetRow($sql);
        if ($row)
        {
            /* 插件已经安装了，获得名称以及描述 */
            $modules[$i]['id']      = $row['id'];
            $modules[$i]['name']    = $row['name'];
            $modules[$i]['title']    = $row['title'];
            $modules[$i]['desc']  = $row['desc'];
            $modules[$i]['disabled']     = $row['disabled'];
            $modules[$i]['version'] = $row['version'];
            $modules[$i]['author'] = $row['author'];
            $modules[$i]['sort_order'] = $row['sort_order'];
            $modules[$i]['base_number'] = $row['base_number'];
            $modules[$i]['install'] = 1;
        }
        else
        {
            $modules[$i]['install'] = 0;
        }
    }

    $smarty->assign('ur_here', '优鲜游戏');
    $smarty->assign('modules', $modules);
    assign_query_info();
    $smarty->display('game_list.htm');
}
/*------------------------------------------------------ */
//-- 安装
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'install')
{
    admin_priv('yx_game_manage');
    $set_modules = true;
    include_once(ROOT_PATH . 'includes/modules/games/' . $_GET['code'] . '.php');
    /* 检查该配送方式是否已经安装 */
    $sql = "SELECT id FROM " .$ecs->table('games'). " WHERE name = '$_GET[code]'";
    $id = $db->GetOne($sql);
    if (!$id)
    {
        /* 该配送方式没有安装过, 将该配送方式的信息添加到数据库 */
        $sql = "INSERT INTO " . $ecs->table('games') . " (`name`, `title`, `version`, `desc`, `author`) VALUES (" .
            "'" . addslashes($modules[0]['name']). "', '" . addslashes($modules[0]['title']) . "', '" .
            addslashes($modules[0]['version']) . "', '" . $modules[0]['desc'] . "', '" . addslashes($modules[0]['author']) . "')";
        $db->query($sql);
        $id = $db->insert_Id();
    }

    /* 记录管理员操作 */
    admin_log(addslashes($modules[0]['name']), 'install', '优鲜游戏');

    /* 提示信息 */
    $lnk[] = array('text' => $_LANG['go_back'], 'href' => 'yxgames.php?act=list');
    sys_msg(sprintf($_LANG['install_succeess'], $modules[0]['name']), 0, $lnk);
}
/*------------------------------------------------------ */
//-- 玩家日志
/*------------------------------------------------------ */
elseif($_REQUEST['act'] == 'user_log')
{
    check_authz_json('yx_game_manage');
    $id = intval($_GET['id']);
    if(!$id){
        sys_msg('缺少参数', 1);
    }
    $disabled = $db->getRow("SELECT disabled FROM ".$ecs->table('games')." WHERE id='$id'");
    if($disabled==false){
        sys_msg('没找到游戏数据', 1);
    }
    $disabled = $disabled['disabled']==1?0:1;
    $res = $db->query("UPDATE " .$ecs->table('games'). " SET disabled = '$disabled' WHERE id = '$id' LIMIT 1");
    if($res){
        $lnk[] = array('text' => $_LANG['go_back'], 'href' => 'yxgames.php?act=list');
        sys_msg('操作成功', 0, $lnk);
    }else{
        sys_msg('操作失败', 1);
    }
}
/*------------------------------------------------------ */
//-- 停用/启用
/*------------------------------------------------------ */
elseif($_REQUEST['act'] == 'disable_game')
{
    check_authz_json('yx_game_manage');
    $id = intval($_GET['id']);
    if(!$id){
        sys_msg('缺少参数', 1);
    }
    $disabled = $db->getRow("SELECT disabled FROM ".$ecs->table('games')." WHERE id='$id'");
    if($disabled==false){
        sys_msg('没找到游戏数据', 1);
    }
    $disabled = $disabled['disabled']==1?0:1;
    $res = $db->query("UPDATE " .$ecs->table('games'). " SET disabled = '$disabled' WHERE id = '$id' LIMIT 1");
    if($res){
        $lnk[] = array('text' => $_LANG['go_back'], 'href' => 'yxgames.php?act=list');
        sys_msg('操作成功', 0, $lnk);
    }else{
        sys_msg('操作失败', 1);
    }
}
/*------------------------------------------------------ */
//-- 编辑排序
/*------------------------------------------------------ */
elseif($_REQUEST['act'] == 'edit_order'){
    /* 检查权限 */
    admin_priv('yx_game_manage');
    /* 取得参数 */
    $id  = json_str_iconv(trim($_POST['id']));
    $val = json_str_iconv(trim($_POST['val']));
    $exc->edit("sort_order = '$val'", $id);
    make_json_result(stripcslashes($val));
}
/*------------------------------------------------------ */
//-- 编辑中奖概率基数
/*------------------------------------------------------ */
elseif($_REQUEST['act'] == 'edit_base_number'){
    /* 检查权限 */
    admin_priv('yx_game_manage');
    /* 取得参数 */
    $id  = json_str_iconv(trim($_POST['id']));
    $val = json_str_iconv(trim($_POST['val']));
    $exc->edit("base_number = '$val'", $id);
    make_json_result(stripcslashes($val));
}
?>