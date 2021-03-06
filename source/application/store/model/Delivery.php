<?php

namespace app\store\model;

use app\common\model\Delivery as DeliveryModel;
use think\Request;
use think\Session;

/**
 * 配送模板模型
 * Class Delivery
 * @package app\common\model
 */
class Delivery extends DeliveryModel
{
    private $admin_user;

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->admin_user = Session::get('yoshop_store.user');
    }

    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $where = [];
        $store_shop_id = $this->admin_user['store_shop_id'];
        if($store_shop_id){
            $where['store_shop_id'] = ['in',[0,$store_shop_id]];
        }else{
            $where['store_shop_id'] = 0;
        }
        return $this->with(['rule'])
            ->where($where)
            ->order(['store_shop_id'=>'asc','sort' => 'asc'])
            ->paginate(15, false, [
                'query' => Request::instance()->request()
            ]);
    }

    /**
     * 添加新记录
     * @param $data
     * @return bool|int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function add($data)
    {
        if (!isset($data['rule']) || empty($data['rule'])) {
            $this->error = '请选择可配送区域';
            return false;
        }
        $data['wxapp_id'] = config('mini_weixin.wxapp_id');
        $data['store_shop_id'] = $this->admin_user['store_shop_id'];
        if ($this->allowField(true)->save($data)) {
            return $this->createDeliveryRule($data['rule']);
        }
        return false;
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function edit($data)
    {
        if (!isset($data['rule']) || empty($data['rule'])) {
            $this->error = '请选择可配送区域';
            return false;
        }
        if ($this->allowField(true)->save($data)) {
            return $this->createDeliveryRule($data['rule']);
        }
        return false;
    }

    /**
     * 获取配送区域及运费设置项
     * @return array
     */
    public function getFormList()
    {
        // 所有地区
        $regions = Region::getCacheAll();
        $list = [];
        foreach ($this['rule'] as $rule) {
            $citys = explode(',', $rule['region']);
            $province = [];
            foreach ($citys as $cityId) {
                if (!in_array($regions[$cityId]['pid'], $province)) {
                    $province[] = $regions[$cityId]['pid'];
                }
            }
            $list[] = [
                'first' => $rule['first'],
                'first_fee' => $rule['first_fee'],
                'additional' => $rule['additional'],
                'additional_fee' => $rule['additional_fee'],
                'province' => $province,
                'citys' => $citys,
            ];
        }
        return $list;
    }

    /**
     * 添加模板区域及运费
     * @param $data
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function createDeliveryRule($data)
    {
        $save = [];
        $connt = count($data['region']);
        for ($i = 0; $i < $connt; $i++) {
            $save[] = [
                'region' => $data['region'][$i],
                'first' => $data['first'][$i],
                'first_fee' => $data['first_fee'][$i],
                'additional' => $data['additional'][$i],
                'additional_fee' => $data['additional_fee'][$i],
                'wxapp_id' => config('mini_weixin.wxapp_id')
            ];
        }
        $this->rule()->delete();
        return $this->rule()->saveAll($save);
    }

    /**
     * 删除记录
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        // 判断是否存在商品
        if ($goodsCount = (new Goods)->where(['delivery_id' => $this['delivery_id'],'is_delete'=>0])->count()) {
            $this->error = '该模板被' . $goodsCount . '个商品使用，不允许删除';
            return false;
        }
        $this->rule()->delete();
        return $this->delete();
    }

}
