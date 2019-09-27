<?php


namespace App\Command;


abstract class BaseCommand
{
    private $_argv;

    public function __construct($argv)
    {
        $this->_argv = $argv;
    }

    public function getArgument($name)
    {
        return $this->_argv[$name] ?? NULL;
    }

    abstract function execute();
}