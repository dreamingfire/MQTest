<?php


namespace App\Controller;


use App\Entity\ImMessage;
use App\Enum\ExchangeEnum;
use App\Http\Request;
use App\Http\Response;
use App\Loader\MQLoader;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class MqSenderController
{
    // 默认交换机
    public function sendBaseQueue()
    {
        $to = Request::get("toUser");
        $from = Request::get("fromUser");
        $content = Request::get("message");
        if(empty($to)) {
            return (new Response(4000))->setErrorMsg("toUser cannot be null");
        }
        $queueName = "q.im.user.{$to}";
        $message = (new ImMessage(NULL, $queueName))->setFrom($from)->setContent($content);
        MQLoader::connect();
        // 通道，同一个TCP连接多个通道，非阻塞多线程的实现方式
        $channel = MQLoader::getConnection()->channel();
        $channel->queue_declare(
            $queueName, // 队列名称
            false, // 是否检测同名队列
            true, // 是否开启队列持久化
            false, // 是否独享消费
            true // 连接断开(无消息时)是否自动删除队列
        );
        $msg = new AMQPMessage($message);
        // exchange为空时 需要将routingkey设置成队列名称，AMQP不会直接投递到队列，而是经过一个默认的直连交换机投递到同名队列中
        $channel->basic_publish($msg, "", $queueName);
        $channel->close();
        return (new Response())->setContent("send to {$to} successfully.");
    }

    // 直连交换机
    public function sendByDirectExchange()
    {
        $content = Request::get("message");
        $fromUser = Request::get("fromUser");
        $topic = Request::get("topic") ?: "";
        if(empty($content)) {
            return (new Response(4000))->setErrorMsg("content cannot be null");
        }
        $message = (new ImMessage(NULL, "all"))->setFrom("{$fromUser}\${$topic}\$")->setContent($content);
        MQLoader::connect();
        $channel = MQLoader::getConnection()->channel();
        // 声明交换机
        $channel->exchange_declare(ExchangeEnum::DIRECT_EX_NAME, ExchangeEnum::DIRECT, false, true, false);
        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, ExchangeEnum::DIRECT_EX_NAME, $topic);
        $channel->close();
        return (new Response())->setContent("topic {$topic} message from {$fromUser} sent successfully.");
    }

    // 扇形交换机（广播）,无 routingKey
    public function sendByFanoutExchange()
    {
        $content = Request::get("message");
        if(empty($content)) {
            return (new Response(4000))->setErrorMsg("content cannot be null");
        }
        $message = (new ImMessage(NULL, "all"))->setFrom("Sys")->setContent($content);
        MQLoader::connect();
        $channel = MQLoader::getConnection()->channel();
        // 声明交换机
        $channel->exchange_declare(ExchangeEnum::SYS_EX_NAME, ExchangeEnum::FANOUT, false, true, false);
        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, ExchangeEnum::SYS_EX_NAME);
        $channel->close();
        return (new Response())->setContent("system message sent successfully.");
    }

    // 主题交换机
    public function sendByTopicExchange()
    {
        $content = Request::get("message");
        $fromUser = Request::get("fromUser");
        $topic = Request::get("topic") ?: "";
        if(empty($content)) {
            return (new Response(4000))->setErrorMsg("content cannot be null");
        }
        $message = (new ImMessage(NULL, "all"))->setFrom("{$fromUser} \${$topic}\$")->setContent($content);
        MQLoader::connect();
        $channel = MQLoader::getConnection()->channel();
        // 声明交换机
        $channel->exchange_declare(ExchangeEnum::TOPIC_EX_NAME, ExchangeEnum::TOPIC, false, true, false);
        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, ExchangeEnum::TOPIC_EX_NAME, $topic);
        $channel->close();
        return (new Response())->setContent("topic {$topic} message from {$fromUser} sent successfully.");
    }

    // 头交换机
    public function sendByHeaderExchange()
    {
        $content = Request::get("message");
        $fromUser = Request::get("fromUser");
        $type = Request::get("type") ?: "";
        $subject = Request::get("subject") ?: "";
        if(empty($content)) {
            return (new Response(4000))->setErrorMsg("content cannot be null");
        }
        $message = (new ImMessage(NULL, "all"))->setFrom("{$fromUser} \${$type}-{$subject}\$")->setContent($content);
        MQLoader::connect();
        $channel = MQLoader::getConnection()->channel();
        // 声明交换机
        $channel->exchange_declare(ExchangeEnum::HEADER_EX_NAME, ExchangeEnum::HEADER, false, true, false);
        $header = new AMQPTable(["type"=>$type, "subject"=>$subject]);
        $msg = new AMQPMessage($message);
        $msg->set("application_headers", $header);
        $channel->basic_publish($msg, ExchangeEnum::HEADER_EX_NAME);
        $channel->close();
        return (new Response())->setContent("{$type}:{$subject} message from {$fromUser} sent successfully.");
    }
}