<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：IndexController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTouch首页控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */
/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class IndexController extends CommonController {

    /**
     * 首页信息
     */
    public function index() {
        //检测优惠券、通知↓↓↓↓↓
        $cou=model('Notice')->check_coupon();
        $this->assign('notice_coupon',$cou);
        $shop_notify=model('Notice')->check_shop_notify();
        $this->assign('shop_notify',$shop_notify);
        //↑↑↑↑↑↑↑↑↑↑
        $this->assign('currNav', CONTROLLER_NAME);
        //导航图标
        $navs = M()->query("SELECT ad_name,ad_img_text,ad_text_color,ad_code,ad_link,start_time,end_time FROM ecs_ad WHERE position_id=264 ORDER BY ad_name ASC LIMIT 8");
        foreach ($navs as $k=>$v){
            $v['ad_code'] = (strpos($v['ad_code'], 'http://') === false && strpos($v['ad_code'], 'https://') === false) ?
                get_data_url($v['ad_code'], 'afficheimg') : $v['ad_code'];
            $navs[$k] = $v;
        }
        $this->assign('navs', $navs);
        $this->display('index.dwt');
    }

    public function ajax_data() {
        if (IS_AJAX) {
            $cat_goods = model('Index')->get_index_nav_cate_goods();
            $data = array(
                'cat_goods'=>$cat_goods,
            );
            echo json_encode($data);
            exit();
        } else {
            $this->redirect(url('index'));
        }
    }

    /**
     * ajax获取商品
     */
    public function ajax_goods() {
        if (IS_AJAX) {
            $type = I('get.type');
            $start = $_POST['last'];
            $limit = $_POST['amount'];
            $goods_list = model('Index')->goods_list($type, $limit, $start);
            $list = array();
            // 热卖商品
            if ($goods_list) {
                foreach ($goods_list as $key => $value) {
                    $value['iteration'] = $key + 1;
                    $this->assign('goods', $value);
                    $list [] = array(
                        'single_item' => ECTouch::view()->fetch('library/asynclist_index.lbi')
                    );
                }
            }
            echo json_encode($list);
            exit();
        } else {
            $this->redirect(url('index'));
        }
    }
    /**
     * 获取首页弹屏广告图
     */
    public function get_idx_tp_ad(){
        if(IS_AJAX){
            $tp_ad = M()->query("SELECT ad_code,ad_link,start_time,end_time FROM ecs_ad WHERE ad_id=36 LIMIT 1");
            $tp_ad = $tp_ad[0];
            if($tp_ad && $tp_ad['start_time']<gmtime() && $tp_ad['end_time']>gmtime() ){
                $tp_ad['ad_code'] = (strpos($tp_ad['ad_code'], 'http://') === false && strpos($tp_ad['ad_code'], 'https://') === false) ?
                    get_data_url($tp_ad['ad_code'], 'afficheimg') : $tp_ad['ad_code'];
                $arr = array('showAd'=>1,'ad'=>$tp_ad);
            }else{
                $arr = array('showAd'=>0);
            }
            echo json_encode($arr);exit();
        } else {
            $this->redirect(url('index'));
        }
    }
}
