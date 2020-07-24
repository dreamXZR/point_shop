<?php

namespace app\common\model;

use app\common\model\Region as RegionModel;

/**
 * 退货地址模型
 * Class ReturnAddress
 * @package app\common\model
 */
class DeliveryAddress extends BaseModel
{
    protected $name = 'delivery_address';

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['region'];

    /**
     * 自提地址详情
     * @param $address_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($address_id)
    {
        return self::get($address_id);
    }

    /**
     * 地区名称
     * @param $value
     * @param $data
     * @return array
     */
    public function getRegionAttr($value, $data)
    {
        return [
            'province' => RegionModel::getNameById($data['province_id']),
            'city' => RegionModel::getNameById($data['city_id']),
            'region' => $data['region_id'] == 0 ? '' : RegionModel::getNameById($data['region_id']),
        ];
    }

}