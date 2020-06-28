<?php

namespace app\api\controller;

use \app\store\model\Banner as BannerModel;

/**
 * 用户管理
 * Class User
 * @package app\api
 */
class Banner extends Controller
{

    public function index($banner_type)
    {
        $model = new BannerModel();
        $filter = [];
        if($banner_type){
            $filter['image_type'] = $banner_type;
        }
        $banners = $model->where($filter)->order('image_type asc,sort asc')->with(['banner'])->select();
        $data = [];
        foreach ($banners as $banner){
            $data[] = [
                'id' => $banner['id'],
                'redirect_url' => $banner['redirect_url'],
                'file_path' => $banner['banner']['file_path']
            ];
        }

        return $this->renderSuccess($data);
    }

}
