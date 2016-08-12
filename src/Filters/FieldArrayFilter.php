<?php

namespace RickTap\Qriteria\Filters;

use RickTap\Qriteria\Interfaces\Filter as FilterInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @implements RickTap\Qriteria\Filter
 */
class FieldArrayFilter implements FilterInterface
{
    protected $filterRequest;

    public function __construct($filterRequest)
    {
        $this->filterRequest = $filterRequest;
    }

    /**
     * Translates the $filterRequest property into commands and runs them on
     * the query object.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query Query Object
     * @return null
     */
    public function filterOn(Builder $query)
    {
        if (is_array($this->filterRequest)) {
            foreach ($this->filterRequest as $field => $value) {
                $query->where($field, "=", $value);
            }
        }
    }
}
