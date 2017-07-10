<?php

namespace Plansys\Db;

class Init
{

    public static function getBase($host)
    {
        var_dump($host);
        die();

        return [
            'pages' => [
                '' => [
                    'dir'=> dirname(__FILE__) . '/pages',
                    'url' => $host . '/pages/'
                ]
            ]
        ];
    }
}
