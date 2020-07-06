<?php


namespace app\api\model\statements;

use app\common\model\statements\UserPointStatements as PointStatementsModel;

class UserPointStatements extends PointStatementsModel
{
    public function getList($user_id)
    {
        return $this
            ->where(['user_id'=>$user_id])
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
}