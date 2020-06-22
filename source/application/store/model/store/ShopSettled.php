<?php


namespace app\store\model\store;


use app\store\model\store\User as StoreUserModel;
use think\Db;
use think\Model;

class ShopSettled extends Model
{
    protected $name = 'store_shop_settled';

    /**
     * 获取未审核的商家
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->where(['is_pass'=>0])->order(['create_time'=>'desc'])->paginate(15, false, [
            'query' => \request()->request()
        ]);
    }

    /**
     * 审核通过操作
     * @param $id
     */
    public function settled_pass($id)
    {
        Db::startTrans();
        try {
            //审核信息
            $info = $this->find($id);
            //审核表通过操作
            $this->save(['is_pass'=>1],['id'=>$id]);
            //创建店铺
            $store_shop_id = Db::table('yoshop_store_shop')->insertGetId([
                'user_id' => $info['user_id'],
                'shop_name' => $info['shop_name'],
                'linkman' => $info['linkman'],
                'phone' => $info['phone'],
                'create_time' => time(),
                'update_time' => time(),
            ]);
            //分配账号密码
            $store_user_model = new StoreUserModel;
            $create_user_result = $store_user_model->add([
                'user_name' => $info['phone'],
                'password' => '123456',
                'password_confirm' => '123456',
                'real_name' => $info['linkman'],
                'role_id' => [7],
                'store_shop_id' => $store_shop_id
            ]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            Db::rollback();
            return false;
        }
    }
}