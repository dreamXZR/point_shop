<?php


namespace app\store\model;



use think\Model;

class UserExchange extends Model
{
    protected $name = 'user_exchange';

    public function getList($listRows = 15)
    {
        $request_data = $data = request()->get();
        $where = [];
        if(isset($request_data['exchange_code'])){
            $where['ux.exchange_code'] = $request_data['exchange_code'];
        }
        $joins = [
            ['user u','u.user_id = ux.user_id','left'],
        ];
        $fields = [
            'ux.id as id',
            'u.nickName as user_name',
            'ux.exchange_number',
            'ux.exchange_points',
            'ux.goods_remarks',
            'ux.is_used',
            'ux.create_time'
        ];
        $sort = ['ux.is_used'=>'asc','ux.create_time'=>'desc'];
        $list = $this->alias('ux')->where($where)->join($joins)->field($fields)->order($sort)
            ->paginate($listRows, false, [
                'query' => \request()->request()
            ]);

        return $list;
    }
}