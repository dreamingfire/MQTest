<?php


namespace App\Http;


class Response
{
    private $_status;
    private $_content;
    private $_errorMsg;

    public function __construct($code = 200)
    {
        $this->_status = $code ?: 200;
    }

    public function __toString()
    {
        $response['status'] = $this->_status;
        !empty($this->_content) ?
            $response['content'] = $this->_content
            : $response['errorMsg'] = $this->_errorMsg;
        $response['timestamp'] = time();
        return json_encode($response);
    }

    public function setStatus($code)
    {
        $this->_status = $code ?: $this->_status;
        return $this;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function getErrorMsg()
    {
        return $this->_errorMsg;
    }

    public function setErrorMsg($errorMsg)
    {
        $this->_errorMsg = $errorMsg;
        return $this;
    }
}