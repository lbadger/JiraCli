<?php

namespace WCurtis\Http;

abstract class HttpClient {
    public abstract function read($url, $params=array(), $headers=array(), $options = array());
    public abstract function put($url, $putData='', $params=array(), $headers=array(), $options = array());
    public abstract function post($url, $postData='', $params=array(), $headers=array(), $options = array());
    public abstract function delete($url, $params=array(), $headers=array(), $options = array());

    protected static function normalizeHeaders($headers=array()) {
        $fixedHeaders = array();

        foreach($headers as $name => $value) {
            if(is_numeric($name)) {
                $fixedHeaders[] = $value;
                continue;
            }

            $fixedHeaders[] = $name . ': ' . $value;
        }

        return $fixedHeaders;
    }

    protected static function GetUserAuth($username, $password) {
        return $username . ':' . $password;
    }
}

