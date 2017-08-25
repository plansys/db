<?php

namespace Plansys\Db;

class Query
{
    private $result;
    private $page;
    private $dbs;
    private $app;

    function __construct(\Yard\Page $page)
    {
        $dbs = [];
        $this->app = &$page->base->settings;
        foreach ($this->app as $k => $v) {
            if (is_object($v) && get_class($v) == 'Plansys\Db\Init') {
                $dbs[$k] = $v;
            }
        }

        $this->page = $page;
        $this->dbs = $dbs;
    }

    public function getResult($rawParams = null)
    {
        $params = json_decode($rawParams, true);
        $page = &$this->page;

        if (method_exists($page, 'query')) {
            $this->result = $page->query($this->app, $params);
        } else if (property_exists($page, 'query')) {
            if (!is_array($page->query)) {
                throw new \Exception('Property $query must be an array in class . ' . get_class($this));
            }

            if (isset($page->query[$spec])) {
                throw new \Exception("Spec `{$spec}` is not declared in \$query in class " . get_class($this));
            }

            $this->result = $page->parseQuery($page->query[$spec], $params);

        } else {
            throw new \Exception('Property or Method Query does not exists in class ' . get_class($this));
        }

        return $this->result;
    }

}
