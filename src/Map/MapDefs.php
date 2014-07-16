<?php

use WCurtis\Config;
use WCurtis\Util;

return [
    'worklogs' => [
        'id' => 'id',
        'author' => 'author.name',
        'timeSpent' => 'timeSpent',
        'comment' => 'comment',
        'date' => [
            'field' => 'started',
            'callable' => function($value) {
                return Config::GetDate($value)->format('Y-m-d');
             }
        ],
    ],

    'comments' => [
        'id' => 'id',
        'author' => 'author.name',
        'body' => [
            'field' => 'body',
            'callable' => ['WCurtis\\Util', 'WrapForTable']
        ],
        'updateAuthor' => 'updateAuthor.name',
        'visibility' => [
            'field' => 'visibility',
            'callable' => function($vis) {
                return Util::GetFromArray($vis, 'type') . '.'
                    . Util::GetFromArray($vis, 'value');
            }
        ],
    ],

    'issues' => [
        'id' => 'id',
        'key' => 'key',
        'summary' => [
            'field' => 'fields.summary',
            'callable' => ['WCurtis\\Util', 'WrapForTable']
        ],
        'description' => [
            'field' => 'fields.description',
            'callable' => ['WCurtis\\Util', 'WrapForTable']
        ],
        'issuetype' => 'fields.issuetype.name',
        'status' => 'fields.status.name'
    ],

    'filters' => [
        'id' => 'id',
        'name' => 'name',
        'jql' => [
            'field' => 'jql',
            'callable' => ['WCurtis\\Util', 'WrapForTable']
        ]
    ]
];
