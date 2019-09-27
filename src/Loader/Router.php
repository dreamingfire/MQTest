<?php


namespace App\Loader;


class Router
{
    private $_ctlConfig;
    private $_cmdConfig;

    public function __construct($filePath)
    {
        $env = $_SERVER['APP_ENV'] ?? "dev";
        $loader = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];
        $this->_ctlConfig = $loader['router']['app_env'][$env] ?? [];
        $this->_cmdConfig = $loader['command'] ?? [];
    }

    public function loadController($route, &$class=NULL, &$method=NULL)
    {
       $class = $this->_ctlConfig[$route]['class'] ?? "FOO";
       $method = $this->_ctlConfig[$route]['method'] ?? "foo";
    }

    public function getAllCommand($isDesc = false)
    {
        $commandArr = array();
        $commandDesc = "";
        foreach ($this->_cmdConfig as $topic => $command) {
            $commandArr[]['route'] = $topic;
            $commandArr[]['description'] = $command['desc'];
            $commandDesc .= "{$topic} \t  {$command['desc']}\n";
        }
        return $isDesc ? $commandDesc : $commandArr;
    }

    public function loadCommand($route, &$class=NULL, &$argv=NULL)
    {
        $class = $this->_cmdConfig[$route]['class'] ?? "FOO";
        $argv = $this->_cmdConfig[$route]['argv'] ?? [];
    }

}