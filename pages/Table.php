<?php

namespace db\Pages;

class Table extends \Yard\Page {

    public function isArray($value)
    {
        if (is_array($value)) return $value;
        return [];
    }

    public function query($app, $params)
    {
        $mode  = $params['mode'];
        $table = $params['table'];

        switch ($mode) {
            case 'create':
                $addColumns = $this->isArray(@$params['addColumns']);
                $primary = $this->isArray(@$params['primary']);
                $parents = $this->isArray(@$params['parents']);

                \Plansys\Db\Table::create($app, $table, $addColumns, $primary, $parents);
                $app->db->syncTable();
                break;
            
            case 'alter':
                $addColumns = $this->isArray(@$params['addColumns']);
                $changeColumns = $this->isArray(@$params['changeColumns']);
                $removeColumns = $this->isArray(@$params['removeColumns']);
                $primary = $this->isArray(@$params['primary']);
                $parents = $this->isArray(@$params['parents']);

                $app->db->tables[$table]->alter($addColumns, $changeColumns, $removeColumns, $primary, $parents);
                break;

            case 'addIndex':
                $app->db->tables[$table]->createIndex($params['index']);
                break;
            
            case 'changeIndex':
                $app->db->tables[$table]->changeIndex($params['index']);
                break;

            case 'dropIndex':
                $app->db->tables[$table]->dropIndex($params['indexName']);
                break;
            
            case 'addParent':
                $parent = $params['parent'];
                $app->db->tables[$table]->createParent($parent);
                break;

            case 'changeParent':
                $parent = $params['parent'];
                $app->db->tables[$table]->changeParent($parent);
                break;
            
            case 'dropParent':
                $parent = $params['parentName'];
                $app->db->tables[$table]->dropParent($parent);
                break;
            
            case 'drop':
                $app->db->tables[$table]->drop();
                break;
            
            case 'select':
                $where = $this->isArray($params['where']);
                $group = @$params['group'];
                $order = @$params['order'];
                $limit = @$params['limit'];

                return $app->db->tables[$table]->select($where, $group, $order, $limit);
                break;
            
            case 'insert':
                $app->db->tables[$table]->insert($params['values']);
                break;
            
            case 'update':
                $app->db->tables[$table]->update($params['values'], $params['where']);
                break;
            
            case 'delete':
                $app->db->tables[$table]->delete($params['where']);
                break;
            
            case 'truncate':
                break;
        }
    }
    
}