<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：RespondController.class.php
 * ----------------------------------------------------------------------------
 * 功能描述：ECTOUCH 支付应答控制器
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class RespondController extends CommonController
{

    private $data;

    public function __construct()
    {
        parent::__construct();
        // 获取参数
        $this->data = array(
            'code' => I('get.code'),
            'type' => I('get.type')
        );
	}

    // 发送
    public function index()
    {
        /* 判断是否启用 */
        $condition['pay_code'] = $this->data['code'];
        $condition['enabled'] = 1;
        $enabled = $this->model->table('payment')->where($condition)->count();
        if ($enabled == 0) {
            $msg = L('pay_disabled');
        } else {
            $plugin_file = ADDONS_PATH.'payment/' . $this->data['code'] . '.php';
            if (file_exists($plugin_file)) {
                include_once($plugin_file);
                $payobj = new $this->data['code']();

                $tmpData = array_merge($_GET, $this->data);
                // 处理异步请求
                if($this->data['type'] == 'notify'){
                    @$payobj->notify($this->data);
                }
//                $msg = (@$payobj->callback($this->data)) ? L('pay_success') : L('pay_fail');

                $paySuccess = @$payobj->callback($tmpData);
                $msg = $paySuccess ? L('pay_success') : L('pay_fail');
                //供应商短信通知
                if($paySuccess){
                    if(isset($_SESSION['wx_order_sn']) && $tmpData['superid']) {
                        $this->assign('super', 1);
                        $msg = L('bingo_pay');
                        unset($_SESSION['super']);
                        unset($_SESSION['wx_order_sn']);
                    }elseif (isset($_SESSION['wx_order_sn']) && $tmpData['collage']){
                        $this->assign('super', 2);
                        $msg = "您已成功参与本次拼单";
                        unset($_SESSION['wx_order_sn']);
                    }else {
                        if ($tmpData['orderid'] && $tmpData['ordersn']) {
                            $ids = explode('|', $tmpData['orderid']);
                            $ordersns = explode(';', $tmpData['ordersn']);
                            foreach ($ids as $k => $id) {
                                $agency_id = $this->model->query("SELECT agency_id FROM " . $this->model->pre . "order_info WHERE order_id='" . $id . "'");
                                $gys_info = $this->model->query("SELECT * FROM " . $this->model->pre . "agency WHERE agency_id='" . $agency_id[0]['agency_id'] . "' LIMIT 1");
                                $gys_info = $gys_info[0];
                                if ($gys_info['agency_phone']) {
                                    require_once BASE_PATH . 'libraries/ClSms.class.php';
                                    $clapi = new ClSms();
                                    $content = "【讯罗优选】收到新的订单,订单编号" . $ordersns[$k] . ",请于48小时内发货。";
                                    $res = $clapi->sendSms($gys_info['agency_phone'], $content);
                                    $res = $res['returnsms'];
                                    $sms_data = array(
                                        'id' => '',
                                        'taskid' => $res['taskID'],
                                        'phone' => $gys_info['agency_phone'],
                                        'content' => $content,
                                        'status' => ($res['returnstatus'] == 'Success' ? 1 : 0),
                                        'addtime' => time()
                                    );
                                    $this->model->table('sms_log')->data($sms_data)->insert();
                                } else {
                                    file_put_contents('clsms.log', json_encode($gys_info) . "\r\n", FILE_APPEND);
                                }
                            }
                        }
                    }
                }else{
                    if(isset($_SESSION['wx_order_sn']) && $tmpData['superid']){
                        $paySuccess=2;
                        $msg=L('bingo_err');
                        $alarm=L('bingo_al');
                        write_log('super_pay_error.json',$_SESSION['user_id'].json_encode($tmpData),0);
                        $this->assign('alarm', $alarm);
                        unset($_SESSION['super']);
                        unset($_SESSION['wx_order_sn']);
                    }
                    if(isset($_SESSION['wx_order_sn']) && $tmpData['collage']){
                        $paySuccess=2;
                        $msg="您已参与过本次拼单";
                        $alarm=L('bingo_al');
                        write_log('collage_pay_error.json',$_SESSION['user_id'].json_encode($tmpData),0);
                        $this->assign('alarm', $alarm);
                        unset($_SESSION['wx_order_sn']);
                    }
                }
                $this->assign('paySuccess', $paySuccess);
                $this->assign('paytime', date('Y-m-d H:i:s',time()));
                $this->assign('ordersn', $tmpData['ordersn']);
            } else {
                $msg = L('pay_not_exist');
            }
        }
        //显示页面
        $this->assign('message', $msg);
        $this->assign('shop_url', __URL__);
        $this->assign('title', '支付结果');
        $this->display('respond.dwt');
    }
}