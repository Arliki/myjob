<?php
/**
 * 首信易 支付结果通知回掉页面
 */

define('IN_ECS', true);
require('./init.php');
header("Content-type: text/html; charset=gb2312");
$third_urls = array(
    'G'=>'http://121.42.240.205:8085/garz/sxypay/notify.do'
);
//接收返回的参数
$v_count = $_REQUEST['v_count'];
$v_oid = $_REQUEST['v_oid'];             //支付提交时的订单编号组
$v_pstatus = $_REQUEST['v_pstatus'];
$v_pstring = $_REQUEST['v_pstring'];   //支付结果信息返回。
$v_pmode = $_REQUEST['v_pmode'];       //支付方式。
$v_amount = $_REQUEST['v_amount'];                //订单金额
$v_moneytype = $_REQUEST['v_moneytype'];          //币种
$v_mac = $_REQUEST['v_mac'];
$v_md5money = $_REQUEST['v_md5money'];
$v_sign = $_REQUEST['v_sign'];

//MD5校验
require_once ROOT_PATH . 'includes/sxy.php';
$sxy = new sxy();

$key = $sxy->key;//商户的密钥
$data1=$v_oid.$v_pmode.$v_pstatus.$v_pstring.$v_count;
$md5mac= $sxy->hmac($key, $data1);

$data2=$v_amount.$v_moneytype;
$md5money= $sxy->hmac($key, $data2);

$data3=$v_oid.$v_pstatus.$v_amount.$v_moneytype.$v_count;
$md5sign = $sxy->hmac($key, $data3);

if($md5mac == $v_mac && $md5money == $v_md5money)
{
    //数字指纹校验成功;
    //转发oid
    $v_oids = explode('|_|', $v_oid);
    $v_pmodes = explode('|_|', $v_pmode);
    $v_pstatuses = explode('|_|', $v_pstatus);
    $v_pstrings = explode('|_|', $v_string);
    $v_amounts = explode('|_|', $v_amount);
    $v_moneytypes = explode('|_|', $v_moneytype);
    for($i=0;$i<count($v_oids);$i++){
        $count = 1;
        $oid = $v_oids[$i];
        $pmode = $v_pmodes[$i];
        $pstatus = $v_pstatuses[$i];
        $pstring = $v_pstrings[$i];
        $amount = $v_amounts[$i];
        $moneytype = isset($v_moneytypes[$i])?$v_moneytypes[$i]:$v_moneytypes[0];

        $arr_void = explode('-', $oid);
        $third_flag = mb_substr($arr_void[2], 0 ,1);
        if(!is_numeric($third_flag)){
            if(isset($third_urls[$third_flag])){
                //TODO general data to send
                $data_mac=$oid.$pmode.$pstatus.$pstring.$count;
                $mac= $sxy->hmac($key, $data_mac);

                $data_money=$amount.$moneytype;
                $md5money= $sxy->hmac($key, $data_money);

                $data_sign=$oid.$pstatus.$amount.$moneytype.$count;
                $sign = $sxy->hmac($key, $data_sign);

                $opts = array(
                    'v_count' => $count,
                    'v_oid' => $oid,
                    'v_pstatus'=>$pstatus,
                    'v_pstring'=>$pstring,
                    'v_pmode'=>$pmode,
                    'v_amount'=>$amount,
                    'v_moneytype'=>$moneytype,
                    'v_mac'=>$mac,
                    'v_md5money'=>$md5money,
                    'v_sign'=>$sign
                );
                $res = http_post($third_urls[$third_flag], $opts);
//    $res = xml_to_array($res);
                file_put_contents('../data/w_logs/zhuanfa.log', date('Y/m/d H:i:s', time()).$third_flag.'[status:'.$res.']'.$third_urls[$third_flag]."\r\n", FILE_APPEND);
            }
            continue;
        }

        $sql= "update ".$ecs->table('sxy_account')." set pb_status='$pstatus',pb_string='$pstring',pb_amount='$amount',pb_time='".time()."' where oid='$oid'";
        $db->query("SET NAMES GB2312;");
        $result = $db->query( $sql );
        file_put_contents("../data/w_logs/zhuanfa.log", date('Y/m/d H:i:s', time())."[status:1]$result\r\n", FILE_APPEND);
    }
    echo 'sent';
}
else
{
    file_put_contents("../data/w_logs/zhuanfa.log", date('Y/m/d H:i:s', time())."[status:0]签名校验错误1\r\n", FILE_APPEND);
    echo 'error';
}

/**
 * 发起http post请求
 * @param $url
 * @param $opts
 * @return mixed
 */
function http_post($url, $opts){
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

?>