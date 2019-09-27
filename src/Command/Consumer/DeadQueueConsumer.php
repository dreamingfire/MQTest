<?php


namespace App\Command\Consumer;


use App\Command\BaseCommand;

class DeadQueueConsumer extends BaseCommand
{
    function execute()
    {
        die("DeadQueueConsumer.\n");
    }
}