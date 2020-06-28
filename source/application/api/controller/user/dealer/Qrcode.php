<?php

namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Setting;
use app\api\model\dealer\User as DealerUserModel;
use app\api\model\store\Shop as ShopModel;
use app\common\service\qrcode\Poster;

/**
 * 推广二维码
 * Class Order
 * @package app\api\controller\user\dealer
 */
class Qrcode extends Controller
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
        // 店铺信息
        $this->dealer = ShopModel::getInfoByUserId($this->user['user_id']);
        // 设置
        $this->setting = Setting::getAll();
    }

    /**
     * 获取推广二维码
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function poster()
    {
        $Qrcode = new Poster($this->dealer);
        return $this->renderSuccess([
            // 二维码图片地址
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

}