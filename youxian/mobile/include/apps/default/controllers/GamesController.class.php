<?php
defined('IN_ECTOUCH') or die('Deny Access');

class GamesController extends CommonController {

    public $user_info;

    public function __construct()
    {
        parent::__construct();
        if(!is_wechat_browser()){
            show_message('请在微信浏览器内打开');
        }
        if(!$_SESSION['user_id']){
            $this->redirect(url('user/login'));
        }else{
            $user_info = model('Game')->get_user_info($_SESSION['user_id']);
            if(!$user_info){
                show_message('没找到用户信息');
            }
            $this->user_info = $user_info;
            $this->assign('user_info', $this->user_info);
            $buy_guodou_url = url('category/index',array('id'=>128));
            $this->assign('buy_guodou_url', $buy_guodou_url);
        }
    }

    /**
     * 游戏首页
     */
    public function index(){
        $id = I('get.id',0,'intval');
        if(!$id){
            //列表页
            $games = model('Game')->get_games(true);
            foreach($games as $k=>$v){
                $v['thumb'] = __ADDONS__.'/games/'.$v['name'].'/thumb.jpg';
                $v['url'] = url('games/index',array('id'=>$v['id']));
                $games[$k] = $v;
            }
            $agreed_txt = model('Game')->agreed_txt();
            $this->assign('agreed_txt', $agreed_txt);

            $user_valid = $this->user_info['user_validate'];
            $this->assign('mobile_validated', $user_valid & USER_MOBILE_VALID);

            $this->assign('games', $games);
            $this->display('games/game_list.dwt');
        }else{
            //游戏页
            $game = model('Game')->get_game_info($id);
            if($game['disabled']){
                show_message('游戏开发维护中,敬请期待...');
            }else{
                $this->assign('awards_url',url('awards_list',array('game_id'=>$id)));
                $this->assign('game', $game);
                $this->assign('page_title', C('shop_name').' '.$game['name']);
                $this->assign('game_path', __ADDONS__.'/games/'.$game['name']);
                $this->game_display($game['name'].'/'.$game['name'].'.html');
            }
        }
    }

    /**
     * 获取验证码
     */
    public function get_verify_code()
    {
        $phone = I('phone','','trim');
        if(!preg_match('/^1\d{10}$/', $phone)){
            echo json_encode(array('success'=>0, 'info'=>'手机号码格式不正确'));die;
        }
        $phoneExists = model('Game')->phone_exist($phone);
        if($phoneExists){
            echo json_encode(array('success'=>0, 'info'=>'手机号码已存在'));die;
        }
        $code = mt_rand(100000,999999);
        require_once BASE_PATH . 'libraries/ClSms.class.php';
        $clapi = new ClSms();
        $content = "【讯罗优选】验证码：".$code."（10分钟内有效），如非本人操作，请忽略本短信";
        $res = $clapi->sendSms($phone,$content);
        $res = $res['returnsms'];
        $sms_data = array(
            'id'=>'',
            'taskid'=>$res['taskID'],
            'phone'=>$phone,
            'content'=>$content,
            'status'=>($res['returnstatus']=='Success'?1:0),
            'addtime'=>time()
        );
        $this->model->table('sms_log')->data($sms_data)->insert();
        if($sms_data['status'] == 1){
            //发送成功
            $_SESSION['game_valid_phone'] = $phone;
            $_SESSION['game_valid_code'] = $code;
            $_SESSION['game_valid_deadline'] = time()+600;
            echo json_encode(array('success'=>1,'info'=>'已发送,请查收'));die;
        }else{
            echo json_encode(array('success'=>0, 'info'=>'发送失败'));die;
        }

    }

    /**
     * 校验验证码,并更新用户绑定手机号
     */
    public function verify_code()
    {
        $phone = I('phone','','trim');
        $code = I('code','','trim');
        if(!preg_match('/^1\d{10}$/', $phone) || !preg_match('/^\d{6}$/', $code)){
            echo json_encode(array('success'=>0, 'info'=>'手机号码或验证码格式不正确'));die;
        }
        if($phone==$_SESSION['game_valid_phone'] && $code==$_SESSION['game_valid_code']){
            if(time()>$_SESSION['game_valid_deadline']){
                echo json_encode(array('success'=>0, 'info'=>'验证码已失效'));die;
            }
            $res = model('Game')->update_user_phone($phone);
            if($res['success']){
                echo json_encode(array('success'=>1, 'info'=>'操作成功'));die;
            }else{
                echo json_encode(array('success'=>0, 'info'=>$res['info']));die;
            }
        }else{
            echo json_encode(array('success'=>0, 'info'=>'验证码不正确'));die;
        }
    }

    /**
     * 同意协议
     */
    public function agree_txt()
    {
        $agreed = model('Game')->agree_txt();
        if($agreed){
            echo json_encode(array('success'=>1));die;
        }else{
            echo json_encode(array('error'=>1));die;
        }
    }

    /**
     * 抽奖
     */
    public function play(){
        $game_id = intval($_REQUEST['id']);
        if(!$game_id){
            $arr = array('error'=>1,'info'=>'缺少游戏ID参数');
            echo json_encode($arr);die;
        }
        //验证操作，积分
        $act_id = intval($_REQUEST['act_id']);
        if($act_id){
            $game_act = model('Game')->get_game_act($act_id,$game_id);
        }else{
            $game_act = model('Game')->get_game_acts($game_id,false);
        }
        if(!$game_act){
            echo json_encode(array(
                'error'=>1,'info'=>'操作有误'
            ));die;
        }
        if($game_act['costs']>$this->user_info['guodou']){
            echo json_encode(array(
                'error'=>1,'info'=>'果豆不足,请充值'
            ));die;
        }
        //获取奖项
        $game_awards = model('Game')->get_game_awards($game_id);
        $game = model('Game')->get_game_info($game_id);
        $award = $this->getRand($game_awards,$game['base_number']); /*根据概率获取奖项id*/

        $luck_play = model('Game')->lucky_play($game,$_SESSION['user_id'],$game_act,$award);
        $fail_info = array(
            '离一等奖只差一点',
            '换个姿势继续',
            '手气差了点',
            '可以去买彩票了,这都不中...',
            '换个手再来',
            '洗洗手再来',
            '木有中...'
        );
        $lucky_info = array(
            '手气逆天啊少年',
            '可以去买彩票了,这都能中...',
            '这都能中,墙都不服就服你..',
            '赶紧刮开看看中的啥',
            '中了！！',
            '算你厉害',
            '恭喜你,中奖了！'
        );
        $rd_idx = array_rand($fail_info);
        if($luck_play['success']){
            if(!$award){
                echo json_encode(array(
                    'success'=>1,
                    'lucky'=>0,
                    'info'=>$fail_info[$rd_idx],
                    'new_guodou'=>$luck_play['new_guodou']
                ));
            }else{
                echo json_encode(array(
                    'success'=>1,
                    'lucky'=>1,
                    'info'=>$lucky_info[$rd_idx],
                    'award_title'=>$award['title'],
                    'award_img'=>$award['img'],
                    'new_guodou'=>$luck_play['new_guodou'],
                    'award_url'=>url('got_award',array('award_id'=>$luck_play['log_id']))
                ));
            }
        }else{
            echo json_encode(array('success'=>0,'lucky'=>0,'info'=>$luck_play['info']));
        }
        die;
    }

    /**
     * 领奖
     */
    public function got_award(){
        $log_id = intval($_REQUEST['award_id']);
        if(!$log_id){
            echo json_encode(array('error'=>1,'info'=>'缺少参数'));die;
        }
        $log_info = model('Game')->get_game_log($log_id);
        if(!$log_info){
            echo json_encode(array('error'=>1,'info'=>'参数有误,没找到中奖信息'));die;
        }
        if($log_info['awards_id']<=0){
            echo json_encode(array('error'=>1,'info'=>'未中奖,无法领取'));die;
        }
        if($_SESSION['user_id'] != $log_info['user_id']){
            echo json_encode(array('error'=>1,'info'=>'参数有误,没找到您的中奖信息'));die;
        }
        if($log_info['gotit']){
            echo json_encode(array('error'=>1,'info'=>'已于'.date('Y-m-d H:i:s',$log_info['gottime']).'领过奖品了'));die;
        }

        $award = model('Game')->get_award_info($log_info['awards_id']);
        if($award['awards_type']==1){
            //实物奖励,判断是否有收货地址
            $has_address = $this->model->table('user_address')->where('user_id='.$log_info['user_id'])->field('address_id')->getOne();
            if(!$has_address){
                echo json_encode(array('error'=>1,'info'=>'请先完善收货地址','url'=>url('user/address_list')));die;
            }
            $success_info = '订单已生成,坐等收货吧^_^';
        }else{
            $success_info = '果豆已发放,请查收^_^';
        }
        $got_award = model('Game')->got_award($log_info, $award);
        if($got_award['success']){
            echo json_encode(array('success'=>1,'info'=>$success_info));die;
        }else{
            echo json_encode(array('error'=>1,'info'=>'啊哦,出错了：'.$got_award['info']));die;
        }

    }

    /**
     * 中奖列表
     */
    public function awards_list(){
        $uid = $_SESSION['user_id'];
        $gid = I('game_id',0,'intval');
        if(!$gid){
            $this->redirect(url('index'));
        }
        if(IS_AJAX){
            $awards = model('Game')->get_user_awards($uid,$gid);
            foreach ($awards as $k => $v) {
                if($v['gottime']>0){
                    $v['gottime'] = date('Y-m-d H:i:s',$v['gottime']);
                }
                $v['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
                $v['lingjiang_url'] = url('got_award',array('award_id'=>$v['id']));
                $awards[$k] = $v;
            }
            if($awards){
                echo json_encode(array('success'=>1,'list'=>$awards));die;
            }
            echo json_encode(array('success'=>0,'info'=>'没有中奖纪录'));die;
        }

        $this->assign('req_url', url('awards_list',array('game_id'=>$gid)));
        $this->display('games/awards_list.dwt');


    }
    /**
     * 抽奖
     * @param $awards
     * @param $base_number
     * @return array
     */
    private function getRand($awards,$base_number) {
        /*概率数组循环*/
        $result = array();

        foreach ($awards as $k => $v) {
            /*相当于每次只能够抽取其中的一个奖项，没抽中就开始抽下一个奖项，直到最后*/
            $randNum = mt_rand(1, $base_number);
            if (intval($randNum) <= intval($v['rate'])) {
                /*表示抽中奖了，该奖品应该要减一*/
                if(!$v['limit_num']){
                    $result = $v;
                    break;
                }else{
                    //检测奖品数量,如果已经发放完,接着验证下一个奖项
                    $send_award_count = model('Game')->get_award_send_count($v['id']);
                    if($send_award_count*1<$v['rate']*1){
                        $result = $v;
                        break;
                    }
                }
            }else{
                $base_number = intval($base_number) - intval($v['rate']);
            }
        }
        return $result;
    }

    /**
     * 再玩一次
     */
    public function play_again(){
        $game_id = intval($_REQUEST['id']);
        if(!$game_id){
            $arr = array('error'=>1,'info'=>'缺少参数');
            echo json_encode($arr);die;
        }
        $act = model('Game')->get_game_acts($game_id,false);
        if($this->user_info['guodou']<$act['costs']){
            echo json_encode(array('error'=>1,'info'=>'果豆不足,请充值'));die;
        }else{
            echo json_encode(array('success'=>1));die;
        }
    }

    public function get_game_act(){
        $game_id = intval($_GET['id']);
        if(!$game_id){
            $arr = array('error'=>1,'info'=>'缺少参数');
            echo json_encode($arr);die;
        }
        $multi_act = intval($_GET['multi_act']);
        $acts = model('Game')->get_game_acts($game_id,$multi_act);
        if($acts){
            echo json_encode(array('success'=>1,'acts'=>$acts));die;
        }else{
            echo json_encode(array('error'=>1,'info'=>'未配置游戏规则,请联系管理员'));die;
        }
    }

    private function game_display($tpl){
        self::$view->cache_lifetime = C('cache_time');
        self::$view->template_dir = ADDONS_PATH . 'games/';
        self::$view->caching = false;
        self::$view->compile_dir = ROOT_PATH . 'data/caches/compiled/games';
        return $this->display($tpl);
    }



}