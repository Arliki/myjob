<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/25
 * Time: 11:31
 */
if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
class sxy{
    public $v_mid = '13917';
    public $key = "D0075F87C8DFACC8";
    public $v_version = '1.0';
    public $v_mac;
    public $v_data;
//    public $daifu_url = 'http://210.73.90.235/merchant/virement/mer_payment_submit_utf8.jsp';
    public $daifu_url = 'http://pay.yizhifubj.com/merchant/virement/mer_payment_submit_utf8.jsp';
    public $zhifu_url = 'http://pay.beijing.com.cn/prs/user_payment.checkit';
    public $balance_url = 'https://api.yizhifubj.com/merchant/virement/mer_payment_balance_check.jsp';
//    public $daifu_check_url = 'http://210.73.90.235/merchant/virement/mer_payment_status_utf8.jsp';
    public $daifu_check_url = 'http://pay.yizhifubj.com/merchant/virement/mer_payment_status_utf8.jsp';
    public function hmac ($key, $data)
    {
        // 创建 md5的HMAC

        $b = 64; // md5加密字节长度
        if (strlen($key) > $b) {
            $key = pack("H*",md5($key));
        }
        $key  = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;

        return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
    }

    public function get_v_mac(){
        $data = urlencode($this->v_mid.$this->v_data);
        return $this->hmac($this->key, $data);
    }

    public function get_balance(){
        $v_mac = $this->hmac($this->key, $this->v_mid);
        $opts = array(
            'v_mid'=>$this->v_mid,
            'v_mac'=>$v_mac
        );
        $res = $this->sendHttpPost($this->balance_url, $opts, 'https');
        return $this->xml_to_array($res);
    }

    public function sxy_daifu($data){
        $this->v_data = $data;
        $this->v_mac = $this->get_v_mac();
        $opts = array(
            'v_mid'=>$this->v_mid,
            'v_data'=>$this->v_data,
            'v_mac'=>$this->v_mac,
            'v_version'=>$this->v_version
        );
        $res = $this->sendHttpPost($this->daifu_url, $opts);
        return $this->xml_to_array($res);
    }

    public function daifu_check($data){
        $v_mac = $this->hmac($this->key, $this->v_mid.$data);
        $opts = array(
            'v_mid'=>$this->v_mid,
            'v_data'=>$data,
            'v_mac'=>$v_mac,
            'v_version'=>$this->v_version
        );
        $res = $this->sendHttpPost($this->daifu_check_url, $opts);
        return $this->xml_to_array($res);
    }

    public static $errCode = array(
            'daifu_check'=>array(
                '0'=>'未处理',
                '1'=>'已成功',
                '2'=>'处理中',
                '3'=>'已失效',
                '4'=>'待处理',
                '8'=>'没有该用户标识对应的代付记录',
                '9'=>'查询失败'
            )
        );

    public function make_order($order_info){
        $html = <<<str
<form action="{$this->zhifu_url}" method="post" name="payease_form" target="_parent">
	  <input type="hidden"  name="v_mid"        value="{$this->v_mid}">              
      <input type="hidden"  name="v_oid"      value="{$order_info['v_oid']}">               
      <input type="hidden"  name="v_rcvname"  value="{$this->v_mid}"> 
      <input type="hidden"  name="v_rcvaddr"  value="{$this->v_mid}">       
      <input type="hidden"  name="v_rcvtel"   value="{$this->v_mid}">        
      <input type="hidden"  name="v_rcvpost"  value="{$this->v_mid}">   
      <input type="hidden"  name="v_amount"   value="{$order_info['v_amount']}">       
      <input type="hidden"  name="v_ymd"      value="{$order_info['v_ymd']}">        
      <input type="hidden"  name="v_orderstatus" value="1"> 
      <input type="hidden"  name="v_ordername" value="{$this->v_mid}">
      <input type="hidden"  name="v_moneytype" value="0">     
      <input type="hidden"  name="v_url" value="{$order_info['v_url']}">         
      <input type="hidden"  name="v_md5info" value="{$order_info['v_md5info']}">
</form>
<script language="javascript">payease_form.submit();</script>
str;
        return $html;
    }

    public function sendHttpPost($url, $opts, $https = 'http'){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        $post_string = http_build_query($opts, '', '&');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($https=='https'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // https请求 不验证证书和hosts
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

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