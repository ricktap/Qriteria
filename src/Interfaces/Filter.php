<?php

namespace RickTap\Qriteria\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    public function __construct($filterRequest);

    /**
     * Translates the $filterRequest property into commands and runs them on
     * the query object.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query Query Object
     * @return null
     */
    public function filterOn(Builder $query);
}
