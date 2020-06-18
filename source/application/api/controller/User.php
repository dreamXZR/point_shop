<?php

namespace app\api\controller;

use app\api\model\User as UserModel;
use app\store\model\UserExchange;
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

    /**
     * 用户兑换记录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exchangeLists($is_used)
    {
        $user = $this->getUser();
        $fields = [
            'ue.id as id',
            'g.goods_name',
            'ue.exchange_number',
            'ue.exchange_points',
            'ue.is_used',
        ];
        $where = [
            'is_used' => $is_used,
            'user_id' => $user['user_id']
        ];
        $joins = [
            ['goods g','ue.point_goods_id = g.goods_id','left']
        ];
        $exchangeRecord = Db::name('user_exchange')->alias('ue')->field($fields)->where($where)->join($joins)->select();
        return $this->renderSuccess([
            'list'=>$exchangeRecord
        ]);
    }

    public function exchangeDetail($id)
    {
        $user = $this->getUser();
        $data = UserExchange::get($id);
        return $this->renderSuccess([
            'info'=>$data
        ]);
    }

}
