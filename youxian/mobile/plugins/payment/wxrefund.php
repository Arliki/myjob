<?php
/*
* File: wxrefund.php
* Author: Arliki
* Date: 2017-12-21 13:10
* 微信退款
*/
class wxrefund{
    public function refund($user,$payment){
        $key=$payment['wxpay_key'];
        $sed['appid']=$payment['wxpay_appid'];
        $sed['mch_id']=$payment['wxpay_mchid'];
        $sed['nonce_str']=$this->get_nonce_str(20);
        $sed['out_trade_no']=$user['order_sn'];
        $sed['out_refund_no']=$user['order_sn'];
        $sed['total_fee']=$user['super_price'];
        $sed['refund_fee']=$user['super_price'];
        $sed['refund_desc']='未中奖退款';
        $sed['sign']=$this->get_sign($sed,$key);
        $xml=$this->array_to_xml($sed);
        $data=$this->get_result($xml);
        return $data;
    }
    public function get_sign($sed,$key){
        foreach ($sed as $k=>$v) {
            $payments[$k]=$v;
        }
        ksort($payments);
        $head="";
        foreach ($payments as $k=>$v){
            $head .= $k."=".$v."&";
        }
        $str="";
        if (strlen($head)>0){
            $str=substr($head,0,strlen($head)-1);
        }
        $str = $str."&key=".$key;
        $str=md5($str);
        $str=strtoupper($str);
        return $str;
    }
    public function get_result($xml){
        $url="https://api.mch.weixin.qq.com/secapi/pay/refund";
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,1);//证书检查
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_SSLCERT,dirname(__FILE__).'/cert/apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_SSLKEY,dirname(__FILE__).'/cert/apiclient_key.pem');
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_CAINFO,dirname(__FILE__).'/cert/rootca.pem');
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data=curl_exec($ch);
        curl_close($ch);
        if($data){
            //返回来的是xml格式需要转换成数组再提取值，用来做更新
            $res = json_encode(simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOCDATA));
            //$ss=file_put_contents('refun_ok.json',$res."\r\n",FILE_APPEND);
            $res=json_decode($res,true);
            return $res;
        }else{
            //$ss=file_put_contents('rufan_wrong.json',$xml."\r\n",FILE_APPEND);
            $res['msg']="error";
            return $res;
        }
    }
    public function array_to_xml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val){
            if(is_array($val)){
                $xml.="<".$key.">".$this->array_to_xml($val)."</".$key.">";
            }else{
                $xml.="<".$key.">".$val."</".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml ;
    }
    public function get_nonce_str($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}