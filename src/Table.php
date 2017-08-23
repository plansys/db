<?php

namespace Plansys\Db;

class Table {

    public $init;
    public $sm;
    public $command;
    public $table;
    public $childs = [];

    public function __construct($init, $table) 
    {
        $this->init = $init;
        $this->sm = $this->init->conn->getSchemaManager();
        $this->command = $this->init->notorm;
        $this->table = $table;
    }

    public static function create($app, string $tableName, array $addColumns, array $primary = [], array $parents = [], $idGeneratorType = 0, array $options=[])
    {
        $sm = $app->db->conn->getSchemaManager();
        
        $addColumns = Column::setMultiple($addColumns);
        $primary = !empty($primary) ? array(Index::set($primary)) : [];
        $parents = ForeignKey::setMultiple($parents);
        
        $table = new \Doctrine\DBAL\Schema\Table($tableName, $addColumns, $_primary, $parents, $idGenerator, $options);
        return $sm->createTable($table);
    }

    public function alter(array $addColumns = [], array $changeColumns = [], array $removeColumns = [], array $primary = [], array $parents = [])
    {        
        $addColumns = $this->addColumns($addColumns);
        $changeColumns = $this->changeColumns($changeColumns);
        $removeColumns = $this->removeColumns($removeColumns);
        $primary = !empty($primary) ? array('primary' => Index::set($primary)) : [];

        $tableDiff = new \Doctrine\DBAL\Schema\TableDiff($this->table, $addColumns, $changeColumns, $removeColumns, $primary);
        $this->sm->alterTable($tableDiff);
        
        foreach ($parents as $parent) {
            $this->createParent($parent);
        }
        if (!empty($parents)) $this->unsetChild();

        return;
    }

    public function rename($newName)
    {
        $this->sm->renameTable($this->table, $newName);
    }

    public function drop()
    {
        $this->unsetChild();
        return $this->sm->dropTable($this->table);
    }

    // columns

    public function addColumns($columns)
    {
        return Column::setMultiple($columns);
    }

    public function changeColumns($columns)
    {
        $result = [];
        foreach ($columns as $column) {
            $result[] = Column::setDiff($column);
        }

        return $result;
    }

    public function removeColumns($columns)
    {
        return Column::setMultiple($columns);
    }

    // index

    public function createIndex($index)
    {
        $index = Index::set($index);
        return $this->sm->createIndex($index, $this->table);
    }

    public function changeIndex($index)
    {
        $index = Index::set($index);
        return $this->sm->dropAndCreateIndex($index, $this->table);
    }

    public function renameIndex($oldName, $newName)
    {
        return;
    }

    public function dropIndex($index)
    {
        return $this->sm->dropIndex($index, $this->table);
    }

    // foreign key

    public function createParent($fk)
    {
        $this->unsetChild();
        $fk = ForeignKey::set($fk);
        return $this->sm->CreateForeignKey($fk, $this->table);
    }

    public function changeParent($fk)
    {
        $this->unsetChild();
        $fk = ForeignKey::set($fk);
        return $this->sm->dropAndCreateForeignKey($fk, $this->table);
    }

    public function renameParent($oldName, $newName)
    {
        return;
    }

    public function dropParent($fk)
    {
        $this->unsetChild();
        return $this->sm->dropForeignKey($fk, $this->table);
    }

    // structure

    public function getColumns()
    {
        $columns = $this->sm->listTableColumns($this->table);
        $result = [];
        foreach ($columns as $column) {
            $result[] = array_merge([
                'name'             => $column->getName(),
                'type'             => $column->getType()->getBindingType(),
                'default'          => $column->getDefault(),
                'notnull'          => $column->getNotnull(),
                'length'           => $column->getLength(),
                'precision'        => $column->getPrecision(),
                'scale'            => $column->getScale(),
                'fixed'            => $column->getFixed(),
                'unsigned'         => $column->getUnsigned(),
                'autoIincrement'   => $column->getAutoincrement(),
                'columnDefinition' => $column->getColumnDefinition(),
                'comment' => $column->getComment,
            ], $column->getPlatformOptions(), $column->getCustomSchemaOptions());
        }
        return $result;
    }

    public function getIndexes()
    {
        $indexes = $this->sm->listTableIndexes($this->table);
        $result = [];
        foreach ($indexes as $index) {
            if ($index->isPrimary()) {
                $type = 'Primary';
            } else if ($index->isUnique()) {
                $type = 'Unique';
            } else {
                $type = 'Index';
            }

            $result[] = [
                'name' => $index->getName(),
                'column' => $index->getColumns()[0],
                'type'   => $type
            ];
        }
        return $result;
    }

    public function unsetChild()
    {
        $this->childs = [];
    }

    public function getChilds()
    {
        if (empty($this->childs)) {
            $tables = $this->sm->listTableNames();
            foreach ($tables as $table) {
                $parents = $this->sm->listTableForeignKeys($table);
                foreach ($parents as $parent) {
                    if ($parent->getForeignTableName() == $this->table) {
                        $this->childs[$table] = $parent->getColumns();
                    }
                }
            }
        }
        return $this->childs;
    }

    public function getParents()
    {
        $foreignKeys = $this->sm->listTableForeignKeys($this->table);
        
        $result = [];
        foreach ($foreignKeys as $foreignKey) {
            $result[] = [
                'name'       => $foreignKey->getname(),
                'column'     => $foreignKey->getColumns()[0],
                'refTable'   => $foreignKey->getForeignTableName(),
                'refColumns' => $foreignKey->getForeignColumns()[0],
                'onUpdate'   => $foreignKey->onUpdate(),
                'onDelete'   => $foreignKey->onDelete()
            ];
        }

        return $result;
    }

    // data

    public function select($where, $group, $order, $limit)
    {
        $result = $this->init->notorm->{$this->table}()->select('*');

        if (!empty($where)) $result->where($where);
        if (!is_null($group)) $result->group($group);
        if (!is_null($order)) $result->order($order);
        if (!is_null($limit)) $result->limit($limit);
        
        return $result;
    }

    public function insert($values)
    {
        return $this->init->notorm->{$this->table}()->insert($values);
    }

    public function update($values, $where)
    {
        $data = $this->init->notorm->{$this->table}()->where($where);
        return $data->update($values);
    }

    public function delete($where)
    {
        $data = $this->init->notorm->{$this->table}()->where($where);
        return $data->delete();
    }
}