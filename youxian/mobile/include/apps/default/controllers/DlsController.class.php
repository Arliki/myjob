<?php
defined('IN_ECTOUCH') or die('Deny Access');

class DlsController extends CommonController {
    protected $dls_id;
    protected $action;
    protected $back_act;

    public function __construct()
    {
        parent::__construct();
        $this->dls_id = intval($_SESSION['dls_id']);
        $this->action = ACTION_NAME;
        $this->check_login();
    }
    private function get_dls_info(){
        if($this->dls_id>0){
            $sql = "SELECT d.*,wu.headimgurl AS avatar_url,wu.nickname FROM ecs_dls AS d LEFT JOIN ecs_wechat_user AS wu ON d.openid=wu.openid WHERE d.dls_id=".$this->dls_id." LIMIT 1";
            $dls = self::$db->getRow($sql);
            return $dls;
        }else{
            return false;
        }

    }
    private function check_login() {

        if(empty($_SESSION['dls_id']) && is_wechat_browser() && isset($_SESSION['openid'])){
            $sql = "SELECT * FROM ecs_dls WHERE openid='".$_SESSION['openid']."' LIMIT 1";
            $dls = self::$db->getRow($sql);
            if($dls && $dls['is_validated']){
                $_SESSION['dls_id'] = $dls['dls_id'];
                $shop_uid = self::$db->getOne("SELECT ect_uid FROM ecs_wechat_user WHERE openid='".$_SESSION['openid']."' LIMIT 1");
                if($shop_uid){
                    $user = self::$db->getRow("SELECT user_rank,jjr_id,dls_id FROM ecs_users WHERE user_id='$shop_uid'");
                    if($user && !($user['user_rank']==6 && $user['jjr_id']==$shop_uid && $user['dls_id']==$dls['dls_id'])){
                        self::$db->query("UPDATE ecs_users SET user_rank=6,jjr_id='$shop_uid',dls_id='".$dls['dls_id']."' WHERE user_id='$shop_uid'");
                    }
                }

                $this->redirect(url('dls/index'));
                exit();
            }
        }
        // 不需要登录的操作或自己验证是否登录（如ajax处理）的方法
        $without = array(
            'login', 'logout','register'
        );
        // 未登录处理
        if (empty($_SESSION['dls_id']) && !in_array($this->action, $without)) {
            if(IS_AJAX){
                echo json_encode(array('error'=>4,'data'=>'请登录'));exit();
            }
            $url = __HOST__ . $_SERVER['REQUEST_URI'];
            $this->redirect(url('login', array(
                'referer' => urlencode($url)
            )));
            exit();
        }

        // 已经登录，不能访问的方法
        $deny = array(
            'login'
        );
        if (isset($_SESSION['dls_id']) && $_SESSION['dls_id'] > 0 && in_array($this->action, $deny)) {
            $this->redirect(url('dls/index'));
            exit();
        }
    }

    public function login() {
        if(IS_POST){
            $phone = I('post.phone');
            $password = I('post.password');
            if (preg_match('/^1(\d){10}$/', $phone)) {
                $where['phone'] = $phone;
            }else{
                echo json_encode(array('success'=>0,'info'=>'手机号格式不正确'));die;
            }
            $sql = "SELECT * FROM ecs_dls WHERE phone=$phone LIMIT 1";
            $dls = self::$db->getRow($sql);

            if($dls){
                if(!$dls['is_validated']){
                    echo json_encode(array('success'=>0,'info'=>'请等待管理员审核'));die;
                }
                if(sha1($password.$dls['salt']) === $dls['password']){
                    if($_SESSION['openid']){
                        if(!$dls['openid']){
                            //首次登陆，代理商绑定微信openid
                            $update_sql = "UPDATE ecs_dls SET openid='".$_SESSION['openid']."' WHERE dls_id='".$dls['dls_id']."'";
                            $update_openid = self::$db->query($update_sql);
                            if(!$update_openid){
                                echo json_encode(array('success'=>0,'绑定微信失败'));die;
                            }
                        }else{
                            //取第一次绑定openid
                            $_SESSION['openid'] = $dls['openid'];
                        }
                        //更改商城会员为代理商自己的经纪人
                        $user_id = self::$db->getOne("SELECT ect_uid FROM ".self::$ecs->table('wechat_user')." WHERE openid='".$_SESSION['openid']."' LIMIT 1");
                        $user = self::$db->getRow("SELECT user_rank,jjr_id,dls_id FROM ".self::$ecs->table('users')." WHERE user_id='$user_id' LIMIT 1");
                        if($user){
                            self::$db->query("UPDATE ".self::$ecs->table('users')." SET user_rank='6',jjr_id='$user_id',dls_id='".$dls['dls_id']."' WHERE user_id='$user_id'");
                        }
                    }

                    $_SESSION['dls_id'] = $dls['dls_id'];
                    $jump_url = url('index');
                    echo json_encode(array('success'=>1, 'info'=>'登陆成功', 'redirect_uri'=>$jump_url));die;
                    //$this->redirect($jump_url);
                }
                echo json_encode(array('success'=>0, 'info'=>'用户名或密码错误'));die;
            }else{
                echo json_encode(array('success'=>0, 'info'=>'代理商不存在'));die;
            }
            exit();
        }
        // 登录页面显示
        $khh = array(
            array('code'=>'103100000026','name'=>'中国农业银行'),
            array('code'=>'105100000017','name'=>'中国建设银行'),
            array('code'=>'102100099996','name'=>'中国工商银行'),
            array('code'=>'308584000013','name'=>'招商银行'),
            array('code'=>'302100011000','name'=>'中信银行'),
            array('code'=>'303100000006','name'=>'中国光大银行')
        );
        $khh_province = array (
            0 => '北京市',
            1 => '上海市',
            2 => '广东省',
            3 => '安徽省',
            4 => '重庆市',
            5 => '福建省',
            6 => '甘肃省',
            7 => '广西自治区',
            8 => '贵州省',
            9 => '海南省',
            10 => '河北省',
            11 => '河南省',
            12 => '黑龙江省',
            13 => '湖北省',
            14 => '湖南省',
            15 => '江苏省',
            16 => '江西省',
            17 => '吉林省',
            18 => '辽宁省',
            19 => '内蒙古自治区',
            20 => '宁夏自治区',
            21 => '青海省',
            22 => '山东省',
            23 => '山西省',
            24 => '陕西省',
            25 => '四川省',
            26 => '天津市',
            27 => '新疆自治区',
            28 => '西藏自治区',
            29 => '云南省',
            30 => '浙江省',
        );
        $this->assign('khh', $khh);
        $this->assign('khh_province', $khh_province);
        $this->display('dls_login.dwt');
    }

    public function register(){
        if(IS_POST){
            $phone = I('post.phone','','trim');
            if(!preg_match('/^1\d{10}$/', $phone)){
                echo json_encode(array('success'=>0,'info'=>'手机号码格式不正确'));die;
            }
            $sql = "SELECT dls_id FROM ".$this->model->pre."dls WHERE phone='$phone'";
            $phoneExists =self::$db->getOne($sql);
            if($phoneExists){
                echo json_encode(array('success'=>0, 'info'=>'手机号码已存在'));die;
            }
            $bank_card = I('post.bank_card','','trim');
            if(!preg_match('/^\d+$/', $bank_card)){
                echo json_encode(array('success'=>0, 'info'=>'银行卡格式不正确'));die;
            }
            $username = I('post.account_name','','trim');
            $password = I('post.password','','trim');
            $salt = mt_rand(100000,999999);
            $password = sha1($password.$salt);

            $bankinfo = explode('|', I('post.bank_info','','trim'));

            $province = I('post.province','','trim');
            $city = I('post.city','','trim');

//            $data = array(
//                'username'=>$username,
//                'password'=>$password,
//                'salt'=>$salt,
//                'phone'=>$phone,
//                'addtime'=>time(),
//                'admin_id'=>1,
//                'dls_bank_card'=>$bank_card,
//                'dls_account_name'=>$username,
//                'dls_bank_name'=>$bankinfo[1],
//                'dls_bank_province'=>$province,
//                'dls_bank_city'=>$city,
//                'dls_bank_code'=>$bankinfo[0]
//            );
            $sql = "INSERT INTO " . $this->model->pre . "dls(`username` ,`password`, `salt`, `phone`, `addtime`, `admin_id`, `dls_bank_card`, `dls_account_name`, `dls_bank_name`, `dls_bank_province`, `dls_bank_city`, `dls_bank_code`) VALUES ('$username', '$password', '$salt', '$phone', ".time().", 1, '$bank_card', '$username', '$bankinfo[1]', '$province', '$city', '$bankinfo[0]')";

            if($dls_id = $this->model->query($sql)){
                echo json_encode(array('success'=>1, 'info'=>'已注册,请等待管理员审核'));die;
            }else{
                echo json_encode(array('success'=>0, 'info'=>'申请失败,请联系客服'));die;
            }
        }
        echo json_encode(array('success'=>0, 'info'=>'请求错误'));die;
    }

    public function logout() {
        $_SESSION['dls_id'] = null;
        show_message('已退出', '' , url('login'));
    }

    /**
     * 个人资料
     */
    public function profile() {
        if(IS_POST){
//            var_dump($_POST);
        }
        $this->display('dls_profile.dwt');
    }

    /**
     * 修改密码
     */
    public function change_password() {
        if(IS_POST){
            $old_password = I('post.old_password');
            $password = I('post.password');
            if(!$old_password){
                echo json_encode(array('success'=>0,'info'=>'请输入原始密码'));die();
            }
            if(!$password){
                echo json_encode(array('success'=>0,'info'=>'请输入新密码'));die();
            }
            $dls = self::$db->getRow("SELECT * FROM ecs_dls WHERE dls_id=".$this->dls_id);
            if(sha1($old_password.$dls['salt']) != $dls['password']){
                echo json_encode(array('success'=>0,'info'=>'原始密码不正确'));die();
            }
            $res = self::$db->query("UPDATE ecs_dls SET password='".sha1($password.$dls['salt'])."' WHERE dls_id=".$this->dls_id);
            if(!$res){
                echo json_encode(array('success'=>0,'info'=>'更新密码失败,请稍后重试'));die();
            }
            echo json_encode(array('success'=>1,'info'=>'密码已更改,下次请使用新密码登录'));die();
        }
        $this->display('dls_change_password.dwt');
    }
    /**
     * 提现申请
     */
    public function out_money(){
        if(IS_POST){
            var_dump($_POST);
            die();
        }
        $sql = "SELECT * FROM ecs_dls WHERE dls_id=".$this->dls_id." LIMIT 1";
        $dls = self::$db->getRow($sql);
        $this->assign('dls', $dls);
        $this->display('dls_out_money.dwt');
    }
    /**
     * 代理商首页
     */
    public function index() {
        $team_num = self::$db->getOne("SELECT COUNT('user_id') FROM ecs_users WHERE dls_id='".$this->dls_id."'");
        $jjr_num = self::$db->getOne("SELECT COUNT('user_id') FROM ecs_users WHERE user_rank=6 AND dls_id='".$this->dls_id."'");
        $reguser_num = self::$db->getOne("SELECT COUNT('user_id') FROM ecs_users WHERE user_rank!=6 AND dls_id='".$this->dls_id."'");
        $this->assign('team_num', $team_num);
        $this->assign('jjr_num', $jjr_num);
        $this->assign('reguser_num', $reguser_num);
//        $condition_shipping = 'AND (shipping_status=1 OR shipping_status=2)';
        $total_sale = self::$db->getOne("SELECT SUM(goods_amount) FROM ecs_order_info WHERE dls_id='".$this->dls_id."' AND pay_status=2 ");
        $last_jiesuan_time = self::$db->getOne("SELECT end_day FROM ".self::$ecs->table('dls_jiesuan')." WHERE dls_id='".$this->dls_id."' AND status=1 ORDER BY id DESC LIMIT 1");
        if($last_jiesuan_time){
            $start_time = strtotime($last_jiesuan_time.' + 1 day');
        }else{
            $start_time = 0;
        }

        $daijiesuan_sql = "SELECT SUM(goods_amount) FROM ".self::$ecs->table('order_info')." WHERE dls_id='".$this->dls_id."' AND pay_time>'$start_time' AND pay_status=2 ";
        $daijiesuan = self::$db->getOne($daijiesuan_sql);
        $yitixian = self::$db->getOne("SELECT SUM(amount) FROM ".self::$ecs->table('dls_jiesuan')." WHERE dls_id='".$this->dls_id."' AND status=1");

        $this->assign('total_sale', $total_sale?$total_sale:0.00);
        $this->assign('daijiesuan', $daijiesuan?$daijiesuan:0.00);
        $this->assign('yitixian', $yitixian?$yitixian:0.00);
        $this->assign('dls', $this->get_dls_info());

        $this->display('dls_index.dwt');
    }

    /**
     * 结算列表
     */
    public function jiesuan_list(){
        $jiesuan_list = self::$db->getAll("SELECT * FROM ".self::$ecs->table('dls_jiesuan')." WHERE dls_id='".$this->dls_id."' AND status=1 ORDER BY id DESC");
        foreach ($jiesuan_list as $k=>$v){
            $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $v['amount'] = price_format($v['amount']);
            $jiesuan_list[$k] = $v;
        }
        $this->assign('jiesuan_list', $jiesuan_list);
        $this->display('dls_jiesuan_list.dwt');
    }

    /**
     * 推广二维码
     */
    public function sence_qrcode(){
        $dls = $this->get_dls_info();
        $qrcode = WechatController::rec_qrcode($dls['username'], 'D'.$this->dls_id, 0, '代理商', 2);
        if($qrcode){
            echo json_encode(array('success'=>1,'imgurl'=>$qrcode));die();
        }else{
            echo json_encode(array('success'=>0,'info'=>'获取二维码失败'));die();
        }
    }

    /**
     * 经纪人列表
     */
    public function jjr_list(){
        $jjr_ids = self::$db->getAll("SELECT user_id FROM ".self::$ecs->table('users')." WHERE dls_id='".$this->dls_id."' AND user_rank=6");
        if($jjr_ids){
            $jjr_id = array_column($jjr_ids, 'user_id', null);
            $jjr_id = implode(',', $jjr_id);
            $jjr_list = self::$db->getAll("SELECT ect_uid,nickname,headimgurl FROM ".self::$ecs->table('wechat_user')." WHERE ect_uid IN (".$jjr_id.")");
            foreach ($jjr_list as $k=>$v){
                $v['reguser_num'] = self::$db->getOne("SELECT COUNT(user_id) FROM ".self::$ecs->table('users')." WHERE jjr_id='".$v['ect_uid']."'");
                $v['total_sale'] = self::$db->getOne("SELECT IFNULL(SUM(goods_amount),0) FROM ".self::$ecs->table('order_info')." WHERE pay_status=2  AND jjr_id='".$v['ect_uid']."'");
                $v['jjr_url'] = url('dls/jjr_profile',array('jid'=>$v['ect_uid']));
                $jjr_list[$k] = $v;
            }

        }else{
            $jjr_list = array();
        }
        $this->assign('jjr_list', $jjr_list);
        $this->display('dls_jjr_list.dwt');
    }

    /**
     * 经纪人页面
     */
    public function jjr_profile(){
        $jid = I('get.jid');
        $jjr = $this->get_jjr_info($jid);
        $jjr_info = array(
            'info'=>$jjr,
            'reguser_num' => self::$db->getOne("SELECT COUNT(user_id) FROM ".self::$ecs->table('users')." WHERE jjr_id='".$jid."'"),
            'total_sale' => self::$db->getOne("SELECT SUM(goods_amount) FROM ".self::$ecs->table('order_info')." WHERE pay_status=2 AND jjr_id='".$jid."'")
        );
        $this->assign('jjr_info', $jjr_info);
        $this->display('dls_jjr_profile.dwt');
    }

    /**
     * 检测经纪人是否属于该代理商
     * @param $dls_id 代理商id
     * @param $jjr_id 经纪人id
     * @return bool
     */
    private function check_dls_jjr($dls_id, $jjr_id){
        return $dls_id == self::$db->getOne("SELECT dls_id FROM ".self::$ecs->table('users')." WHERE user_id='$jjr_id' AND user_rank=6");
    }

    /**
     * 获取经纪人资料
     * @param $jid
     * @return mixed
     */
    private function get_jjr_info($jid){
        if(!$jid){
            show_message('缺少参数');
        }
        if(!$this->check_dls_jjr($this->dls_id, $jid)){
            show_message('参数有误,经纪人不属于你');
        }
        return self::$db->getRow("SELECT ect_uid,nickname,headimgurl FROM ".self::$ecs->table('wechat_user')." WHERE ect_uid='".$jid."'");
    }

    /**
     * 异步获取我的推荐会员
     */
    public function get_jjr_reguser(){
        $jid = I('get.jid');
        if(!$this->check_dls_jjr($this->dls_id, $jid)){
            echo json_encode(array('error'=>1,'data'=>'参数有误,经纪人不属于你'.$this->dls_id.'--'.$jid));exit();
        }
        $start = I('get.start');
        $num = I('get.num');

        $res = self::$db->getAll("SELECT wu.nickname,wu.headimgurl,from_unixtime(u.reg_time,'%Y/%m/%d') AS addtime FROM ".self::$ecs->table('wechat_user')." AS wu LEFT JOIN ".self::$ecs->table('users')." AS u ON wu.ect_uid=u.user_id WHERE u.jjr_id='".$jid."' OR (u.user_rank=6 AND u.user_id='$jid') LIMIT $start, $num");

        if($res){
            $arr = array('error'=>0,'data'=>$res);
        }else{
            $arr = array('error'=>1,'data'=>'没有更多数据了');
        }
        echo json_encode($arr);exit();
    }
    /**
     * 异步获取会员消费记录
     */
    public function get_jjr_order(){
        $jid = I('get.jid');
        if(!$this->check_dls_jjr($this->dls_id, $jid)){
            echo json_encode(array('error'=>1,'data'=>'参数有误,经纪人不属于你'.$this->dls_id.'--'.$jid));exit();
        }
        $start = I('get.start');
        $num = I('get.num');

        $res = self::$db->getAll("SELECT wu.nickname,wu.headimgurl,from_unixtime(o.pay_time,'%Y/%m/%d') AS buy_time,o.order_sn,o.goods_amount,o.shipping_status FROM ".self::$ecs->table('wechat_user')." AS wu JOIN ".self::$ecs->table('order_info')." AS o ON wu.ect_uid=o.user_id WHERE o.pay_status=2 AND  o.jjr_id='".$jid."' ORDER BY o.pay_time DESC LIMIT $start, $num");

        if($res){
            $arr = array('error'=>0,'data'=>$res);
        }else{
            $arr = array('error'=>1,'data'=>'没有更多数据了');
        }
        echo json_encode($arr);exit();
    }

    /**
     * 销售统计
     */
    public function sale_tj(){
        $type = I('get.type') || 'jjr';
        if($type=='jjr'){
            $jid = I('get.jid');
            $avatar = $this->get_jjr_info($jid);
            $this->assign('title','经纪人销售统计');
        }
        $this->assign('avatar', $avatar);
        $this->display('dls_sale_tj.dwt');
    }

    /**
     * 异步获取销售统计
     */
    public function get_sale_list(){
        $type = I('get.type') || 'jjr';
        $where = ' 1';
        if($type=='jjr'){
            $jid = I('get.jid');
            $where .= ' AND jjr_id='.$jid;
        }
        $start_day = I('start_day');
        $end_day = I('end_day');
        $start_time = strtotime($start_day);
        $end_time = strtotime($end_day." + 1 day");
        $where .= " AND shipping_time>='$start_time' AND shipping_time<'$end_time' AND pay_status=2 ";

        $total_cost = self::$db->getOne("SELECT SUM(goods_amount) FROM ".self::$ecs->table('order_info')." WHERE ".$where);
        $join_where = preg_replace('/AND /','AND o.', $where);
        $res = self::$db->getAll("SELECT wu.headimgurl,wu.nickname,o.order_id,o.order_sn,from_unixtime(o.pay_time,'%Y/%m/%d') AS pay_time,o.goods_amount FROM ".self::$ecs->table('order_info')." AS o LEFT JOIN ".self::$ecs->table('wechat_user')." AS wu ON o.user_id=wu.ect_uid WHERE ".$join_where." ORDER BY o.pay_time DESC");

        if($res){
            $arr = array('error'=>0,'total_sale'=>$total_cost,'data'=>$res);
        }else{
//            $arr = array('error'=>1,'data'=>'没有更多数据了');
            $arr = array('error'=>0,'total_sale'=>0.00,'data'=>$res);

        }
        echo json_encode($arr);exit();
    }

    /**
     * 发起代理商缴纳诚意金申请，并调起微信支付
     */
    public function pay_deposit() {
        $user_id = 0;
        if(is_wechat_browser() && isset($_SESSION['openid'])){
            $openid = $_SESSION['openid'];
        }else{
            $openid = self::$db->getOne('SELECT openid FROM '.self::$ecs->table('dls').' WHERE dls_id='.$this->dls_id);
            $_SESSION['openid'] = $openid;
        }
        if($openid){
            $user_id = self::$db->getOne("SELECT ect_uid FROM ".self::$ecs->table('wechat_user')." WHERE openid='$openid'");
        }
        if(!$user_id){
            sys_msg('没有找到关联的商城用户');
        }

        $amount = C('dls_deposit');
        $payment_id = 1; //微信支付
        $payment_info = model('Order')->payment_info($payment_id);

        $surplus = array(
            'user_id'=>$user_id,
            'user_note'=>'代理商缴纳诚意金'.$amount,
            'admin_note'=>'代理商缴纳诚意金'.$amount,
            'process_type'=>0, //预付费(充值)
            'payment'=>$payment_info['pay_name']
        );
        $payment = unserialize_config($payment_info['pay_config']);
        //记录会员资金账户变动
        $surplus['rec_id'] = model('ClipsBase')->insert_user_account($surplus, $amount);
        //记录支付id
        $log_id = model('ClipsBase')->insert_pay_log($surplus['rec_id'], $amount, $type=PAY_DLS_DEPOSIT, 0);
        //取得支付信息，生成支付代码

        /* 调用相应的支付方式文件 */
        include_once (ROOT_PATH . 'plugins/payment/' . $payment_info['pay_code'] . '.php');
        /* 取得在线支付方式的支付按钮 */
        $pay_obj = new $payment_info['pay_code']();
        $od = array(
            'total_amount'=>$amount,
            'log_id'=>$log_id,
            'body'=>'代理商诚意金缴纳',
            'ids'=>'',
            'pay_from'=>'dls_deposit',
            'attach'=>array(
                'dls_id'=>$this->dls_id
            )
        );
        $auto_call_js='<script>callpay();</script>';
        $js_str = $pay_obj->get_code($od, $payment).$auto_call_js;
        echo json_encode(array('success'=>1,'js'=>$js_str));die;
    }
}