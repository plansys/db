<?php

namespace db\Pages;

class Query extends \Yard\Page
{
    public $norender = true;

    public function js() {
        return $this->loadFile('QueryLib.js','Query.js');
    }

    public function propTypes() {
        return [
            'spec' => 'string.isRequired',
            'tag' => 'string',
            'debug' => 'string',
            'params' => 'object',
            'bind' => 'function'
        ];
    }

    public function render()
    {
        return $this->loadFile('Query.html');
    }
}
