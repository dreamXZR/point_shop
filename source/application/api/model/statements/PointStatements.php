<?php


namespace app\api\model\statements;

use app\common\model\statements\PointStatements as PointStatementsModel;

class PointStatements extends PointStatementsModel
{
    public function getList($shop_id)
    {
        return $this
            ->where(['shop_id'=>$shop_id])
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
}