<?php
/*
* File: SuperController.class.php
* Author: Arliki
* Date: 2017-12-01 10:49
*
*/
defined('IN_ECTOUCH') or die('Deny Access');
header('contend-type:text/html;charset=utf8');
class SuperController extends CommonController {
    public function __construct() {
        parent::__construct();
    }
    public function index()
    {
        $_SESSION['super']='';
        //首次进入提示窗
        if($_SESSION['super']){
            $this->assign('first_alert',0);
        }else{
            $this->assign('first_alert',1);
        }
        //  检查用户是否已经登录 如果用户已经登录了则检查是否有默认的收货地址 如果没有登录则跳转到登录和注册页面
        if (empty($_SESSION ['direct_shopping']) && $_SESSION ['user_id'] == 0) {
            /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
            $this->redirect(url('user/login',array('step'=>'flow')));
            exit;
        }
        //获取活动id
        if($_GET['id']){
            $act_id=$_GET['id'];
        }else{
            $act_id=$_GET['act_id'];
        }
        //获取活动内容
        $super=model('Super')->get_super($act_id);
        $goods_id=$super[0]['goods_id'];
        $_SESSION['bid_price']=$super[0]['short_price'];
        $_SESSION['goods_nu']=$super[0]['max_num'];
        $_SESSION['goods_names']=$super[0]['goods_name'];
        $_SESSION['btime']=$super[0]['start_time'];
        $_SESSION['etime']=$super[0]['end_time'];
        //判断是否开始并初始化文件----待改进
        if(gmtime()<$_SESSION['btime']) {
            if (file_exists('last.txt')) {
                file_put_contents('last.txt',500);
            } else {
                file_put_contents('last.txt', 500);
            }
            if (file_exists('nums.txt')) {
                file_put_contents('nums.txt', 1);
            } else {
                file_put_contents('nums.txt', 1);
            }
            if (file_exists('super.txt')) {
                file_put_contents('super.txt', 0);
            } else {
                file_put_contents('super.txt', 0);
            }
            if (file_exists('old.txt')) {
                file_put_contents('old.txt', 0);
            } else {
                file_put_contents('old.txt', 0);
            }
            if (file_exists('super_old.txt')) {
                file_put_contents('super_old.txt', 0);
            } else {
                file_put_contents('super_old.txt', 0);
            }
        }
        //当前已抢购数量、检查时间、返回时间差
        $new_num=file_get_contents('super.txt');
        $new_num=model('Super')->get_int($new_num,6);
        $check_times=5;
        $check_nums=file_get_contents('nums.txt');
        $timecha=$_SESSION['btime'];
        $timecha=model('Super')->get_int($timecha,15);
        $timecha=gmtime()-$timecha;
        $this->assign('timecha',$timecha);
        //获取活动时间并设置概率
        $_SESSION['time_diff']=$_SESSION['etime']-$_SESSION['btime'];
        $_SESSION['owen_num']=floor(($_SESSION['goods_nu']-$new_num)/floor(($_SESSION['time_diff']-($check_nums*$check_times))/$check_times));
        //未结束对分子修改赋值
        if ($_SESSION['time_diff']-($check_nums*$check_times)>0){
            if($_SESSION['owen_num']==0){
                $_SESSION['owen_num']=1;
            }
            if($_SESSION['owen_num']<0){
                $_SESSION['owen_num']=$_SESSION['goods_nu']-$new_num;
            }
        }
        $_SESSION['super']=$goods_id;
        $_SESSION['act_id']=$act_id;
        //检查状态
        $check=model('Super')->check_node();
        if($check){
            $res['che']=$check;
            $this->assign('fankui',$res);
        }
        $this->assign('send_time',rand(400,1200));
        $this->assign('super',$act_id);
        $this->assign('nowtime',gmtime());
        $this->display('index.dwt');
    }

    /**
     * 点击
     */
    public function check_bingo()
    {
        $now_time=gmtime();
        $first_click=500;//概率分母默认值
        //检查用户状态--拉黑、已买、无
        $check=model('Super')->check_node();
        if($check){
            $res["msg"]=$check;
            $res["type"]=2;
            $res["url"]=url('index/index');
            die(json_encode($res));
        }
        $new_num=file_get_contents('super.txt');
        //先到先得用户数量
        if($new_num<=floor($_SESSION['goods_nu']/3)){
            $new_num++;
            file_put_contents('super.txt',$new_num);
            $super_super_old=file_get_contents('super_old.txt');
            $super_super_old++;
            file_put_contents('super_old.txt',$super_super_old);
            $sql="insert into ecs_snatch_log (snatch_id,user_id,bid_price,bid_time) values('".$_SESSION['act_id']."','".$_SESSION['user_id']."','".$_SESSION['bid_price']."','".gmtime()."' )";
            M()->query($sql);
            $select="select act.goods_name,goods_sn,short_price from ecs_goods_activity act left join ecs_goods goods on goods.goods_id=act.goods_id where act.goods_id=".$_SESSION['super'];
            $snatch=M()->query($select);
            $snatch=$snatch[0];
            model('Order')->clear_cart(CART_SNATCH_GOODS);
            $_SESSION['goods_sns']=$snatch['goods_sn'];
            /* 加入购物车 */
            $cart = array(
                'user_id'        => $_SESSION['user_id'],
                'session_id'     => SESS_ID,
                'goods_id'       => $_SESSION["super"],
                'product_id'     => 0,
                'goods_sn'       => addslashes($snatch['goods_sn']),
                'goods_name'     => addslashes($snatch['goods_name']),
                'market_price'   => $snatch['short_price'],
                'goods_price'    => $snatch['short_price'],
                'goods_number'   => 1,
                'goods_attr'     => '抢购活动专属',
                'goods_attr_id'  => 1,
                'is_real'        => 1,
                'extension_code' => '1',
                'parent_id'      => 0,
                'rec_type'       => 0,
                'is_gift'        => 0
            );
            $this->model->table('cart')->data($cart)->insert();
            $url=url('flow/checkout');
            $_SESSION["isis"]=1;
            $msg="Bingo，正在生成订单... ...";
            $res=array(
                'msg'=>$msg,
                'url'=>$url,
                'type'=>3,
                'time'=>15
            );
            unset($_SESSION['check']);
            die(json_encode($res));
        }

        if($new_num>=$_SESSION['goods_nu']){
            $res["msg"]="商品已经抢完咯";
            $res["type"]=2;
            $res["url"]=url('index/index');
            die(json_encode($res));
        }
        if($_SESSION['etime']<$now_time){
            $res["msg"]="活动已经结束";
            $res["type"]=1;
            die(json_encode($res));
        }
        if($_SESSION['btime']>$now_time){
            $res["msg"]="活动尚未开始";
            $res["type"]=1;
            die(json_encode($res));
        }
        //获取点击量  ++
        $all_times_last=file_get_contents('last.txt');
        $all_times_last++;
        //判断是否为第一次加载点击次数
        if(isset($_SESSION['lastlast'])){
        }else{
            $_SESSION['lastlast']=0;
        }
        //获取 检查时间、检查次数、当前概率分母
        $check_times=5;
        $check_nums=file_get_contents('nums.txt');
        if (isset($all_times_now)){
        }else{
            $all_times_now=$first_click;
        }
        //检查是否到达检查时间点
        $ci=floor(($now_time-$_SESSION['btime'])/$check_times)+1;
        if ($ci>$check_nums){
            $all_times_now=abs($all_times_last-$_SESSION['lastlast']);
            $check_nums=$ci;
            $_SESSION['lastlast']=$all_times_now;
            file_put_contents('last.txt',$_SESSION['lastlast']);
            file_put_contents('nums.txt',$check_nums);
            /**
             * 测试
             */
            $super_num_now=file_get_contents('super.txt');
            file_put_contents('dianji.json',"last=".$all_times_last."now/fenmu=".$all_times_now."fenzi='".$_SESSION['owen_num']."' maichu=".$super_num_now.PHP_EOL,FILE_APPEND);
        }else{
            file_put_contents('last.txt',$all_times_last);
        }
        //  检查用户是否已经登录 如果用户已经登录了则检查是否有默认的收货地址 如果没有登录则跳转到登录和注册页面
        if (empty($_SESSION ['direct_shopping']) && $_SESSION ['user_id'] == 0) {
            /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
            $res["msg"]="您还没有登录哦";
            $res["type"]=2;
            $res["url"]=url('user/login');
            die(json_encode($res));
        }
        //获取用户等级
        $user_id=$_SESSION['user_id'];
        $rank_points=M()->query("select rank_points from ecs_users where user_id = ".$user_id);
        switch ($rank_points)
        {
            case $rank_points<50:
                $rank_points=1;
                break;
            case $rank_points<100:
                $rank_points=2;
                break;
            case $rank_points<500:
                $rank_points=3;
                break;
            case $rank_points>500:
                $rank_points=4;
                break;
            default:
                $rank_points=0;
        }
        //判断脚本攻击
        $time=check_time(3);
        if (isset($_SESSION['check']['old'])){
            $che=$time-$_SESSION['check']['old'];
            switch ($che){
                case $che<70:
                    $_SESSION['check']['script']++;
                    break;
                case $che<150:
                    $_SESSION['check']['medium']++;
                    break;
                default :
                    $_SESSION['check']['slow']++;
            }
            if($_SESSION['check']['script']>=10){
                M()->query("insert into ecs_snatch_log (snatch_id,user_id,bid_price,bid_time) VALUES ('".$_SESSION['super']."','".$_SESSION['user_id']."','-1','".gmtime()."')");
                $msg="Script attack";
                $res=array(
                    'msg'=>$msg,
                    'type'=>3,
                    'url'=>url('index/index'),
                    'time'=>20
                );
                die(json_encode($res));
            }
            $_SESSION['check']['old']=$time;
        }else{
            $_SESSION['check']['old']=$time;
            $_SESSION['first']=gmtime();
        }
        //更换中奖概率
        if($all_times_now<50) {
            $num = get_rand(5, 2, $rank_points);
        }else{
            $num = get_bingo_rand($_SESSION['owen_num'],$all_times_now,$rank_points);
        }//
        if (isset($num['bingo']) && isset($num['user']) && $num['user']<=$num['bingo']){
            $sql="insert into ecs_snatch_log (snatch_id,user_id,bid_price,bid_time) values('".$_SESSION['act_id']."','".$_SESSION['user_id']."','".$_SESSION['bid_price']."','".gmtime()."' )";
            $new_num++;
            file_put_contents('super.txt',$new_num);
            $super_super_old=file_get_contents('super_old.txt');
            $super_super_old++;
            file_put_contents('super_old.txt',$super_super_old);
            M()->query($sql);
            $select="select act.goods_name,goods_sn,short_price from ecs_goods_activity act left join ecs_goods goods on goods.goods_id=act.goods_id where act.goods_id=".$_SESSION['super'];
            $snatch=M()->query($select);
            $snatch=$snatch[0];
            model('Order')->clear_cart(CART_SNATCH_GOODS);
            $_SESSION['goods_sns']=$snatch['goods_sn'];
            /* 加入购物车 */
            $cart = array(
                'user_id'        => $_SESSION['user_id'],
                'session_id'     => SESS_ID,
                'goods_id'       => $_SESSION["super"],
                'product_id'     => 0,
                'goods_sn'       => addslashes($snatch['goods_sn']),
                'goods_name'     => addslashes($snatch['goods_name']),
                'market_price'   => $snatch['short_price'],
                'goods_price'    => $snatch['short_price'],
                'goods_number'   => 1,
                'goods_attr'     => '抢购活动专属',
                'goods_attr_id'  => 1,
                'is_real'        => 1,
                'extension_code' => '1',
                'parent_id'      => 0,
                'rec_type'       => 0,
                'is_gift'        => 0
            );
            $this->model->table('cart')->data($cart)->insert();
            $url=url('flow/checkout');
            $_SESSION["isis"]=1;
            $msg="Bingo，正在生成订单... ...";
            $res=array(
                'msg'=>$msg,
                'url'=>$url,
                'type'=>3,
                'time'=>15
            );
            die(json_encode($res));
        }else{
            $res=array(
                'type'=>-1
            );
            die(json_encode($res));
        }
    }

    /**
     * 请求中奖信息
     */
    public function get_bingo(){
        $super_num_now=file_get_contents('super.txt');
        $super_num_old=file_get_contents('old.txt');//上一次推送数量
        $super_super_old=file_get_contents('super_old.txt');
        $super_num_all=$_SESSION['goods_nu'];//总数  固定
        if($super_num_now==0){
            $res["num_now"]=0;
            die(json_encode($res));
        }
        //是否更新交易量
        if ($super_super_old != $super_num_now) {
            if ($super_num_now == $super_num_all) {
                $res["num_now"] = 1000;
            } else {
                $add = floor(1000 / $super_num_all);//增加值
                $add = rand($add - 5, $add + 5);
                $res["num_now"] = $super_num_now * $add;/*已经抢购数量*/
                if ($res["num_now"] < $super_num_old) {
                    $res["num_now"] += ($super_super_old - $super_num_now) * $add;
                }
                if($res["num_now"]>=1000){
                    $res["num_now"]=1000;
                }
                file_put_contents('old.txt',$res["num_now"]);
            }
        } else {
            $res["num_now"] = $super_num_old;/*不做改动*/
        }
        //返回中奖信息--随机
        $nowtime=gmtime();
        if ($nowtime-$_SESSION["btime"]>=1 && $res["num_now"]!=0) {
            $res['user_name'] = array();
            $num = M()->query("select count(*) from ecs_users");
            $num = $num[0]["count(*)"];
            $id = rand(1, $num - 7);
            $nextid = $id + 4;
            $name = M()->query("select user_name from ecs_users where user_id>=" . $id . " and user_id<=" . $nextid);
            for ($i = 0; $i <= 4; $i++) {
                array_push($res['user_name'], model('Super')->hideStr($name[$i]["user_name"], 5, 7));
            }
        }
        die(json_encode($res));
    }
    /**
     * 检查是否购买/确认订单
     */
    public function check_pay(){
        $sql="select info.pay_status pay,info.order_id order_id,info.add_time add_time,info.user_id user_id from ecs_order_info AS info inner JOIN ecs_order_goods AS goods ON goods.order_id = info.order_id where goods.goods_id='".$_SESSION['super']."' AND info.goods_amount='".$_SESSION['bid_price']."' AND info.add_time > '".$_SESSION['btime']."' ";
        $cou=M()->query($sql);
        if($cou){
            $log="select user_id,bid_time from ecs_snatch_log where bid_price='".$_SESSION['bid_price']."' and snatch_id='".$_SESSION['act_id']."' ";
            $log=M()->query($log);
            $log_num=count($log);
            $count=count($cou);
            if($log_num!=$count) {
                $check_uid = array();
                for ($j = 0; $j < $count; $j++) {
                    $check_uid[$j] = $cou[$j];
                }
                for($t=0;$t<$log_num;$t++){
                    if(in_array($log[$t]["user_id"],$check_uid)){
                    }else{
                        if(gmtime()-$log[$t]["bid_time"]>25){
                            M()->query("delete from ecs_cart where user_id='".$_SESSION['user_id']."' and goods_id='".$_SESSION['super']."' ");
                            $super_num_now=file_get_contents('super.txt');
                            $super_super_old=file_get_contents('super_old.txt');
                            $super_num_now--;
                            $super_super_old--;
                            file_put_contents('shanchu.json',"order_id=".$cou[$t]["order_id"]."user=".$_SESSION['user_id']."dangqian=".$super_num_now."yonghu=".$log[$t]["user_id"].PHP_EOL,FILE_APPEND);//检测删除确认的
                            file_put_contents("super.txt",$super_num_now);
                            file_put_contents("super_old.txt",$super_super_old);
                        }
                    }
                }
            }
            for ($i=0;$i<$count;$i++){
                if(gmtime()-$cou[$i]["add_time"]>20){
                    $del="delete from ecs_order_info where order_id=".$cou[$i]["order_id"];
                    $d1=M()->query($del);
                    $del3="delete from ecs_order_goods where order_id=".$cou[$i]["order_id"];
                    $d3=M()->query($del3);
                    $super_num_now=file_get_contents('super.txt');
                    $super_super_old=file_get_contents('super_old.txt');
                    $super_num_now--;
                    /**
                     * 测试
                     */
                    file_put_contents('shanchu.json',"order_id=".$cou[$i]["order_id"]."user=".$_SESSION['user_id']."dangqian=".$super_num_now."d1=".$d1."d3=".$d3.PHP_EOL,FILE_APPEND);//检测删除确认
                    $super_super_old--;
                    file_put_contents("super.txt",$super_num_now);
                    file_put_contents("super_old.txt",$super_super_old);
                }
            }
            $res["msg"]="null";
            die(json_encode($res));
        }
    }
    /**
     * 预购活动
     */
    public function pre_order()
    {
        //  检查用户是否已经登录
        if ($_SESSION ['user_id'] == 0) {
            /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
            $this->redirect(url('user/login', array('step' => 'flow')));
            exit;
        }
        //++++++++检测是否有地址添加地址
        $se_add = M()->query("select address_id from ecs_user_address where user_id=" . $_SESSION ['user_id'] . " order by address_id desc");
        if (empty($se_add[0]["address_id"])) {
            $this->redirect(url('user/add_address'));
            exit;
        }
        //获取活动id
        if ($_GET['id']) {
            $act_id = $_GET['id'];
        } else {
            $this->redirect(url('user/login', array('step' => 'flow')));
            exit;
        }
        //获取活动内容、设置保存信息
        $super = model('Super')->get_super($act_id);
        $_SESSION['super'] = $super[0]['goods_id'];
        $_SESSION['act_id'] = $super[0]['act_id'];
        $_SESSION['bid_price'] = $super[0]['short_price'];
        $etime = $super[0]['end_time'];
        $btime = $super[0]['start_time'];
        $wx_order_sn = $this->createNoncestr(20);
        $_SESSION['wx_order_sn'] = $wx_order_sn;
        //是否购买
        $check=M()->query("select add_time from ecs_pre_order where act_id='".$_SESSION['act_id']."' and super='".$_SESSION['super']."' and userid='".$_SESSION['user_id']."' order by add_time desc");
        $_SESSION['disa']='0';
        //是否结束
        if (gmtime()<$btime) {
            $btn = "<button class='is_join' type='button' id='is_join'>活动尚未开始</button>";
        }else{
            if (gmtime() > $etime) {
                $url = url('super/show_bingo');
                $btn = "<button class='show_bingo' type='button' id='show_bingo'>查看中奖名单</button>";
            } else {
                if ($check[0]['add_time']) {
                    $btn = '<button class="is_join" type="button" id="is_join">您已参与抽奖</button>';
                } else {
                    //↓↓↓↓↓↓↓↓
                    $white = array(
                        '2192', '2604', '4132', '6376', '2792', '261', '4133', '1032', '3504', '7141'
                    );
                    if (in_array($_SESSION['user_id'], $white))
                    {
                        $_SESSION['disa'] = 'disw';
                    }else{
                        M()->query("select userid from ecs_pre_order where userid='".$_SESSION['user_id']."' and bingo='1'") ? $_SESSION['disa'] = 'disa' : $_SESSION['disa']='disb';
                    }
                    //↑↑↑↑↑↑↑↑

                    $url = url('super/get_js');
                    $btn='<button class="go_pay" id="go_pay" type="button">参与抽奖</button>';
                }
            }
        }
        $this->assign('show_btn', $btn);
        $this->assign('url', $url);
        $this->display('pre_order.dwt');
    }
    /**
     * js
     */
    public function get_js(){
        //调支付--微信
        $payment = model('Order')->payment_info(1);
        include_once(ROOT_PATH . 'plugins/payment/' . $payment ['pay_code'] . '.php');
        $pay_obj = new $payment ['pay_code'] ();
        $od = array(
            'total_amount' => $_SESSION['bid_price'],
            'log_id' => $_SESSION['act_id'] . $_SESSION['super'] . $_SESSION['user_id'],
            'body' => $_SESSION['wx_order_sn'],
            'disa'=>$_SESSION['disa'],
            'super' => 1
        );
        $js = $pay_obj->get_code($od, unserialize_config($payment ['pay_config']));
        die($js);
    }
    /**
     * 产生随机字符串，不长于32位
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
     * 返回中奖名单  人数 ：50
     */
    public function show_bingo(){
        $order_url=url('user/order_list');//查看个人全部订单
        $this->assign('own_order', $order_url);
        $goto_index=url('index/index');//首页
        $this->assign('goto_index', $goto_index);
        $this->assign('url',url('super/show_bingos'));
        $this->display('super_bingo.dwt');
    }
    public function show_bingos(){
//        $_SESSION['act_id']=5;
//        $_SESSION['super']=160;   //测试开启
        $sql="select userid from ecs_pre_order where act_id='".$_SESSION['act_id']."' and super='".$_SESSION['super']."'and bingo=1 limit 50";
        $uid=M()->query($sql);
        if(count($uid)<10){
         $nuid["none"]=1;
        }else {
            $nuid["zhongjiang"] = array();
            for ($i = 0; $i < count($uid); $i++) {
                if (isset($uid[$i]["userid"])) {
                    $name = M()->query("select user_name from ecs_users where user_id='" . $uid[$i]["userid"] . "' limit 1");
                    $name = $name[0]["user_name"];
                    if (isset($name) && str_len($name) > 13) {
                    } else {
                        $name = "wx_15051802" . rand(1000, 9999);
                    }
                    $nuid["zhongjiang"][$i]["name"] = model('Super')->hideStr($name, 7, 5);
                } else {
                    $name = "wx_15051802" . rand(1000, 9999);
                    $nuid["zhongjiang"][$i]["name"] = model('Super')->hideStr($name, 7, 5);
                }
            }
        }
        die(json_encode($nuid));
    }
}