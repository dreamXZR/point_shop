<?php

namespace app\store\model\store;

use app\common\model\store\Shop as ShopModel;
use app\store\model\statements\PointStatements;
use Lvht\GeoHash;
use think\Db;

/**
 * 商家门店模型
 * Class Shop
 * @package app\store\model\store
 */
class Shop extends ShopModel
{
    /**
     * 获取列表数据
     * @param null $status
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($status = null)
    {
        !is_null($status) && $this->where('status', '=', (int)$status);
        return $this->where('is_delete', '=', '0')
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        return $this->allowField(true)->save($this->createData($data));
    }

    /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function edit($data,$is_store_shop = true)
    {
        //验证
//        if (!$this->validateForm($data)) {
//            return false;
//        }
        //
        if($is_store_shop){
            return $this->allowField(true)->save($this->createData($data)) !== false;
        }else{
            return $this->allowField(true)->save($data) !== false;
        }

    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
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
        $data['wxapp_id'] = self::$wxapp_id;
        // 格式化坐标信息
        $coordinate = explode(',', $data['coordinate']);
        $data['latitude'] = $coordinate[0];
        $data['longitude'] = $coordinate[1];
        // 生成geohash
        $Geohash = new Geohash;
        $data['geohash'] = $Geohash->encode($data['longitude'], $data['latitude']);
        return $data;
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['logo_image_id']) || empty($data['logo_image_id'])) {
            $this->error = '请选择门店logo';
            return false;
        }
        return true;
    }

    /**
     * 获取当前店铺总数
     * @param array $where
     * @return int|string
     */
    public function getShopTotal($where = [])
    {
        $this->where('is_delete', '=', 0);
        !empty($where) && $this->where($where);
        return $this->count();
    }

    /**
     * 提现打款成功：累积提现佣金
     * @param $user_id
     * @param $money
     * @return false|int
     * @throws \think\exception\DbException
     */
    public static function totalMoney($user_id, $money)
    {
        $model = self::detail($user_id);
        return $model->save([
            'freeze_money' => $model['freeze_money'] - $money,
            'total_money' => $model['total_money'] + $money,
        ]);
    }

    /**
     * 提现驳回：解冻店家资金
     * @param $user_id
     * @param $money
     * @return false|int
     * @throws \think\exception\DbException
     */
    public static function backFreezeMoney($shop_id, $money)
    {
        $model = self::detail($shop_id);
        return $model->save([
            'money' => $model['money'] + $money,
            'freeze_money' => $model['freeze_money'] - $money,
        ]);
    }

    public function recharge($data)
    {
        Db::startTrans();
        try {
            $this->save([
                'points' => $this['points'] + $data['points']
            ]);
            //积分记录
            $point_statements = new PointStatements();
            $point_statements->record([
                'shop_id' => $this['shop_id'],
                'charge_money'=>$data['charge_money'],
                'points' => $data['points'],
                'type' => 30,
                'remark' => '商家充值操作'
            ]);
            Db::commit();
            return true;
        }catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

}