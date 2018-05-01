<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/19
 * Time: 17:46
 */
defined('IN_ECTOUCH') or die('Deny Access');
class GysModel extends Model
{
    /**
     * 通过订单id获取供应商资料
     * @param $order_id
     * @return mixed
     */
    public function getAgencyByOrderId($order_id)
    {
        $this->table = 'order_info';
        $agency_id = $this->field('agency_id', "order_id='$order_id'");
        $this->table = 'agency';
        $agency = $this->find("agency_id='$agency_id'", '*');
        return $agency;
    }

    /**
     * 获取一个商品的供货商供应价
     * @param $goods_id
     * @param $attr_id
     * @return mixed
     */
    public function get_goods_gys_price($goods_id, $attr_id)
    {
        $this->table = 'goods';
        $final_price = $this->field('shop_price', "goods_id='$goods_id'");

        $where = "goods_id=".intval($goods_id)." AND ";
        $where .= db_create_in($attr_id, 'goods_attr_id');
        $this->table = 'goods_attr';
        $res = $this->field('SUM(attr_gys_price)', $where);

        return $final_price + $res;
    }

    /**
     * 获取一个订单的供应商供应价
     * @param $order_id
     * @return float
     */
    public function get_order_gys_price($order_id)
    {
        if(!$order_id){
            return 0.00;
        }
        $this->table = 'order_goods';

        $gys_price = $this->field("SUM(gys_price*goods_number)", "order_id='$order_id'");
        if($gys_price>0){
            return $gys_price;
        }

        $goods = $this->select("order_id=".intval($order_id), "goods_id,goods_attr_id,goods_number");

        $gys_price = 0.00;
        foreach ($goods as $k=>$v) {
            $gys_amount = $this->get_goods_gys_price($v['goods_id'], $v['goods_attr_id']);
            $gys_price += $gys_amount*$v['goods_number'];
        }
        return $gys_price;

    }

    /**
     * 微信支付,完成交易状态的订单，供应商首期款利润分配
     * @param $log_id 微信支付交易id pay_log表log_id
     */
    public function wxpayInAccount($log_id) {
        $this->table = 'pay_log';
        $order_id = $this->field("order_id", "log_id='$log_id' AND order_type=0");
        $this->inAccount($order_id);
    }

    /**
     * 供应商入账首付款[预付款方式(既顾客下单付款后,供应商入账首付款)]
     * @param $order_id 订单id
     * @return string
     */
    public function inAccount($order_id) {
//        $this->table = 'pay_log';
//        $order_id = $this->field("order_id", "log_id='$log_id'");
        $agency = $this->getAgencyByOrderId($order_id);
        if(!$agency || $agency['agency_pay_type']==1){
            return '';
        }

        $gys_price = $this->get_order_gys_price($order_id);
        $inMoney = round($gys_price*$agency['agency_first_percent']/100, 2);
        $data = array(
            'gys_id'=>$agency['agency_id'],
            'order_id'=>$order_id,
            'amount'=> $inMoney,
            'add_time'=>time(),
            'process_type'=> '首期款'.$agency['agency_first_percent'].'%',//入账类型(0:用户下单并支付,供应商收益70%,1用户确认收货供应商收益30%)
            'is_paid'=>1
        );

        $this->query('SET AUTOCOMMIT=0');

        $this->table = 'admin_user';
        $gys = $this->find("agency_id='".$agency['agency_id']."'", 'user_id,gys_account');
        $gys_account = $gys['gys_account']+$inMoney;
        $gys_adminid = $gys['user_id'];

        $this->table = 'gys_earn_log';
        $insert_id = $this->insert($data);

        $this->table = 'admin_user';
        $update_account = $this->update("user_id='$gys_adminid'", array("gys_account"=>$gys_account));

        if($insert_id && $update_account){
            $this->query('COMMIT');
//            file_put_contents('transaction_test.log', "TransSuccess\r\n", FILE_APPEND);
        }else{
            $this->query('ROLLBACK');
            file_put_contents('GysAccountError.log', date('Y-m-d H:i:s', time())."TransError::orderid-".$order_id."\r\n", FILE_APPEND);
        }
//        $this->query('END');
        $this->query("SET AUTOCOMMIT=1");
        return '';
    }

    /**
     * 供应商尾款入账
     * @param $order_id
     * @return string
     */
    public function inAccountSec($order_id)
    {
        $agency = $this->getAgencyByOrderId($order_id);
        if(!$agency){
            return '';
        }
        $gys_price = $this->get_order_gys_price($order_id);

        $this->table = 'gys_earn_log';
        //该订单已入账首期款信息
        $firstMoney = $this->find("gys_id='$agency[agency_id]' AND order_id='$order_id' AND f_step='0'", "id,amount,add_time,is_paid");
        $secMoney = $this->find("gys_id='$agency[agency_id]' AND order_id='$order_id' AND f_step='1'", "id,amount,add_time,is_paid");
        if($firstMoney && $secMoney){
            return '';
        }
        if($firstMoney['amount']){
            //有首期款入账记录
            $inMoney = (intval($gys_price*100) - intval($firstMoney['amount']*100))/100;
            $data = array(
                'gys_id'=>$agency['agency_id'],
                'order_id'=>$order_id,
                'amount'=> $inMoney,
                'add_time'=>time(),
                'process_type'=> '尾款'.(100-$agency['agency_first_percent']).'%',//入账类型(0:首期款,1尾款)
                'is_paid'=>1,
                'f_step'=>1
            );
            file_put_contents('gysPayFlowError.log', date('Y-m-d H:i:s', time())."Error::firstMoney:".$firstMoney['amount'].'inMoney:'.$inMoney.' order_id:'.$order_id."\r\n", FILE_APPEND);
        }else{
            //没有首期款结算记录
            if($agency['agency_pay_type'] == 0){
                //如果供应商结算方式是下单后结算首期款,记录错误
                file_put_contents('GysAccountError.log', date('Y-m-d H:i:s', time())."LogicError::首期款有误,直接进入尾款流程orderid-".$order_id."\r\n", FILE_APPEND);
                return '';
            }else{
                //如果供应商结算方式是发货后结算首期款,生成首期款和尾款结算记录
                $firstInMoney = round($gys_price*$agency['agency_first_percent']/100, 2);
                $secInMoney = (intval($gys_price*100) - intval($firstInMoney*100))/100;
                $inMoney = $gys_price;
                $data = array(
                    array(
                        'gys_id'=>$agency['agency_id'],
                        'order_id'=>$order_id,
                        'amount'=> $firstInMoney,
                        'add_time'=>time(),
                        'process_type'=> '顾客确认订单,首期款'.$agency['agency_first_percent'].'%',//入账类型(0:首期款,1尾款)
                        'is_paid'=>1,
                        'f_step'=>0
                    ),
                    array(
                        'gys_id'=>$agency['agency_id'],
                        'order_id'=>$order_id,
                        'amount'=> $secInMoney,
                        'add_time'=>time(),
                        'process_type'=> '顾客确认订单,尾款'.(100-$agency['agency_first_percent']).'%',//入账类型(0:首期款,1尾款)
                        'is_paid'=>1,
                        'f_step'=>1
                    )
                );
            }
        }

        if($inMoney == 0){
            //如果尾款为0,不走事务流程,直接增加入账记录，否则事务处理供应商资金总额和入账记录
            $this->table = 'gys_earn_log';
            $this->insert($data);
        }else{
            $this->query('SET AUTOCOMMIT=0');

            $this->table = 'admin_user';
            $gys = $this->find("agency_id='".$agency['agency_id']."'", 'user_id,gys_account');
            $gys_account = $gys['gys_account']+$inMoney;
            $gys_adminid = $gys['user_id'];

            $this->table = 'gys_earn_log';
            if(count($data) == 2){
                $insert_id1 = $this->insert($data[0]);
                $insert_id2 = $this->insert($data[1]);
                $insert_id = $insert_id1 && $insert_id2;
            }else{
                $insert_id = $this->insert($data);
            }

            $this->table = 'admin_user';
            $update_account = $this->update("user_id='$gys_adminid'", array("gys_account"=>$gys_account));

            if($insert_id && $update_account){
                $this->query('COMMIT');
            }else{
                $this->query('ROLLBACK');
                file_put_contents('GysAccountError.log', date('Y-m-d H:i:s', time())."TransError::orderid-".$order_id."\r\n", FILE_APPEND);
            }
            $this->query("SET AUTOCOMMIT=1");
        }

    }
}