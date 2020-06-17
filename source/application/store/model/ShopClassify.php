<?php

namespace app\store\model;

use app\store\model\store\Shop;
use think\Cache;
use app\common\model\Category as CategoryModel;

/**
 * 商品分类模型
 * Class Category
 * @package app\store\model
 */
class ShopClassify extends CategoryModel
{
    protected $name = 'store_shop_classify';
    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
//        if (!empty($data['image'])) {
//            $data['image_id'] = UploadFile::getFildIdByName($data['image']);
//        }
        $this->deleteCache();
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        $this->deleteCache();
        !array_key_exists('image_id', $data) && $data['image_id'] = 0;
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除店铺分类
     * @param $category_id
     * @return bool|int
     * @throws \think\Exception
     */
    public function remove($category_id)
    {
        // 判断是否存在店铺
//        if ($goodsCount = (new Shop())->getShopTotal(['category_id' => $category_id])) {
//            $this->error = '该分类下存在' . $goodsCount . '个店铺，不允许删除';
//            return false;
//        }
        // 判断是否存在子分类
        if ((new self)->where(['parent_id' => $category_id])->count()) {
            $this->error = '该分类下存在子分类，请先删除';
            return false;
        }
        $this->deleteCache();
        return $this->delete();
    }

    /**
     * 删除缓存
     * @return bool
     */
    private function deleteCache()
    {
        return Cache::rm('shop_classify');
    }

    /**
     * 分类图片
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }

    /**
     * 所有分类
     * @return mixed
     */
    public static function getALL()
    {
        $model = new static;
        if (!Cache::get('shop_classify')) {
            $data = $model->with(['image'])->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
            $all = !empty($data) ? $data->toArray() : [];
            $tree = [];
            foreach ($all as $first) {
                if ($first['parent_id'] != 0) continue;
                $twoTree = [];
                foreach ($all as $two) {
                    if ($two['parent_id'] != $first['category_id']) continue;
                    $threeTree = [];
                    foreach ($all as $three)
                        $three['parent_id'] == $two['category_id']
                        && $threeTree[$three['category_id']] = $three;
                    !empty($threeTree) && $two['child'] = $threeTree;
                    $twoTree[$two['category_id']] = $two;
                }
                if (!empty($twoTree)) {
                    array_multisort(array_column($twoTree, 'sort'), SORT_ASC, $twoTree);
                    $first['child'] = $twoTree;
                }
                $tree[$first['category_id']] = $first;
            }
            Cache::tag('cache')->set('shop_classify', compact('all', 'tree'));
        }
        return Cache::get('shop_classify');
    }

    /**
     * 获取所有分类
     * @return mixed
     */
    public static function getCacheAll()
    {

        return self::getALL()['all'];
    }

    /**
     * 获取所有分类(树状结构)
     * @return mixed
     */
    public static function getCacheTree()
    {
        return self::getALL()['tree'];
    }

    /**
     * 获取所有分类(树状结构)
     * @return string
     */
    public static function getCacheTreeJson()
    {
        return json_encode(static::getCacheTree());
    }

    /**
     * 获取指定分类下的所有子分类id
     * @param $parent_id
     * @param array $all
     * @return array
     */
    public static function getSubCategoryId($parent_id, $all = [])
    {
        $arrIds = [$parent_id];
        empty($all) && $all = self::getCacheAll();
        foreach ($all as $key => $item) {
            if ($item['parent_id'] == $parent_id) {
                unset($all[$key]);
                $subIds = self::getSubCategoryId($item['category_id'], $all);
                !empty($subIds) && $arrIds = array_merge($arrIds, $subIds);
            }
        }
        return $arrIds;
    }

}
