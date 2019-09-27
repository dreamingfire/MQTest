<?php


namespace App\Enum;


final class ExchangeEnum
{
    // 交换机类型枚举
    const DIRECT = "direct";
    const FANOUT = "fanout";
    const TOPIC = "topic";
    const HEADER = "headers";

    // 通用交换机名称枚举
    const DIRECT_EX_NAME = "x-im.group-direct";
    const SYS_EX_NAME = "x-im.all-fanout";
    const TOPIC_EX_NAME = "x-im.group-topic";
    const HEADER_EX_NAME = "x-im.group-headers";
}