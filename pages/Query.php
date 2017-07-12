<?php

namespace db\Pages;

class Query extends \Yard\Page
{
    public $norender = true;

    public function js() {
        return $this->loadFile('QueryLib.js','Query.js');
    }

    public function css() {
        return $this->loadFile('Query.css');
    }

    public function propTypes() {
        return [
            'spec' => 'string.isRequired',
            'tag' => 'string',
            'debug' => 'string',
            'params' => 'object',
            'onDone' => 'function',
        ];
    }

    public function render()
    {
        return $this->loadFile('Query.html');
    }
}
