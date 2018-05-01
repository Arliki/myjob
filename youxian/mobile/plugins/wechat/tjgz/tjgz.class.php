<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：recommend.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：微信通-查找不到对应内容时推荐一个商品
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (! defined('IN_ECTOUCH')) {
    die('Deny Access');
}

/**
 * 推荐关注
 *
 * @author wanglu
 *        
 */
class tjgz extends PluginWechatController{
    // 插件名称
    protected $plugin_name = '';
    // 配置
    protected $cfg = array();

    /**
     * 构造方法
     *
     * @param unknown $cfg            
     */
    public function __construct($cfg = array())
    {
        $name = basename(__FILE__, '.class.php');
        $this->plugin_name = $name;
        $this->cfg = $cfg;
    }

    /**
     * 安装
     */
    public function install()
    {
        $this->plugin_display('install', $this->cfg);
    }

    /**
     * 获取数据
     */
    public function show($fromusername, $info)
    {
        $articles = array();

//        $goods = model('Base')->model->table('goods')
//            ->field('goods_id, goods_name')
//            ->where('is_best = 1 and is_on_sale = 1 and is_delete = 0')
//            ->order('RAND()')->find();
//        $goods_url = __HOST__ . url('goods/index', array('id' => $goods['goods_id']));

        $articles['type'] = 'text';
        $fromUser = model('Base')->model->table('wechat_user')->field('nickname,subscribe,ect_uid')->where("openid='$fromusername'")->find();
        $parent = model('Base')->model->table('users')->field('parent_id')->where("user_id='".$fromUser['ect_uid']."'")->find();
        if($parent['parent_id']){
            $parentWx = model('Base')->model->table('wechat_user')->field('nickname')->where("ect_uid='".$parent['parent_id']."'")->find();
            if($parentWx){
                $articles['content'] = '亲爱的 '.$fromUser['nickname'].', 感谢您的关注，您的推荐人是 '.$parentWx['nickname'].'!';
            }else{
                $articles['content'] = '亲爱的 '.$fromUser['nickname'].', 感谢您的关注!';
            }
        }else{
            $articles['content'] = '亲爱的 '.$fromUser['nickname'].', 感谢您的关注!';
        }
        return $articles;
    }
    public function give_point($fromusername, $info){

    }
    /**
     * 行为操作
     */
    public function action()
    {}
}
