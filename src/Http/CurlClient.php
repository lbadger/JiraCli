<?php
namespace WCurtis\Http;

class CurlClient extends HttpClient {

    public function read($url, $params = array(), $headers = array(), $options = array())
    {
        return $this->HttpRequest($url, 'GET', $params, $headers, '', $options);
    }

    public function post($url, $postData = '', $params = array(), $headers = array(), $options = array())
    {
        return $this->HttpRequest($url, 'POST', $params, $headers, $postData, $options);
    }

    public function put($url, $putData = '', $params = array(), $headers = array(), $options = array())
    {
        return $this->HttpRequest($url, 'PUT', $params, $headers, $putData, $options);
    }

    public function delete($url, $params = array(), $headers = array(), $options = array())
    {
        return $this->HttpRequest($url, 'DELETE', $params, $headers, $options);
    }

    protected function HttpRequest($url, $type = 'GET', $params = array(), $headers = array(), $postData='', $options = array()) {
        $curl = $this->initCurl($url, $type, $params, $headers, $postData, $options);
        $response = curl_exec($curl);

        return $this->ParseHttpResponse($curl, $response);
    }

    protected function initCurl($url, $type = 'GET', $params = array(), $headers=array(), $postData='', $options=array()) {

        $qMark = (strpos($url, '?') === false ? '?' : '&');

        $queryParams = empty($params) ? '' : http_build_query($params);

        $url = $url . (empty($queryParams) ? '' : $qMark) . $queryParams;
        $session = curl_init($url);

        curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);


        if($type === 'POST') {
            curl_setopt($session, CURLOPT_POST, 1);
            curl_setopt($session, CURLOPT_POSTFIELDS, $postData);
        } else if($type === 'PUT') {
            curl_setopt($session, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($session, CURLOPT_POSTFIELDS, $postData);
        } else if($type === 'DELETE') {
            curl_setopt($session, CURLOPT_CUSTOMREQUEST, "DELETE");
        }

        $extraHeaders = $this->HandleOptions($session, $options);

        $headers = array_merge($headers, $extraHeaders);

        $headers = self::normalizeHeaders($headers);

        if(!empty($headers)) {
            curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        }

        return $session;
    }

    protected function HandleOptions($session, $options) {
        $headers = [];

        if(!empty($options['auth'])) {
            $extraHeaders = $this->HandleAuth($session, $options['auth']);
            $headers = array_merge($headers, $extraHeaders);
        }

        if(!empty($options['file'])) {
            $extraHeaders = $this->HandleFile($session, $options['file']);
            $headers = array_merge($headers, $extraHeaders);
        }

        return $headers;
    }

    protected function HandleAuth($session, $options) {
        if(isset($options['username']) && isset($options['password'])) {
            curl_setopt($session, CURLOPT_USERPWD, self::GetUserAuth($options['username'], $options['password']));
        }

        return [];
    }

    protected function HandleFile($session, $options) {
        if(!is_string($options)) return [];
        $path = '@' . realpath($options);
        $filename = basename($options);

        curl_setopt($session, CURLOPT_POSTFIELDS, [
            'file' => $path . ';filename=' . $filename,
            //'filename' => $filename//. '; filename=' . $filename
        ]);

        return [
            'Content-Type' => 'multipart/form-data',
            'X-Atlassian-Token' => 'nocheck'
        ];
    }

    protected function ParseHttpResponse($session, $response) {
        $ret = $this->createHttpResponseObject($session);

        if(!$response) {
            $error = curl_error($session);
            $ret['error'] = $error;
        } else {
            if($ret['status'] >= 400) {
                $ret['error'] = $ret['status'];
            }
            $ret['data'] = $response;
        }

        return $ret;
    }

    protected function createHttpResponseObject($session = null) {
        return array(
            'error' => null,
            'status' => ($session ? curl_getinfo($session, CURLINFO_HTTP_CODE) : null),
            'info' => curl_getinfo($session),
            'data' => null
        );
    }
}
