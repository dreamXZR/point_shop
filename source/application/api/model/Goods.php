<?php

namespace app\api\model;

use app\common\model\Category;
use app\common\model\Goods as GoodsModel;
use think\Session;

/**
 * 商品模型
 * Class Goods
 * @package app\api\model
 */
class Goods extends GoodsModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'sales_initial',
        'sales_actual',
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    protected $append = [
        'time',
        'goods_sales'
    ];

    /**
     * 商品详情：HTML实体转换回普通字符
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * 获取商品列表
     * @param int $status
     * @param int $category_id
     * @param string $search
     * @param string $sortType
     * @param bool $sortPrice
     * @param int $listRows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList(
        $status = null,
        $category_id = 0,
        $search = '',
        $goods_status = '',
        $sortType = 'all',
        $sortPrice = false,
        $shop_id = 0,
        $listRows = 15,
        $is_index_show = null
    )
    {
        // 筛选条件
        $filter = [
            'admin_goods_status'=>10,
            'is_delete' => 0
        ];
        $category_id > 0 && $filter['category_id'] = ['IN', Category::getSubCategoryId($category_id)];
        $status > 0 && $filter['goods_status'] = $status;
        !empty($search) && $filter['goods_name'] = ['like', '%' . trim($search) . '%'];
        //商品类型判断
        $filter['is_point_goods'] = 0;
        $filter['is_seckill_goods'] = 0;
        if($goods_status == 'seckill'){
            $filter['is_seckill_goods'] = 1;
        }elseif ($goods_status == 'point'){
            $filter['is_point_goods'] = 1;
        }
        //判断是商家还是超管
        $admin_user = Session::get('yoshop_store.user');
        if($admin_user['store_shop_id']){
            $filter['shop_id'] = $admin_user['store_shop_id'];
        }
        if($shop_id){
            $filter['shop_id'] = ['in',$shop_id];
        }
        if($is_index_show){
            $filter['is_index_show'] = 1;
        }
        // 排序规则
        $sort = [];
        if ($sortType === 'all') {
            $sort = ['goods_sort', 'goods_id' => 'desc'];
        } elseif ($sortType === 'sales') {
            $sort = ['goods_sales' => 'desc'];
        } elseif ($sortType === 'price') {
            $sort = $sortPrice ? ['goods_max_price' => 'desc'] : ['goods_min_price'];
        }
        // 商品表名称
        $tableName = $this->getTable();
        // 多规格商品 最高价与最低价
        $GoodsSku = new GoodsSku;
        $minPriceSql = $GoodsSku->field(['MIN(goods_price)'])
            ->where('goods_id', 'EXP', "= `$tableName`.`goods_id`")->buildSql();
        $maxPriceSql = $GoodsSku->field(['MAX(goods_price)'])
            ->where('goods_id', 'EXP', "= `$tableName`.`goods_id`")->buildSql();
        // 执行查询
        $list = $this
            ->field(['*', '(sales_initial + sales_actual) as goods_sales',
                "$minPriceSql AS goods_min_price",
                "$maxPriceSql AS goods_max_price"
            ])
            ->with(['category', 'image.file', 'sku'])
            ->where($filter)
            ->order($sort)
            ->paginate($listRows, false, [
                'query' => \request()->request()
            ]);
        return $list;
    }

    /**
     * 获取商品列表
     * @param int $status
     * @param int $category_id
     * @param string $search
     * @param string $sortType
     * @param bool $sortPrice
     * @param int $listRows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getSeckillList(
        $status = null,
        $category_id = 0,
        $search = '',
        $goods_status = '',
        $shop_id = 0,
        $listRows = 15
    )
    {
        // 筛选条件
        $filter = [
            'admin_goods_status'=>10,
            'is_delete' => 0
        ];
        if($status == 'on-going' || empty($status)){
            $filter['start_at'] = ['<',time()];
            $filter['end_at'] = ['>',time()];
        }else if($status == 'not-started'){
            $filter['start_at'] = ['>',time()];
        }
        !empty($search) && $filter['goods_name'] = ['like', '%' . trim($search) . '%'];
        //商品类型判断
        $filter['is_point_goods'] = 0;
        $filter['is_seckill_goods'] = 0;
        if($goods_status == 'seckill'){
            $filter['is_seckill_goods'] = 1;
        }elseif ($goods_status == 'point'){
            $filter['is_point_goods'] = 1;
        }
        if($shop_id){
            $filter['shop_id'] = $shop_id;
        }
        // 排序规则
        $sort = ['start_at'=>'asc'];
        // 商品表名称
        $tableName = $this->getTable();
        // 多规格商品 最高价与最低价
        $GoodsSku = new GoodsSku;
        $minPriceSql = $GoodsSku->field(['MIN(goods_price)'])
            ->where('goods_id', 'EXP', "= `$tableName`.`goods_id`")->buildSql();
        $maxPriceSql = $GoodsSku->field(['MAX(goods_price)'])
            ->where('goods_id', 'EXP', "= `$tableName`.`goods_id`")->buildSql();
        // 执行查询
        $list = $this
            ->field(['*', '(sales_initial + sales_actual) as goods_sales',
                "$minPriceSql AS goods_min_price",
                "$maxPriceSql AS goods_max_price"
            ])
            ->with(['category', 'image.file', 'sku'])
            ->where($filter)
            ->order($sort)
            ->paginate($listRows, false, [
                'query' => \request()->request()
            ]);
        return $list;
    }

    public function getTimeAttr($value,$data)
    {
        if($data['is_seckill_goods'] != 1){
            return false;
        }
        $current_time = time();
        if($data['start_at']>$current_time){
            return date("m月d H:i",$data['start_at']);
        }
        if($data['start_at']<$current_time && $data['end_at']>$current_time){
            $distance_time = $data['end_at']-$current_time;
            if($distance_time < 86400){
                return gmdate("H小时i分",$distance_time);
            }else{
                return gmdate("d天H小时i分",$distance_time);
            }

        }
    }

}
