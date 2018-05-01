<?php
/*
* File: CollageController.class.php
* Author: Arliki
* Date: 2018-01-11 10:43
*/
defined('IN_ECTOUCH') or die('Deny Access');
header('contend-type:text/html;charset=utf8');
class CollageController extends CommonController {

    public function wx()
    {
        $payment = model('Order')->payment_info(1);
        $conf=unserialize_config($payment ['pay_config']);
        $wx=array(
            'appid'=>$conf['wxpay_appid'],
            'appsecret'=>$conf['wxpay_appsecret'],
        );
        return $wx;
    }

    public function index(){
        if ($_SESSION ['user_id'] == 0) {
            $this->redirect(url('user/login', array('step' => 'flow')));
            exit;
        }
        $this->assign('title','拼单列表');
        $this->assign('show_type',1);
        $collage_list=M()->query("select g.goods_thumb,g.goods_name goods_name,c.end_time end_time,c.surplus surplus,c.collage_id,c.goods_id goods_id from ecs_collage_index as c left join ecs_goods as g on c.goods_id=g.goods_id");
        if (count($collage_list)<=0){
            $this->assign('show_collage',0);
            $this->display('collage_list.dwt');
            exit;
        }
        $ntime=time();
        $collage=array();
        for ($i=0;$i<count($collage_list);$i++){
            if ($collage_list[$i]["end_time"]>$ntime){
                $localtime=date('Y/m/d H:i',$collage_list[$i]["end_time"]);
                $collage[$i]["goods_thumb"]=get_image_path($collage_list[$i]["goods_id"],$collage_list[$i]["goods_thumb"],true,'gallery');
                $collage[$i]["goods_name"]=$collage_list[$i]["goods_name"];
                $collage[$i]["end_time"]=$localtime;
                $collage[$i]["surplus"]=$collage_list[$i]["surplus"];
                $collage[$i]["collage_id"]=$collage_list[$i]["collage_id"];
                $collage[$i]["goods_id"]=$collage_list[$i]["goods_id"];
            }
        }
        if (count($collage)<=0){
            $this->assign('show_collage',0);
            $this->display('collage_list.dwt');
            exit;
        }
        $this->assign('show_collage',1);
        $this->assign('collage_list',$collage);
        $this->display('collage_list.dwt');
    }
    /*
     * 拼单详情
     */
    public function detail(){
        if ($_SESSION ['user_id'] == 0) {
            $this->redirect(url('user/login', array('step' => 'flow')));
            exit;
        }
        unset($_SESSION['collage_id']);
        $this->assign('title','拼单详情');
        $this->assign('show_type',2);
        $collage_id=I('get.id');
        $goods_need=M()->query("select g.goods_name goods_name,g.goods_thumb from ecs_collage_index as c left join ecs_goods as g on c.goods_id=g.goods_id where c.collage_id='$collage_id'");
        $_SESSION["goodsname"]=$goods_need[0]["goods_name"];
        $_SESSION["goodsthumb"]=$goods_need[0]["goods_thumb"];
        $user_id=$_SESSION['user_id'];
        //返回按钮
        $check=M()->query("select users_id,max,end_time,goods_id,status from ecs_collage_list where Id='$collage_id'");
        $goods_id=$check[0]['goods_id'];
        $_SESSION['share2']=$goods_id;
        $_SESSION['goods_id']=$goods_id;
        isset($_SESSION['share1'])?'':$_SESSION['share1']=-1;
        $usersid=explode(',',$check[0]['users_id']);
        /*
         * 获取头像
         */
        $head=model('Collage')->get_head($usersid);
        $surplus=$check[0]['max']-count($usersid);
        $end_time = date('Y/m/d H:i', $check[0]["end_time"]);
        if(time()>$check[0]['end_time']){
            $collage_btn="<a href='javascript:;' class='show_bingo bingo1 timeout' id='show_bingo'><img src='__TPL__/images/c_is_over.png' class='yiwancheng'></a>";
        }else {
            if (count($usersid) >= $check[0]['max']) {
                $collage_btn = "<a href='javascript:;' class='show_bingo bingo1 timeout' id='show_bingo'><img src='__TPL__/images/c_is_over.png' class='yiwancheng'></a>";
            } else {
                if (in_array($user_id, $usersid)) {
                    $this->assign('show_share', 1);
                    $wxconf = $this->getSignPackage();
                    if (preg_match('/^[\d+].*?[\d+]$/i', $_SERVER['HTTP_HOST'])) {
                        $host = "m.cnxunluo.cn";
                    } else {
                        $host = $_SERVER["HTTP_HOST"];
                    }
                    $share = array(
                        'title' => "超惠拼单",
                        'link' => "https://$host$_SERVER[REQUEST_URI]",
                        'desc' => "我在参与$_SESSION[goodsname]的优惠拼单，来和我一起拼吧~~~",
                        'imgurl' => "https://$host/$_SESSION[goodsthumb]",
                    );
                    $js = "<script>wx.config({debug: false,appId: '{$wxconf[appid]}',timestamp:'{$wxconf[timestamp]}' ,nonceStr: '{$wxconf[noncestr]}',signature: '{$wxconf[sign]}',jsApiList: ['checkJsApi','onMenuShareTimeline','onMenuShareAppMessage']});wx.ready(function(){var share={title: '{$share[title]}',desc: '{$share[desc]}',link: '{$share[link]}',imgUrl: '{$share[imgurl]}',type: '',dataUrl: '',success: function (res) {alert('您已成功分享此拼单');},cancel: function (res) {alert('您已取消分享');},};wx.onMenuShareAppMessage(share);wx.onMenuShareTimeline(share);});wx.error(function (res) {alert(res.errMsg);});</script>";
                    $this->assign('wxjs', $js);
                    $collage_btn = "<a href='javascript:;' class='show_bingo bingo1 timeout' id='is_join'><img src='__TPL__/images/c_is_join.png' class='yicanyu'></a>";
                } else {
                    $url = url('collage/get_js');
                    $collage_btn = "<a href='javascript:;' class='show_bingo bingo1 timeout' id='go_pay'><img src='__TPL__/images/c_o_p.png' class='canyunow'></a>";
                    $this->assign('url', $url);
                }
            }
        }
        //拼单属性
        $attr=M()->query("select collage_o_p,collage_size from ecs_goods where goods_id='$goods_id'");
        $_SESSION['collage_price']=$attr[0]['collage_o_p'];
        $_SESSION['collage_id']=$collage_id;
        $_SESSION['c_goods_id']=$goods_id;
        $_SESSION['wx_order_sn']=$this->createNoncestr(20);
        $this->assign('attr',$attr[0]);
        $this->assign('surplus',$surplus);
        $this->assign('end_time',$end_time);
        $this->assign('collage_btn',$collage_btn);
        $this->assign('user_head',$head);
        //轮播图
        $pic=model('GoodsBase')->get_goods_gallery($goods_id);
        $this->assign('pictures', $pic);
        $goods_img=M()->query("select goods_img,goods_name from ecs_goods where goods_id='$goods_id'");
        $this->assign('goods_img',$goods_img[0]['goods_img']);
        $this->assign('goods_name',$goods_img[0]['goods_name']);

        $this->display('collage_list.dwt');
    }
    /*
     * 发起拼单
     */
    public function  launch_list(){
        unset($_SESSION['collage_id']);
        $this->assign('show_type',3);
        $this->assign('title','可拼单列表');
        //  检查用户是否已经登录
        if ($_SESSION ['user_id'] == 0) {
            $this->redirect(url('user/login', array('step' => 'flow')));
            exit;
        }
        $all_list=M()->query("select goods_id,goods_name,goods_thumb,collage_f_p,collage_o_p,collage_max,collage_time,collage_size from ecs_goods where collage_size!='0' and collage_max!='0' and is_on_sale='1' and is_delete!='1'");
        if(count($all_list)>0){
            foreach ($all_list as $k=>$v){
                $all_list[$k]["goods_thumb"]=get_image_path($v['goods_id'],$v['goods_thumb'],true,'gallery');
            }
            $this->assign('collage_list',$all_list);
        }else{
            $this->assign('no_collage',1);
        }
        $this->display('collage_list.dwt');
    }
    /*
     * 拼单列表
     */
    public function my_collage(){
        if ($_SESSION ['user_id'] == 0) {
            $this->redirect(url('user/login', array('step' => 'flow')));
            exit;
        }

        $this->assign('title','我的拼单');
//        $_SESSION['user_id']=2604;
        $user_id=$_SESSION['user_id'];
        $sql="select id,users_id,status,goods_id,end_time,max from ecs_collage_list order by status asc,id desc";
        $all_list=M()->query($sql);
        $user_collage=array();
        $k=0;
        if ($all_list) {
            for ($i = 0; $i < count($all_list); $i++) {
                $tmp = $all_list[$i];
                $all_id = explode(',', $tmp['users_id']);
                if (in_array($user_id, $all_id)) {
                    $user_collage[$k]['goods_id'] = $tmp['goods_id'];
                    $user_collage[$k]['status'] = $tmp['status'];
                    $user_collage[$k]['end_time'] = date('Y/m/d H:i', $tmp['end_time']);
                    $user_collage[$k]['collage_id'] = $tmp['id'];
                    $user_collage[$k]['surplus'] = $tmp['max'] - count($all_id);
                    $k++;
                }
            }
        }
        if ($user_collage){
            for ($i=0;$i<count($user_collage);$i++){
                $tmp=M()->query("select goods_name,goods_thumb from ecs_goods where goods_id =".$user_collage[$i]['goods_id']);
                $user_collage[$i]['goods_name']=$tmp[0]['goods_name'];
                $user_collage[$i]['goods_thumb']=get_image_path($user_collage[$i]['goods_id'],$tmp[0]['goods_thumb'],true,'gallery');
                $this->assign('show',1);
                $this->assign('collage_list',$user_collage);
            }
        }else{
            $this->assign('show',0);
        }
        $this->assign('show_type',4);
        $this->display('collage_list.dwt');
    }

    public function launch_goods(){
        //  检查用户是否已经登录
        if ($_SESSION ['user_id'] == 0) {
            $this->redirect(url('user/login', array('step' => 'flow')));
            exit;
        }
        $this->assign('show_type',2);
        $this->assign('detail',1);
        $this->assign('title','拼单详情');
        $goods_id=I('get.goods_id');
        //拼单属性
        $detail=M()->query("select goods_name,goods_img,collage_f_p,collage_o_p,collage_max,collage_time,collage_size from ecs_goods where goods_id='$goods_id'");
        $this->assign('goods_detail',$detail[0]);
        //轮播图
        $pic=model('GoodsBase')->get_goods_gallery($goods_id);
        $this->assign('pictures', $pic);
        $this->assign('goods_img', $detail[0]['goods_img']);
        //返回butn
        $_SESSION['wx_order_sn']=$this->createNoncestr(20);
        $_SESSION['collage_price']=$detail[0]['collage_f_p'];
        $_SESSION['c_goods_id']=$goods_id;
        $url=url('collage/get_js');
        $collage_btn="<a href='javascript:;' class='show_bingo bingo1 timeout' id='go_pay'><img src='__TPL__/images/c_f_p.png' class='pinzhubuy'></a>";
        $this->assign('url',$url);
        $this->assign('collage_btn',$collage_btn);
        $this->display('collage_list.dwt');
    }
    public function get_js(){
        //调支付-微信
        $payment = model('Order')->payment_info(1);
        include_once(ROOT_PATH . 'plugins/payment/' . $payment ['pay_code'] . '.php');
        $pay_obj = new $payment ['pay_code'] ();
        $od = array(
            'total_amount' => $_SESSION['collage_price'],
            'log_id' => $_SESSION['collage_id'] . $_SESSION['user_id'].rand(100,999),
            'body' => $_SESSION['wx_order_sn'],
            'goods_id' => $_SESSION['c_goods_id'],
            'collage' => 1
        );
        $js = $pay_obj->get_code($od, unserialize_config($payment ['pay_config']));
        die($js);
    }

    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function getSignPackage() {
        $wx=$this->wx();
        $jsapiTicket = $this->getJsApiTicket();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        if(preg_match('/^[\d+].*?[\d+]$/i',$_SERVER['HTTP_HOST'])){
            $host="https://m.cnxunluo.cn";
        }else{
            $host=$protocol.$_SERVER["HTTP_HOST"];
        }
        $url = "$host$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr(16);
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appid"     => $wx['appid'],
            "noncestr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "sign" => $signature,
            "rawstr" => $string
        );
        return $signPackage;
    }

    public function getJsApiTicket()
    {
    isset($_SESSION['token_time']) ? '' : $_SESSION['token_time'] = 0;
    isset($_SESSION['token_key']) ? '' : $_SESSION['token_key'] = 0;
    isset($_SESSION['ticket_time']) ? '' : $_SESSION['ticket_time'] = 0;
    isset($_SESSION['ticket_key']) ? '' : $_SESSION['ticket_key'] = 0;
    if ($_SESSION['share1'] == $_SESSION['share2'] && $_SESSION['ticket_time'] > time()){
        $ticket = $_SESSION['ticket_key'];
    }else{
        if ($_SESSION['ticket_time'] < time()) {
            $accessToken = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url),true);
            $ticket = $res['ticket'];
            if ($ticket) {
                $_SESSION['ticket_time'] = time() + 7000;
                $_SESSION['ticket_key'] = $ticket;
            }
        } else {
            $ticket = $_SESSION['ticket_key'];
        }
    }
        return $ticket;
    }

    public function getAccessToken() {
        $wx=$this->wx();
        isset($_SESSION['token_time'])?'':$_SESSION['token_time']=0;
        isset($_SESSION['token_key'])?'':$_SESSION['token_key']=0;
        if($_SESSION['token_time']<time()){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$wx[appid]&secret=$wx[appsecret]";
            $res = json_decode($this->httpGet($url),true);
            $access_token = $res['access_token'];
            if ($access_token) {
                $_SESSION['share1']=$_SESSION['goods_id'];
                $_SESSION['token_time']=time()+7000;
                $_SESSION['token_key']=$access_token;
            }
        }else{
            $access_token=$_SESSION['token_key'];
        }
        return $access_token;
    }

    public function httpGet($url,$ssl=true) {
        $curl=curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        $user_agant=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']:'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.78 Safari/537.36';
        curl_setopt($curl, CURLOPT_USERAGENT,$user_agant);
        curl_setopt($curl, CURLOPT_AUTOREFERER,true);
        if($ssl){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        if(false===$response){
            return false;
        }
        return $response;
    }

}