<?php
/**
 * 刮刮卡
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = (isset($modules)) ? count($modules) : 0;
    $modules[$i]['name']    = 'guaguaka';
    $modules[$i]['title']    = '优鲜刮刮卡';
    $modules[$i]['version'] = 'v1.0';
    $modules[$i]['desc']    = '优鲜刮刮卡';
    $modules[$i]['author']  = 'ActionNone';
    $modules[$i]['sort_order']  = '0';
    $modules[$i]['base_number']  = '1000';
    return;
}

class guaguaka
{
    /*------------------------------------------------------ */
    //-- PUBLIC ATTRIBUTEs
    /*------------------------------------------------------ */

    /**
     * 配置信息
     */
    var $configure;

    /**
     * 构造函数
     *
     * @param: $configure[array]    配送方式的参数的数组
     *
     * @return null
     */
    function __construct($cfg=array())
    {
        foreach ($cfg AS $key=>$val)
        {
            $this->configure[$val['name']] = $val['value'];
        }
    }
}

?>
