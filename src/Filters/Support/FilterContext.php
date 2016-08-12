<?php

namespace RickTap\Qriteria\Filters\Support;

class FilterContext
{
    public $query;
    public $position;
    public $command = "";
    public $operation = "and";

    public function __construct($query, $position = 0)
    {
        $this->query = $query;
        $this->position = $position;
    }
}
