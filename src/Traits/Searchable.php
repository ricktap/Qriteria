<?php

namespace RickTap\Qriteria\Traits;

use RickTap\Qriteria\Query;

trait Searchable
{
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

    static public function Qriteria(callable $queryCall = null)
    {
        return new Query(new self, $queryCall);
    }
}
