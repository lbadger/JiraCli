<?php
namespace WCurtis\Http;

class HttpException extends \Exception {
    protected $response;

    public function __construct($message, $response) {
        parent::__construct($message);
        $this->response = $response;

        echo print_r($this->response, true);
    }

    public function GetResponse() {
        return $this->response;
    }
}
