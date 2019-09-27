<?php
namespace App;

use App\Http\Request;
use App\Http\Response;
use App\Loader\Router;

class DistributionApp
{
    private $_requestUri;
    private $_request;
    private $_response;
    private $_controller;
    private $_method;

    public function __construct()
    {
        $this->_requestUri = $_SERVER['PATH_INFO'];
        $this->_request = $_REQUEST;
    }

    public function handle()
    {
        Request::setRequest($this->_request);
        $this->getConfig();
        try {
            $object = new \ReflectionClass('App\\Controller\\' . $this->_controller);
        } catch (\ReflectionException $e) {
            $this->_response = (new Response(5001))->setErrorMsg($e->getMessage());return;
        }
        if(!$object->hasMethod($this->_method)) {
            $this->_response = (new Response(5002))->setErrorMsg("method not found in " . $object->getName());return;
        }
        try {
            $this->_response = $object
                    ->getMethod($this->_method)
                    ->invoke($object->newInstance());
        } catch (\ReflectionException $e) {
            $this->_response = (new Response(5003))->setErrorMsg($e->getMessage());return;
        }
    }

    public function send()
    {
        die($this->_response ?: (new Response(5004))
            ->setErrorMsg("empty response sent"));
    }

    private function getConfig()
    {
        (new Router(dirname(__DIR__)."/config/router.json"))
            ->loadController($this->_requestUri, $this->_controller, $this->_method);
    }
}