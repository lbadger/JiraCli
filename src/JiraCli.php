<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 7/8/14
 * Time: 9:33 PM
 */

namespace WCurtis;

use WCurtis\Jira\JiraUtil;
use WCurtis\Timer\TimerAbstract;

class JiraCli {
    protected $jira;
    protected $maxIssues = 50;

    protected $filters;

    protected $issueFields = ['summary', 'issuetype', 'description'];

    public function __construct(JiraUtil $jira, TimerAbstract $timer) {
        $this->jira = $jira;
        $this->timer = $timer;
    }

    public function GetTimer() {
        return $this->timer;
    }

    public function GetFilters($force = false) {
        if(!$force && $this->filters) return $this->filters;

        $filters = $this->jira->GetFavoriteFilters();
        $this->filters = Util::toDict($filters, function($f) {
            return [$f['id'], $f];
        });

        return $this->filters;
    }

    public static $issueMap = [
        'id' => 'id',
        'key' => 'key',
        'summary' => 'fields.summary',
        'description' => 'fields.description',
        'issuetype' => 'fields.issuetype.name'
    ];

    protected function mapThing($thing, $map, $wrap = true) {
        $newThing = [];

        foreach($map as $key => $value) {
            $newValue = Util::GetFromArray($thing, $value);

            $newValue = $wrap 
                ? wordwrap(str_replace("\r", '', $newValue), 70, "\n", true)
                : $newValue;

            $newThing[$key] = $newValue;
        }

        return $newThing;
    }

    protected function mapIssues($jiraSearchResult) {
        return array_map(
            function($i) { return $this->mapThing($i, self::$issueMap, true); },
            $jiraSearchResult['issues']
        );
    }

    public function RunJql($jql) {
        $result = $this->jira->Search(
            $jql,
            0,
            $this->maxIssues,
            $this->issueFields
        );

        return $this->mapIssues($result);
    }

    public function RunFilter($filter) {
        if(!$this->filters) $this->GetFilters();
        if(is_string($filter) || is_int($filter)) $filter = $this->filters[$filter];

        return $this->RunJql($filter['jql']);
    }

    protected static $unitDividers = [
        'seconds' => 1,
        'minutes' => 60,
        'hours' => 3600,
        'days' => 86400
    ];

    public function GetLoggedTime($issue, $onlyMe = true, $unit = 'hours') {
        $url = $this->jira->getEndpoint(['issue', $issue, 'worklog']);
        $result = $this->jira->HttpGet($url);
        $me = $this->jira->GetUsername();

        $seconds = array_reduce($result['worklogs'], function($seconds, $worklog) use ($onlyMe, $me) {
            $author = Util::getFromArray($worklog, 'author.name');
            if($onlyMe && $author !== $me) return $seconds;

            return $seconds + (int)$worklog['timeSpentSeconds'];
        }, 0);

        return $seconds / self::$unitDividers[$unit];
    }

    public function GetWorklogs($issue, $onlyMe = true) {
        $url = $this->jira->getEndpoint(['issue', $issue, 'worklog']);
        $result = $this->jira->HttpGet($url);
        $me = $this->jira->GetUsername();

        return array_filter(array_map(function($w) use ($onlyMe, $me) {
            $author = Util::getFromArray($w, 'author.name');
            if($onlyMe && $author !== $me) return null;
            return [
                'id' => $w['id'],
                'timeSpent' => $w['timeSpent'],
                'comment' => $w['comment'],
                'date' => Config::GetDate($w['started'])->format('Y-m-d')
            ];
        }, $result['worklogs']));
    }

    public function AddWorklog($issue, \DateTime $started, $comment, $time, $sendTimeUnparsed = false) {
        $time = $sendTimeUnparsed ? $time : (int)$time . 'm';

        $url = $this->jira->getEndpoint(['issue', $issue, 'worklog']);
        $worklog = [
            'author' => ['name' => $this->jira->GetUsername()],
            'updateAuthor' => ['name' => $this->jira->GetUsername()],
            'comment' => $comment,
            'timeSpent' => $time,
            'started' => JiraUtil::FormatJiraDate($started)
        ];

        return $this->jira->HttpPost($url, json_encode($worklog));
    }

    public static $commentMap = [
        'id' => 'id',
        'author' => 'author.name',
        'body' => 'body',
        'updateAuthor' => 'updateAuthor.name',
        'visType' => 'visibility.type',
        'visValue' => 'visibility.value',
    ];

    public function ListComments($issue) {
        $url = $this->jira->getEndpoint(['issue', $issue, 'comment']);

        $result = $this->jira->HttpGet($url);

        return array_map(function($c) {
            return $this->mapThing($c, self::$commentMap);
        }, $result['comments']);
    }

    protected static function parseVisibility($vis) {
        $components = explode('.', $vis);

        if(count($components) !== 2) throw new \Exception("Could not parse visibility: $vis");

        list($type, $value) = $components;

        return [
            'type' => $type,
            'value' => $value
        ];
    }
    
    public function AddComment($issue, $body, $visibility = null) {
        $url = $this->jira->getEndpoint(['issue', $issue, 'comment']);

        $comment = [
            'body' => $body,
        ];

        if($visibility) {
            $comment['visibility'] = self::parseVisibility($visibility);
        }

        return $this->jira->HttpPost($url, json_encode($comment));
    }
}
