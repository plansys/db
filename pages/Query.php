<?php

namespace db\Pages;

class Query extends \Yard\Page
{
    public $norender = true;

    public function js() {
        return $this->loadFile('Query.js');
    }

    public function propsTypes() {
        return [
            'spec' => 'string.isRequired',
            'tag' => 'string',
            'params' => 'object'
        ];
    }

    public function render()
    {
        return $this->loadFile('Query.html');
    }
}
