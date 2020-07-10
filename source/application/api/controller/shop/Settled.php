<?php


namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\api\model\dealer\Setting;
use app\api\model\store\shop\Withdraw as WithdrawModel;
use app\api\model\store\Shop;
use app\store\model\store\ShopSettled;


class Settled extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    private $dealer;
    private $setting;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 用户信息
        $this->user = $this->getUser();
        // 入驻店铺信息
        $shop = new Shop();
        $this->dealer = $shop->where(['user_id'=>$this->user['user_id']])->find();
        // 入驻店铺设置
        $this->setting = Setting::getAll(config('mini_weixin.wxapp_id'));
    }

    /**
     * 商家入驻信息
     * @return array
     */
    public function center()
    {
        return $this->renderSuccess([
            // 当前是否为入驻用户
            'is_dealer' => $this->isDealerUser(),
            // 当前用户信息
            'user' => $this->user,
            // 分销商用户信息
            'dealer' => $this->dealer,
            // 背景图
            'background' => $this->setting['background']['values']['index'],
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

    /**
     * 入驻店铺申请状态
     * @param null $referee_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function apply($referee_id = null)
    {
        return $this->renderSuccess([
            // 当前是否为分销商
            'is_dealer' => $this->isDealerUser(),
            // 当前是否在申请中
            'is_applying' => $this->isApplying(),
            // 背景图
            'background' => $this->setting['background']['values']['apply'],
            // 页面文字
            'words' => $this->setting['words']['values'],
            // 申请协议
            'license' => $this->setting['license']['values']['license'],
        ]);
    }

    /**
     * 提交入驻店铺申请
     * @param string $name
     * @param string $mobile
     * @return array
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function submit()
    {
        $model = new ShopSettled();
        $data = $this->postData();
        //查看手机号是否有重复
        $shop = $model->where(['phone'=>$data['mobile']])->find();
        if($shop){
            return $this->renderError('该手机号已经提交过申请');
        }

        $model->save([
            'user_id' => $this->user['user_id'],
            'shop_name' => $data['shop_name'],
            'linkman' => $data['name'],
            'phone' => $data['mobile']
        ]);
        return $this->renderSuccess('您已经申请，请耐心等待');
    }

    /**
     * 店铺提现信息
     * @return array
     */
    public function withdraw()
    {
        return $this->renderSuccess([
            // 分销商用户信息
            'dealer' => $this->dealer,
            // 结算设置
            'settlement' => $this->setting['settlement']['values'],
            // 背景图
            'background' => $this->setting['background']['values']['withdraw_apply'],
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

    /**
     * 提交提现申请
     * @param $data
     * @return array
     * @throws \app\common\exception\BaseException
     */
    public function withdraw_submit($data)
    {
        $formData = json_decode(htmlspecialchars_decode($data), true);
        $model = new WithdrawModel();
        if ($model->submit($this->dealer, $formData)) {
            return $this->renderSuccess([], '申请提现成功');
        }
        return $this->renderError($model->getError() ?: '提交失败');
    }

    /**
     * 分销商提现明细
     * @param int $status
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($status = -1)
    {
        $model = new WithdrawModel;
        return $this->renderSuccess([
            // 提现明细列表
            'list' => $model->getList($this->user['user_id'], (int)$status),
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

    /**
     * 当前用户是否为入驻商家
     * @return bool
     */
    private function isDealerUser()
    {
        return !!$this->dealer && !$this->dealer['is_delete'];
    }

    /**
     * 当前用户是否为入驻商家
     * @return bool
     */
    private function isApplying()
    {
        $model = new ShopSettled();
        $info = $model->where(['user_id'=>$this->user['user_id'],'is_pass'=>0])->find();
        if($info){
            return true;
        }else{
            return false;
        }
    }
}