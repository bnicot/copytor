<?php

namespace lib;

class Table
{

    public $name;
    public $searchOn = [];
    public $links = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function searchOn($column, $value) {
        $this->searchOn[] = ['column' => $column, 'value' => $value];
    }

    public function link($sourceField, Table $destTable, $destField) {
        $this->links[] = new Link($this, $sourceField, $destTable, $destField);
    }

}