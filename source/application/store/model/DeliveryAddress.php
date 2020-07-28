<?php

namespace app\store\model;

use app\common\model\DeliveryAddress as DeliveryAddressModel;
use Lvht\GeoHash;
use think\Session;

/**
 * 退货地址模型
 * Class ReturnAddress
 * @package app\store\model
 */
class DeliveryAddress extends DeliveryAddressModel
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
        return $this->order(['sort' => 'asc'])
            ->where([
                'is_delete' => 0,
            ])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 获取全部收货地址
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAll($store_shop_id)
    {
        $where = ['is_delete'=>0];
        if($store_shop_id){
            $where['store_shop_id'] = $store_shop_id;
        }
        return $this->order(['sort' => 'asc'])
            ->where($where)
            ->select();
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        return $this->allowField(true)->save($this->createData($data));
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {

        return $this->allowField(true)->save($this->createData($data));
    }

    /**
     * 删除记录
     * @return bool|int
     */
    public function remove()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 创建数据
     * @param array $data
     * @return array
     */
    private function createData($data)
    {
        // 格式化坐标信息
        $coordinate = explode(',', $data['coordinate']);
        $data['latitude'] = $coordinate[0];
        $data['longitude'] = $coordinate[1];
        // 生成geohash
        $Geohash = new Geohash;
        $data['geohash'] = $Geohash->encode($data['longitude'], $data['latitude']);
        return $data;
    }

}