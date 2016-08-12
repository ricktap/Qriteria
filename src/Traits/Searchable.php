<?php

namespace RickTap\Qriteria\Traits;

use RickTap\Qriteria\Query;

trait Searchable
{
    public $filterClass;

    /**
     * This method should be overriden to do stuff like authentication before
     * the request parameters get added to the query
     *
     * @param  string $query
     * @return string Return the query object, as you would in any scope method
     */
    public function prepareSearchQuery($query)
    {
        return $query;
    }

    static public function qriteria(callable $queryCall = null)
    {
        return new Query(new self, $queryCall);
    }

    public function getFilterClass()
    {
        if (property_exists($this, 'filterClass') && strlen($this->filterClass) > 0) {
            return $this->filterClass;
        }
        return config("qriteria.filter.class");
    }
}
