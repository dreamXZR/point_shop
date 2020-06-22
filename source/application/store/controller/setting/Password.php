<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\store\Role as RoleModel;
use app\store\model\store\User as StoreUserModel;
use app\store\model\store\UserRole;
use think\Session;

/**
 * 清理缓存
 * Class Index
 * @package app\store\controller
 */
class Password extends Controller
{
    public function edit()
    {
        // 管理员详情
        $admin_user = Session::get('yoshop_store')['user'];
        $model = StoreUserModel::detail($admin_user['store_user_id']);
        $model['roleIds'] = UserRole::getRoleIds($model['store_user_id']);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', [
                'model' => $model,
                // 角色列表
                'roleList' => (new RoleModel)->getList(),
                // 所有角色id
                'roleIds' => UserRole::getRoleIds($model['store_user_id']),
            ]);
        }
        // 更新记录
        if ($model->edit($this->postData('user'),false)) {
            return $this->renderSuccess('更新成功', url('setting.password/edit'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
