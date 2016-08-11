<?php

namespace RickTap\Qriteria\Interfaces;

interface Filter
{
    public function __construct($filterList);

    /**
     * Translates the $filterList property into commands and runs them on
     * the query object.
     *
     * @param  \Illuminate\Database\Query\Builder $query Query Object
     * @return null
     */
    public function filterOn($query);
}
