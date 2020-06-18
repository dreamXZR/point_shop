<?php

namespace app\store\model;

use think\Cache;
use think\Model;

/**
 * 商品分类模型
 * Class Category
 * @package app\store\model
 */
class Banner extends Model
{
    protected $name = 'banner';
    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {

        if (!empty($data['image'])) {
            $data['image_url'] = UploadFile::getFildIdByName($data['image']);
        }
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        !array_key_exists('image_id', $data) && $data['image_id'] = 0;
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除商品分类
     * @param $category_id
     * @return bool|int
     * @throws \think\Exception
     */
    public function remove($category_id)
    {
        // 判断是否存在商品
        if ($goodsCount = (new Goods)->getGoodsTotal(['category_id' => $category_id])) {
            $this->error = '该分类下存在' . $goodsCount . '个商品，不允许删除';
            return false;
        }
        // 判断是否存在子分类
        if ((new self)->where(['parent_id' => $category_id])->count()) {
            $this->error = '该分类下存在子分类，请先删除';
            return false;
        }
        $this->deleteCache();
        return $this->delete();
    }

    public function getList($banner_type = 0)
    {
        $filter = [];
        if($banner_type){
            $filter['image_type'] = $banner_type;
        }
        return $this->where($filter)->order('image_type asc,sort asc')->select();
    }

    public function banner()
    {
        return $this->hasOne("app\\store\\model\\UploadFile", 'file_id', 'image_id');
    }

}
