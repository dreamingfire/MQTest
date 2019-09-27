<?php


namespace App\Entity;


class ImMessage
{
    private static $forceCloseCmd = ["/exit", "/quit", "/shutdown", "/close"];
    private $content;
    private $from;
    private $to;
    private $createTime;

    public function __construct($messageJson, $toUser)
    {
        $message = json_decode($messageJson,true);
        $this->content = $message['message'] ?? "some error occur, no message sent";
        $this->createTime = $message['createTime'] ?? date('Y/m/d H:i:s GST');
        $this->from = $message['sender'] ?? "Anonym";
        $this->to = $toUser;
    }

    public function __toString()
    {
        return json_encode([
            "message"   => $this->getContent(),
            "sender"    => $this->getFrom(),
            "routingKey"=> $this->getTo(),
            "createTime"=> $this->getCreateTime(),
        ]);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content ?: $this->content;
        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from)
    {
        $this->from = $from ?: $this->from;
        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getCreateTime()
    {
        return $this->createTime;
    }

    // 处理Message
    public function showMessage()
    {
        echo sprintf("\n[%s] From %s: %s\n", $this->getCreateTime(), $this->getFrom(), $this->getContent());
        if(in_array(strtolower($this->getContent()), self::$forceCloseCmd)) {
            die("\n[".date("Y-m-d H:i:s GST")."] From Sys: Your session interrupted by {$this->getFrom()}\n");
        }
    }
}