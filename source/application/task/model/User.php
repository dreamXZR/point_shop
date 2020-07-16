<?php

namespace app\task\model;

use app\api\model\dealer\Referee;
use app\api\model\dealer\Setting;
use app\common\model\User as UserModel;

/**
 * 用户模型
 * Class User
 * @package app\task\model
 */
class User extends UserModel
{
    /**
     * 获取用户信息
     * @param $user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($user_id)
    {
        return self::get($user_id);
    }

    /**
     * 累积用户总消费金额
     * @param $money
     * @return int|true
     * @throws \think\Exception
     */
    public function cumulateMoney($money)
    {
        $setting = Setting::getAll(config('mini_weixin.wxapp_id'));
        $this->setInc('money', $money);
        //查询用户是否到达购买金额
        if($this['money'] >= $setting['share']['values']['share_total_money']){
            //查看是否存在
            $referee_user_id = Referee::getRefereeUserId($this['user_id'],1);
            if($referee_user_id){
                $referee_user = static::detail($referee_user_id);
                $referee_user->incPoints($setting['share']['values']['share_points']);
            }
        }
    }

    public function incPoints($points)
    {
        return $this->setInc('points',$points);
    }

    public function decPoints($points)
    {
        return $this->setDec('points',$points);
    }

    public function incPreparePoints($points)
    {
        return $this->setInc('prepare_points',$points);
    }

    public function decPreparePoints($points)
    {
        if($this['prepare_points'] - $points < 0 ){
            return $this->save(['prepare_points'=>0]);
        }else{
            return $this->setDec('prepare_points',$points);
        }

    }

}
