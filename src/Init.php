<?php

namespace Plansys\Db;

class Init
{
    public $connParams;
    public $conn;
    public $notorm;

    function __construct($connParams)
    {
        $this->connParams = $connParams;

        $conf = new \Doctrine\DBAL\Configuration();
        $this->conn = \Doctrine\DBAL\DriverManager::getConnection($this->connParams, $conf);
        $this->conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $this->notorm = new \NotOrm($this->conn->getWrappedConnection());
    }
    
    function __call($func, $args)
    {
        if ($func === 'conn') {
            return call_user_func_array([$this, $func], $args);
        }
        
        return call_user_func_array([$this->notorm, $func], $args);
    }

    public static function query($page, $params)
    {
        $dbs = [];
        foreach ($this->settings as $k => $v) {
            if (is_object($v) && get_class($v) == '\Plansys\Db\Init') {
                $dbs[$k] = $v;
            }
        }
        var_dump($dbs);
        die();

        $query = new Query($dbs, $page);
        return $query->getResult($params);
    }

    public static function getBase($host)
    {
        return [
            'pages' => [
                '' => [
                    'dir'=> realpath(dirname(__FILE__) . '/..') . '/pages',
                    'url' => $host . '/pages/'
                ]
            ]
        ];
    }
}
