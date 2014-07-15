<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 7/8/14
 * Time: 9:33 PM
 */

namespace WCurtis;

//use WCurtis\Map\Maps;
use WCurtis\Jira\JiraUtil;
use WCurtis\Timer\TimerAbstract;

class JiraCli {
    protected $jira;
    protected $maxIssues = 50;
    protected $map;

    protected $filters;

    protected $issueFields = ['summary', 'issuetype', 'description'];

    public function __construct(JiraUtil $jira, TimerAbstract $timer) {
        $this->jira = $jira;
        $this->timer = $timer;
        $this->map = new \WCurtis\Map\Maps();
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

    public function RunJql($jql) {
        $result = $this->jira->Search(
            $jql,
            0,
            $this->maxIssues,
            $this->issueFields
        );

        return $this->map->MapArray('issues', $result['issues']);
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

        $logs = $this->map->MapArray('worklogs', $result['worklogs']);

        return $onlyMe
            ? array_filter($logs, function($w) use ($onlyMe, $me) {
                return !($onlyMe && Util::getFromArray($w, 'author') !== $me);
            })
            : $logs;
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

    public function ListComments($issue) {
        $url = $this->jira->getEndpoint(['issue', $issue, 'comment']);
        $result = $this->jira->HttpGet($url);

        return $this->map->MapArray('comments', $result['comments']);
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
