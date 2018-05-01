<?php
/**
 * 首信易 支付结果通知回掉页面
 */

define('IN_ECS', true);
require('./init.php');
header("Content-type: text/html; charset=gb2312");

//接收返回的参数
$v_oid = $_REQUEST['v_oid'];             //支付提交时的订单编号，此时返回
$v_pstatus = $_REQUEST['v_pstatus'];     //1 待处理,20 支付成功,30 支付失败
$v_pstring = urldecode($_REQUEST['v_pstring']);   //支付结果信息返回。当v_pstatus=1时-已提交。20-支付完成。30-支付失败
$v_pmode = urldecode($_REQUEST['v_pmode']);       //支付方式。
$v_amount = $_REQUEST['v_amount'];                //订单金额
$v_moneytype = $_REQUEST['v_moneytype'];          //币种
$v_md5info = $_REQUEST['v_md5info'];
$v_md5money = $_REQUEST['v_md5money'];
$v_sign = $_REQUEST['v_sign'];
//MD5校验

require_once ROOT_PATH . 'includes/sxy.php';
$sxy = new sxy();

$key = $sxy->key;//商户的密钥
$data1=$v_oid.$v_pstatus.$v_pstring.$v_pmode;
$md5info= $sxy->hmac($key, $data1);

$data2=$v_amount.$v_moneytype;
$md5money= $sxy->hmac($key, $data2);

if($md5info == $v_md5info && $md5money == $v_md5money)
{
    //echo("数字指纹校验成功<br>");
    if($v_pstatus=='20')
    {
        $sql= "update ".$ecs->table('sxy_account')." set pstatus='$v_pstatus',pstring='$v_pstring',pmode='$v_pmode',pay_time='".time()."' where oid='$v_oid'";
        $db->query("SET NAMES GB2312;");
        $result = $db->query( $sql );
        $str = <<<str
    <div style="text-align:center;margin:100px auto;">
    <p>支付成功</p><p><span class="s">5</span>秒后自动<a href="javascript:window.location.href='about:blank';window.close();">关闭页面</a></p>
    </div>
    <script >
    setInterval(function(){
        var s =document.querySelector('.s').innerText;
        document.querySelector('.s').innerText=s-1;
        },1000);
    setTimeout(function(){
        window.location.href="about:blank";window.close();
        },5000)
    </script>
str;
        echo $str;
    }
    else if($v_pstatus=='30')
    {
        $sql= "update ".$ecs->table('sxy_account')." set pstatus='$v_pstatus',pstring='$v_pstring',pmode='$v_pmode',pay_time='".time()."' where oid='$v_oid'";
        $db->query("SET NAMES GB2312;");
        $result = $db->query( $sql );
        $str = <<<str
    <div style="text-align:center;margin:100px auto;">
    <p>支付失败,请查看支付平台记录</p><p>a href="javascript:window.location.href='about:blank';window.close();">关闭页面</pa></p>
    </div>
str;
        echo $str;
    }
    else
    {
        $str = <<<str
    <div style="text-align:center;margin:100px auto;">
    <p>未支付,等待处理</p><p>a href="javascript:window.location.href='about:blank';window.close();">关闭页面</pa></p>
    </div>
str;
        echo $str;
    }
}
else
{
    echo("数字指纹校验错误");
}

?>