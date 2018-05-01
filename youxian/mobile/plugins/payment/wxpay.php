<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：wxpay.php
 * ----------------------------------------------------------------------------
 * 功能描述：微信支付插件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (! defined('IN_ECTOUCH')) {
    die('Deny Access');
}

/**
 * 微信支付类
 */
class wxpay
{

    var $parameters; // cft 参数
    var $payment; // 配置信息
    /**
     * 生成支付代码
     *
     * @param array $order
     *            订单信息
     * @param array $payment
     *            支付方式信息
     */
    function get_code($order, $payment)
    {
        // 配置参数
        $this->payment = $payment;
        // 网页授权获取用户openid
        $openid = empty($_SESSION['openid']) ? $_SESSION['wechat_user']['openid'] : $_SESSION['openid'];
//        fpc_debug('payFail.log','openid:'.$openid);
        if (!isset($openid) || empty($openid)) {
            file_put_contents("showPayBtn.debug.log",date('Y-m-d H:i:s', time())."::openid:$openid:::user_id:".$_SESSION['user_id']."\r\n",FILE_APPEND);
            if($_SESSION['user_id']){
                $openid = M()->query("SELECT openid FROM ecs_wechat_user WHERE ect_uid='".$_SESSION['user_id']."' LIMIT 1");
                $openid = $openid[0]['openid'];
                if($openid){
                    $_SESSION['openid'] = $openid;
                }else{
                    file_put_contents("showPayBtn.debug.log",date('Y-m-d H:i:s', time())."::没找到粉丝绑定信息\r\n",FILE_APPEND);
                    return false;
                }
            }else{
                file_put_contents("showPayBtn.debug.log",date('Y-m-d H:i:s', time())."::用户未登录\r\n",FILE_APPEND);
                return false;
            }
        }
//        file_put_contents("showPayBtn.debug.log",date('Y-m-d H:i:s', time())."::PASS--openid:$openid\r\n",FILE_APPEND);
        // 设置必填参数
        $body = mb_substr($order['body'], -128);
        $out_trade_no = mb_substr(date('Ymd',time()).'A'.($order['total_amount']*100).'B'.$order['log_id'],-32);
        // 根目录url
        if($order['super'] == 1){
            $n=array(
                'a'=>$_SESSION['act_id'],
                'u'=>$_SESSION['user_id'],
                's'=>$_SESSION['super'],
                'w'=>$_SESSION['wx_order_sn'],
                'd'=>$_SESSION['disa'],
                'o'=>$out_trade_no
            );
            $this->setParameter("attach",json_encode($n));
            write_log('user_super_click.json',json_encode($n),0);
        }
        if($order['collage'] == 1){
            $n=array(
                'c'=>$_SESSION['collage_id'],
                'u'=>$_SESSION['user_id'],
                'w'=>$_SESSION['wx_order_sn'],
                'g'=>$_SESSION['c_goods_id'],
                'o'=>$out_trade_no
            );
            $this->setParameter("attach",json_encode($n));
            write_log('collage_click.json',json_encode($n),0);
        }
        $this->setParameter("openid", "$openid"); // 商品描述
        $this->setParameter("body", $body); // 商品描述
        $this->setParameter("out_trade_no", $out_trade_no); // 商户订单号
        $this->setParameter("total_fee", intval($order['total_amount'] * 100)); // 总金额
        $this->setParameter("notify_url", return_url(basename(__FILE__, '.php'), true)); // 通知地址
        $this->setParameter("trade_type", "JSAPI"); // 交易类型

        $prepay_id = $this->getPrepayId();

        $jsApiParameters = $this->getParameters($prepay_id);

        /**
         * 预购活动
         */
        if($order['super']){
            $js = '<script language="javascript">
        function jsApiCall(){WeixinJSBridge.invoke("getBrandWCPayRequest",' . $jsApiParameters . ',function(res){if(res.err_msg == "get_brand_wcpay_request:ok"){location.href="' . return_url(basename(__FILE__, '.php'), false, array('status' => 1,'ordersn'=>$order['body'],'superid'=>$order['super'])) . '"}else{location.href="' . return_url(basename(__FILE__, '.php'), false, array('status' => 0,'ordersn'=>$order['body'],'superid'=>$order['super'])) . '"}});}function callpay(){if (typeof WeixinJSBridge == "undefined"){if( document.addEventListener ){document.addEventListener("WeixinJSBridgeReady", jsApiCall, false);}else if (document.attachEvent){document.attachEvent("WeixinJSBridgeReady", jsApiCall);document.attachEvent("onWeixinJSBridgeReady", jsApiCall);}}else{jsApiCall();}}
            </script>';
            return $js;
        }elseif($order['collage']){
            $js = '<script language="javascript">
        function jsApiCall(){WeixinJSBridge.invoke("getBrandWCPayRequest",' . $jsApiParameters . ',function(res){if(res.err_msg == "get_brand_wcpay_request:ok"){location.href="' . return_url(basename(__FILE__, '.php'), false, array('status' => 1,'ordersn'=>$order['body'],'collage'=>$order['collage'])) . '"}else{location.href="' . return_url(basename(__FILE__, '.php'), false, array('status' => 0,'ordersn'=>$order['body'],'collage'=>$order['collage'])) . '"}});}function callpay(){if (typeof WeixinJSBridge == "undefined"){if( document.addEventListener ){document.addEventListener("WeixinJSBridgeReady", jsApiCall, false);}else if (document.attachEvent){document.attachEvent("WeixinJSBridgeReady", jsApiCall);document.attachEvent("onWeixinJSBridgeReady", jsApiCall);}}else{jsApiCall();}}
            </script>';
            return $js;
        }else{
            $js = '<script language="javascript">
        function jsApiCall(){WeixinJSBridge.invoke("getBrandWCPayRequest",' . $jsApiParameters . ',function(res){if(res.err_msg == "get_brand_wcpay_request:ok"){location.href="' . return_url(basename(__FILE__, '.php'), false, array('status' => 1,'ordersn'=>$order['body'],'orderid'=>$order['ids'])) . '"}else{location.href="' . return_url(basename(__FILE__, '.php'), false, array('status' => 0,'ordersn'=>$order['body'],'orderid'=>$order['ids'])) . '"}});}function callpay(){if (typeof WeixinJSBridge == "undefined"){if( document.addEventListener ){document.addEventListener("WeixinJSBridgeReady", jsApiCall, false);}else if (document.attachEvent){document.attachEvent("WeixinJSBridgeReady", jsApiCall);document.attachEvent("onWeixinJSBridgeReady", jsApiCall);}}else{jsApiCall();}}
            </script>';
            $button = '<div style="text-align:center"><button class="btn-info ect-btn-info" style="background-color:#44b549;" type="button" onclick="callpay()">去付款</button></div>' . $js;
        }
        return $button;
    }

    /**
     * 响应操作
     */
    function callback($data)
    {
        if ($data['status'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 响应操作
     */
    function notify($data)
    {
        $inputdata = file_get_contents("php://input");
        if (! empty($inputdata)) {
            $payment = model('Payment')->get_payment($data['code']);
            $postdata = json_decode(json_encode(simplexml_load_string($inputdata, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */

            // 微信端签名
            $wxsign = $postdata['sign'];
            unset($postdata['sign']);

            // 微信附加参数
            $attach = $postdata['attach'];

            foreach ($postdata as $k => $v) {
                $Parameters[$k] = $v;
            }
            // 签名步骤一：按字典序排序参数
            ksort($Parameters);

            $buff = "";
            foreach ($Parameters as $k => $v) {
                $buff .= $k . "=" . $v . "&";
            }
            $String = '';
            if (strlen($buff) > 0) {
                $String = substr($buff, 0, strlen($buff) - 1);
            }
            // 签名步骤二：在string后加入KEY
            $String = $String . "&key=" . $payment['wxpay_key'];
            // 签名步骤三：MD5加密
            $String = md5($String);
            // 签名步骤四：所有字符转为大写
            $sign = strtoupper($String);
            // 验证成功
            if ($wxsign == $sign) {
                // 交易成功
                if ($postdata['result_code'] == 'SUCCESS') {
                    if(isset($postdata["attach"])){
                        $sq=json_decode($postdata["attach"],true);
                        if($sq['a']) {
                            model('Super')->pre_order($sq["u"], $sq["a"], $sq["s"], $postdata["total_fee"] / 100, $sq["w"], $sq['o'], $sq['d']);
                        }elseif ($sq['g']){
                            write_log('do_collage.json',json_encode($sq),1);
                            model('Collage')->collage_pay($sq['g'],$sq['c'],$sq['u'],$sq['w'],$sq['o'],$postdata["total_fee"] / 100);
                        }
                    }else {
                        // 获取log_id
                        $out_trade_no = explode('B', $postdata['out_trade_no']);
                        $log_ids = $out_trade_no[1]; // 订单号log_id
                        //TODO 循环
                        foreach (explode('|', $log_ids) as $log_id) {
                            // 改变订单状态
                            if ($attach == 'drp') {
                                model('Payment')->drp_order_paid($log_id, 2);
                            } else {
                                model('Payment')->order_paid($log_id, 2);
                            }
                            model('Gys')->wxpayInAccount($log_id);
                            /*供应商入账END*/
                            if (method_exists('WechatController', 'do_oauth')) {
                            }
                        }
                    }
                }
                $returndata['return_code'] = 'SUCCESS';
            } else {
                unset($_SESSION['wx_order_sn']);
                $returndata['return_code'] = 'FAIL';
                $returndata['return_msg'] = '签名失败';
            }
        } else {
            $returndata['return_code'] = 'FAIL';
            $returndata['return_msg'] = '无数据返回';
        }
        // 数组转化为xml
        $xml = "<xml>";
        foreach ($returndata as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";

        echo $xml;
        exit();
    }

    function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     * 作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 作用：设置请求参数
     */
    function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 作用：生成签名
     */
    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        // 签名步骤一：按字典序排序参数
        ksort($Parameters);

        $buff = "";
        foreach ($Parameters as $k => $v) {
            $buff .= $k . "=" . $v . "&";
        }
        $String = '';
        if (strlen($buff) > 0) {
            $String = substr($buff, 0, strlen($buff) - 1);
        }
        // echo '【string1】'.$String.'</br>';
        // 签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->payment['wxpay_key'];
        // echo "【string2】".$String."</br>";
        // 签名步骤三：MD5加密
        $String = md5($String);
        // echo "【string3】 ".$String."</br>";
        // 签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        // echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     * 作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml, $url, $second = 30)
    {
        // 初始化curl
        $ch = curl_init();
        // 设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        // 这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        // 运行curl
        $data = curl_exec($ch);
        // 返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            curl_close($ch);
            return false;
        }
    }

    /**
     * 获取prepay_id
     */
    function getPrepayId()
    {
        // 设置接口链接
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        try {
            // 检测必填参数
            if ($this->parameters["out_trade_no"] == null) {
                throw new Exception("缺少统一支付接口必填参数out_trade_no！" . "<br>");
            } elseif ($this->parameters["body"] == null) {
                throw new Exception("缺少统一支付接口必填参数body！" . "<br>");
            } elseif ($this->parameters["total_fee"] == null) {
                throw new Exception("缺少统一支付接口必填参数total_fee！" . "<br>");
            } elseif ($this->parameters["notify_url"] == null) {
                throw new Exception("缺少统一支付接口必填参数notify_url！" . "<br>");
            } elseif ($this->parameters["trade_type"] == null) {
                throw new Exception("缺少统一支付接口必填参数trade_type！" . "<br>");
            } elseif ($this->parameters["trade_type"] == "JSAPI" && $this->parameters["openid"] == NULL) {
                throw new Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！" . "<br>");
            }
            $this->parameters["appid"] = $this->payment['wxpay_appid']; // 公众账号ID
            $this->parameters["mch_id"] = $this->payment['wxpay_mchid']; // 商户号
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; // 终端ip
            $this->parameters["nonce_str"] = $this->createNoncestr(); // 随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters); // 签名
            $xml = "<xml>";
            foreach ($this->parameters as $key => $val) {
                if (is_numeric($val)) {
                    $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
                } else {
                    $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
                }
            }
            $xml .= "</xml>";
        } catch (Exception $e) {
            file_put_contents("showPayBtn.debug.log",date('Y-m-d H:i:s', time())."::Exception:".$e->getMessage()."\r\n",FILE_APPEND);
            die($e->getMessage());
        }

        // $response = $this->postXmlCurl($xml, $url, 30);
        $response = Http::curlPost($url, $xml, 30);
        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $prepay_id = $result["prepay_id"];
        return $prepay_id;
    }

    /**
     * 作用：设置jsapi的参数
     */
    public function getParameters($prepay_id)
    {
        $jsApiObj["appId"] = $this->payment['wxpay_appid'];
        $timeStamp = time();
        $jsApiObj["timeStamp"] = "$timeStamp";
        $jsApiObj["nonceStr"] = $this->createNoncestr();
        $jsApiObj["package"] = "prepay_id=$prepay_id";
        $jsApiObj["signType"] = "MD5";
        $jsApiObj["paySign"] = $this->getSign($jsApiObj);
        $this->parameters = json_encode($jsApiObj);

        return $this->parameters;
    }
}