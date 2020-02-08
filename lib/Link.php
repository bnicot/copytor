<?php

namespace lib;

class Link
{

    public $srcTable;
    public $srcField;
    public $destTable;
    public $destField;

    public function __construct(Table $srcTable, $srcField, Table $destTable, $destField)
    {
        $this->srcTable = $srcTable;
        $this->srcField = $srcField;
        $this->destTable = $destTable;
        $this->destField = $destField;
    }

}