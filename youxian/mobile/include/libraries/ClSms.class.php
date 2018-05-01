<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/25
 * Time: 16:30
 * 晨露短信
 * http://www.114dx.net/Index.aspx
 */
defined('IN_ECTOUCH') or die('Deny Access');
class ClSms{
    public $userid = 939;
    public $account = 'xunluo';
    public $password = 'xunluo126';
    public $api_url = 'http://123.196.122.28:8812/sms.aspx';

    /**
     * 获取短信余量及总量
     * @return array
     */
    public function getOverage(){
        $post_data = array(
            'userid'=>$this->userid,
            'account'=>$this->account,
            'password'=>$this->password,
            'action'=>'overage'
        );
        $res = $this->http_post($this->api_url, $post_data);
        return $this->xml_to_array($res);
    }

    /**
     * 发送短信
     * @param $mobile 手机号
     * @param $content 短信内容
     * @return array
     */
    public function sendSms($mobile, $content) {
        $post_data = array(
            'userid'=>$this->userid,
            'account'=>$this->account,
            'password'=>$this->password,
            'mobile'=>$mobile,
            'content'=>$content,
            'action'=>'send',
            'sendTime'=>'',
            'extno'=>''
        );
        $res = $this->http_post($this->api_url, $post_data);
        return $this->xml_to_array($res);
    }

    /**
     * 发起http post请求
     * @param $url
     * @param $opts
     * @return mixed
     */
    public function http_post($url, $opts){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 运行cURL，请求网页
        $html = curl_exec($ch);
        // close cURL resource, and free up system resources
        curl_close($ch);
        return $html;
    }

    /**
     * xml 转 array
     * @param $xml
     * @return array
     */
    function xml_to_array($xml) {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }
}