<?php


namespace App\Command\Consumer;


use App\Command\BaseCommand;
use App\Entity\ImMessage;
use App\Enum\ExchangeEnum;
use App\Loader\MQLoader;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class BaseQueueConsumer extends BaseCommand
{

    function execute()
    {
        $userName = $this->getArgument("name");
        $topic = $this->getArgument("topic");
        $queueName = "q.im.user.{$userName}";
        MQLoader::connect();
        $channel = MQLoader::getConnection()->channel();

        // 声明队列
        $channel->queue_declare($queueName, false, true, false, true);
        echo "\nUser {$userName} enter, wait for message. Exit by CTRL-C.\n";

        // QOS 质量控制，控制一个消费者一次最多接收的消息数量
        $channel->basic_qos(null, 5, null);

        $channel->exchange_declare(ExchangeEnum::SYS_EX_NAME, ExchangeEnum::FANOUT, false, true, false);
        // 绑定 扇形交换机
        $channel->queue_bind($queueName,ExchangeEnum::SYS_EX_NAME);

        $channel->exchange_declare(ExchangeEnum::DIRECT_EX_NAME, ExchangeEnum::DIRECT, false, true, false);
        // 绑定 直连交换机
        $channel->queue_bind($queueName,ExchangeEnum::DIRECT_EX_NAME, $topic);

        $channel->exchange_declare(ExchangeEnum::TOPIC_EX_NAME, ExchangeEnum::TOPIC, false, true, false);
        // 绑定 主题交换机
        $channel->queue_bind($queueName,ExchangeEnum::TOPIC_EX_NAME, $topic);

        $channel->exchange_declare(ExchangeEnum::HEADER_EX_NAME, ExchangeEnum::HEADER, false, true, false);
        // 绑定 头交换机
        $channel->queue_bind($queueName,ExchangeEnum::HEADER_EX_NAME,"", false, new AMQPTable(["type"=>$topic,"subject"=>"test","x-match" => "any"]));

        // 消费
        $channel->basic_consume($queueName,"",false,false,false,false, function (AMQPMessage $msg){
            // 消息回调
            $messageObj = new ImMessage($msg->getBody(), "");
            $messageObj->showMessage();
            /**@var AMQPChannel $channel*/
            $channel = $msg->delivery_info['channel'];
            // 确认消息
            $channel->basic_ack($msg->delivery_info['delivery_tag']);
            // 拒绝消息
            //$channel->basic_reject($msg->delivery_info['delivery_tag'], true);
        });
        try {
            while (count($channel->callbacks)) {
                // 长连接
                $channel->wait();
            }
        } catch (\ErrorException $e) {
            die($e->getMessage() . "\n");
        }
    }
}