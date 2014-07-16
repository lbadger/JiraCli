<?php

namespace WCurtis\Jira;

use WCurtis\Http\HttpClient;
use WCurtis\Http\HttpException;
use WCurtis\Util;

class JiraUtil
{

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var string */
    protected $host;

    /** @var \WCurtis\Http\HttpClient */
    protected $httpClient;

    /** @var string */
    protected $baseEndpoint;

    public function __construct(HttpClient $httpClient, $username, $password, $host)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->httpClient = $httpClient;

        $this->baseEndpoint = $this->getBaseEndpoint();
    }

    /**
     * @param \DateTime $datetime
     * @return string
     */
    public static function FormatJiraDate(\DateTime $datetime)
    {
        return $datetime->format('Y-m-d\TH:i:s.000O');
    }

    protected static function getDefaultHeaders($type = 'GET', $excludeHeaders = [])
    {
        $headers = [];

        if(!in_array('Accept', $excludeHeaders)) {
            $headers['Accept'] = 'application/json';
        }
        if (($type == 'POST' || $type == 'PUT') && !in_array('Content-Type', $excludeHeaders)) {
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }

    /**
     * @param string $jql JQL query
     * @param int $startAt Offset to start retrieval at
     * @param int $maxResults Limit of the number of records
     * @param array $fields Simple array of fields to return
     * @param array $expand Simple array of which fields to expand
     *                      Basically, may be a field in JIRA that has multiple
     *                      records; this tells the api to retrieve all of them
     * @return array
     */
    public function Search($jql, $startAt = 0, $maxResults = 50, $fields = array(), $expand = array())
    {
        $params = array(
            'jql' => $jql,
            'startAt' => $startAt,
            'maxResults' => $maxResults,
            'fields' => implode(',', $fields),
            'expand' => implode(',', $expand),
        );
        $url = $this->getEndpoint(array('search'));

        return $this->HttpGet($url, $params);
    }

    public function getEndpoint($path)
    {
        return $this->baseEndpoint . '/' . implode('/', $path);
    }

    public function HttpGet($url, $params = array(), $header = array(), $excludeHeaders = [], $parse = true)
    {
        $options = $this->getRequestOptions();
        $header = array_merge(self::getDefaultHeaders('GET', $excludeHeaders), $header);

        $response = $this->httpClient->read($url, $params, $header, $options);

        if($response['error'] || $response['status'] >= 400) {
            throw new HttpException("Error during HttpGet: " . Util::GetFromArray($response, 'data'), $response);
        }

        return $parse
            ? json_decode($response['data'], true)
            : $response;
    }

    public function HttpPost($url, $postData, $header = array(), $options = [])
    {
        if (!is_string($postData)) {
            $postData = json_encode($postData);
        }

        $options = array_merge($options, $this->getRequestOptions());

        $header = array_merge(self::getDefaultHeaders('POST'), $header);

        $response = $this->httpClient->post($url, $postData, array(), $header, $options);

        if($response['error'] || $response['status'] >= 400) {
            throw new HttpException("Error during HttpPost: " . Util::GetFromArray($response, 'data')
                . "\nData: $postData", $response
            );
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getBaseEndpoint()
    {
        return $this->host . '/rest/api/2';
    }

    protected function getRequestOptions()
    {
        $options = array(
            'auth' => array(
                'username' => $this->username,
                'password' => $this->password
            ),
        );

        return $options;
    }

    public function GetFavoriteFilters() {
        $url = $this->getEndpoint(['filter', 'favourite']);
        $result = $this->HttpGet($url);

        $filters = array_map(function($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'jql' => $item['jql']
            ];
        }, $result);

        return $filters;
    }

    public function GetUsername() {
        return $this->username;
    }

    public function Attach($filename, $issue) {
        $url = $this->getEndpoint(['issue', $issue, 'attachments']);

        $response = $this->HttpPost($url, '', [], [
            'file' => $filename
        ]);

        $decoded = json_decode($response['data'], true);

        return empty($decoded) ? [] : $decoded[0];
    }

    public function GetIssue($issue, $fields = []) {
        $url = $this->getEndpoint(['issue', $issue]);
        $params = [
            'fields' => empty($fields) ? '*all' : implode(',', $fields)
        ];

        return $this->HttpGet($url, $params);
    }
}
