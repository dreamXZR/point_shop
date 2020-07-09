<?php


namespace app\api\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;

class OrderFinish extends Command
{
    protected function configure()
    {
        $this->setName('orderFinish')->setDescription('订单状态更新、积分分发');
    }

    protected function execute(Input $input, Output $output)
    {
        $order = new \app\task\behavior\Order();
        $result = $order->run(new \app\task\model\Order());
        if($result){
            $output->writeln('finish');
        }else{
            $output->writeln($order->error);
        }

    }
}