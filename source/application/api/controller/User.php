<?php

namespace app\api\controller;

use app\api\model\OutlineOrder;
use app\api\model\statements\UserPointStatements;
use app\api\model\User as UserModel;
use app\api\model\Wxapp as WxappModel;
use app\api\model\WxappPrepayId as WxappPrepayIdModel;
use app\common\library\wechat\WxPay;
use app\api\model\UserExchange;
use think\Db;

/**
 * 用户管理
 * Class User
 * @package app\api
 */
class User extends Controller
{
    /**
     * 用户自动登录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login()
    {
        $model = new UserModel;
        return $this->renderSuccess([
            'user_id' => $model->login($this->request->post()),
            'token' => $model->getToken()
        ]);
    }

    private $exchange_status = [
        'no-exchange' => 0,
        'exchange' => 1
    ];

    /**
     * 用户兑换记录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exchangeLists($dataType,$listRows = 15)
    {
        $user = $this->getUser();

        $model = new UserExchange();

        $sort = ['create_time'=>'desc'];

        $lists = $model->where([
            'user_id' => $user['user_id'],
            'is_used' => $this->exchange_status[$dataType],
        ])->order($sort)->with(['goods','goods.image.file'])
            ->paginate($listRows, false, [
                'query' => \request()->request()
            ]);
        return $this->renderSuccess([
            'list'=>$lists
        ]);
    }

    /**
     * 用户线下支付
     */
    public function outlinePay()
    {
        $user = $this->getUser();
        $data = $this->request->post();
        if(!$data['shop_id'] && !$data['total_price']){
            return $this->renderError('缺少必要参数');
        }
        $outlineOrderModel = new OutlineOrder();
        //创建线下付款订单
        if($outlineOrderModel->createOrder($user['user_id'],$data)){
            // 发起微信支付
            return $this->renderSuccess([
                'payment' => $this->unifiedorder($outlineOrderModel),
                'order_id' => $outlineOrderModel['id']
            ]);
        }
    }

    /**
     * 构建微信支付
     * @param $order
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    private function unifiedorder($order)
    {
        $user = $this->getUser();
        // 统一下单API
        $wxConfig = WxappModel::getWxappCache();
        $WxPay = new WxPay($wxConfig);
        $payment = $WxPay->unifiedorder($order['order_no'], $user['open_id'], $order['pay_price'],'outline');
        // 记录prepay_id 线下付款
        $model = new WxappPrepayIdModel;
        $model->add($payment['prepay_id'], $order['id'], $user['user_id'], 30);
        return $payment;
    }

    /**
     * 用户积分明细
     */
    public function pointsInfo()
    {
        $user = $this->getUser();
        $model = new UserPointStatements();
        return $this->renderSuccess([
            'list' => $model->getList($user['user_id']),
        ]);
    }

}
