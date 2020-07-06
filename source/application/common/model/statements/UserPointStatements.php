<?php


namespace app\common\model\statements;


use app\common\model\BaseModel;
use think\Session;

class UserPointStatements extends BaseModel
{
    protected $name = 'user_point_statements';

    private $admin_user;

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->admin_user = Session::get('yoshop_store.user');

    }

    public function record($data)
    {
        return $this->save($data);
    }

    public function getShopList()
    {
        $store_shop_id = $this->admin_user['store_shop_id'];
        return $this->where(['shop_id'=>$store_shop_id])
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    public function getShopRechargeList()
    {
        $where = ['type'=>30];
        $store_shop_id = $this->admin_user['store_shop_id'];
        if($store_shop_id){
            $where['shop_id'] = $store_shop_id;
        }
        return $this->where($where)
            ->with('shop')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);

    }

    public function user()
    {
        return $this->belongsTo('app\common\model\user','user_id','user_id');
    }

    public function shop()
    {
        return $this->belongsTo('app\common\model\store\Shop','shop_id','shop_id');
    }

}