<?php
/**
 * ������ ֧�����֪ͨ�ص�ҳ��
 */

define('IN_ECS', true);
require('./init.php');
header("Content-type: text/html; charset=gb2312");

//���շ��صĲ���
$v_oid = $_REQUEST['v_oid'];             //֧���ύʱ�Ķ�����ţ���ʱ����
$v_pstatus = $_REQUEST['v_pstatus'];     //1 ������,20 ֧���ɹ�,30 ֧��ʧ��
$v_pstring = urldecode($_REQUEST['v_pstring']);   //֧�������Ϣ���ء���v_pstatus=1ʱ-���ύ��20-֧����ɡ�30-֧��ʧ��
$v_pmode = urldecode($_REQUEST['v_pmode']);       //֧����ʽ��
$v_amount = $_REQUEST['v_amount'];                //�������
$v_moneytype = $_REQUEST['v_moneytype'];          //����
$v_md5info = $_REQUEST['v_md5info'];
$v_md5money = $_REQUEST['v_md5money'];
$v_sign = $_REQUEST['v_sign'];
//MD5У��

require_once ROOT_PATH . 'includes/sxy.php';
$sxy = new sxy();

$key = $sxy->key;//�̻�����Կ
$data1=$v_oid.$v_pstatus.$v_pstring.$v_pmode;
$md5info= $sxy->hmac($key, $data1);

$data2=$v_amount.$v_moneytype;
$md5money= $sxy->hmac($key, $data2);

if($md5info == $v_md5info && $md5money == $v_md5money)
{
    //echo("����ָ��У��ɹ�<br>");
    if($v_pstatus=='20')
    {
        $sql= "update ".$ecs->table('sxy_account')." set pstatus='$v_pstatus',pstring='$v_pstring',pmode='$v_pmode',pay_time='".time()."' where oid='$v_oid'";
        $db->query("SET NAMES GB2312;");
        $result = $db->query( $sql );
        $str = <<<str
    <div style="text-align:center;margin:100px auto;">
    <p>֧���ɹ�</p><p><span class="s">5</span>����Զ�<a href="javascript:window.location.href='about:blank';window.close();">�ر�ҳ��</a></p>
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
    <p>֧��ʧ��,��鿴֧��ƽ̨��¼</p><p>a href="javascript:window.location.href='about:blank';window.close();">�ر�ҳ��</pa></p>
    </div>
str;
        echo $str;
    }
    else
    {
        $str = <<<str
    <div style="text-align:center;margin:100px auto;">
    <p>δ֧��,�ȴ�����</p><p>a href="javascript:window.location.href='about:blank';window.close();">�ر�ҳ��</pa></p>
    </div>
str;
        echo $str;
    }
}
else
{
    echo("����ָ��У�����");
}

?>