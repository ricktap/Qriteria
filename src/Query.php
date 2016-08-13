<?php

namespace RickTap\Qriteria;

use Illuminate\Database\Eloquent\Model;
use RickTap\Qriteria\NestedFilter;

class Query
{
    static protected $filterOperators = [
        "eq"   => "=",
        "leq"  => "<=",
        "geq"  => ">=",
        "lt"   => "<",
        "gt"   => ">",
        "like" => "LIKE",
        "neq"  => "!=",
    ];

    /**
     * Default maximum number of returned results.
     *
     * @var integer
     */
    static protected $defaultLimit = 3;

    /**
     * Model the query is based on.
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Model query.
     *
     * @var Illuminate\Database\Eloquent\Model|string
     */
    protected $query;

    /**
     * Maximum number of returned results.
     *
     * @var integer
     */
    protected $limit;

    /**
     * Search terms, that are allowed to be used in the filter.
     *
     * @var array
     */
    protected $allowedSearchTerms;

    /**
     * Holds the specified fields to select them during the
     * include phase.
     *
     * @var array
     */
    protected $includeFieldSelects = array();

    protected $request;

    /**
     * Create a new Request Criteria Query instance.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param callable|null $queryCall call to query. Can be used for one-off queries
     */
    public function __construct(Model $model, callable $queryCall = null)
    {
        // backup the model and query object
        $this->model = $model;
        $this->query = $model->query();

        // get the request from the app
        $this->request = request();

        $this->prepareQueryIfTrait();

        if (is_callable($queryCall)) {
            $this->query = $queryCall($this->query);
        }

        $this->grabLimit()
             ->selectFields()
             ->includeRelations()
             ->orderBy()
             ->filter();
    }

    /**
     * Interface to further enhance the query.
     *
     * @param  callable $queryCall closure that works like a scope
     * @return $this
     */
    public function query(callable $queryCall)
    {
        $this->query = $queryCall($this->query);

        return $this;
    }

    /**
     * Returns a limited collection of 'model' objects.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function get()
    {
        return $this->query
                    ->limit($this->limit)
                    ->get();
    }

    /**
     * Returns an unlimited collection of 'model' objects.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->query
                    ->get();
    }

    /**
     * Returns a paginated collection of 'model' objects.
     *
     * @return Illuminate\Contracts\Pagination\Paginator
     */
    public function paginate()
    {
        return $this->query
                    ->paginate($this->limit)
                    ->appends($this->request->except('page'));
    }

    /**
     * Removes the limit of the request, if used
     * in conjunction with paginate(), the default limit
     * of laravel will be used instead.
     *
     * @return $this
     */
    public function unlimited()
    {
        $this->limit = null;
        return $this;
    }

    /**
     * Grabs the limit from the get parameter 'limit'.
     *
     * @return $this
     */
    protected function grabLimit()
    {
        $this->limit = $this->request->input('limit', static::$defaultLimit);
        return $this;
    }

    /**
     * Orders the output, based on the orderBy get parameter, also
     * translates the '-' prefix, to a desc order.
     *
     * @return $this
     */
    protected function orderBy()
    {
        $orders = explode(',', $this->request->input('orderBy'));

        foreach ($orders as $order) {
            if (!empty($order)) {
                $this->orderQuery($order);
            }
        }

        return $this;
    }

    /**
     * Selects the fields of the master model (the one provided in constructor)
     * and translates the other specified fields for the includes.
     * The Selection is based on a plain fields parameter or a fields array
     * with a key representing the lowercase model name or the table name.
     *
     * @return $this
     */
    protected function selectFields()
    {
        $fields = $this->grabFields();
        $selectedFields = "*";

        foreach ($fields as $table => $fieldset) {
            if ($this->fieldsetBelongsToModel($table)) {
                $selectedFields = explode(",", $fieldset);
            } else {
                $this->includeFieldSelects[$table] = explode(",", $fieldset);
            }
        }

        $this->query->select($selectedFields);

        return $this;
    }

    /**
     * Iterates over the include get parameter and includes
     * all of the specified relations. Also grabs only the selected fields
     * if provided as a get parameter.
     *
     * @return $this
     */
    protected function includeRelations()
    {
        $includes = explode(',', $this->request->input('include', ''));

        foreach ($includes as $relation) {
            if (!empty($relation)) {
                $this->includeRelation($relation);
            }
        }

        return $this;
    }

    /**
     * Filters the query by the request parameter 'filter'
     *
     * @return $this
     */
    protected function filter()
    {
        $filterList = $this->request->input('filter');

        $FilterClass = $this->model->getFilterClass();

        $filter = new $FilterClass($filterList);
        $filter->filterOn($this->query);

        return $this;
    }

    /**
     * Sets the allowed search terms and runs the prepare search query of the
     * model if the CriteriaSearchable Trait has been used.
     *
     * @return null
     */
    protected function prepareQueryIfTrait()
    {
        $this->allowedSearchTerms = [];

        if (in_array("RickTap\\Qriteria\\Traits\\Searchable", class_uses($this->model))) {
            $this->query = $this->model->prepareSearchQuery($this->query);
        }
    }

    /**
     * Orders the query based on the '-' prefix.
     *
     * @param  string $order fieldname with optional prefix
     * @return null
     */
    private function orderQuery($order)
    {
        $direction = 'asc';
        if (substr($order, 0, 1) === '-') {
            $direction = 'desc';
            $order = substr($order, 1);
        }

        $this->query->orderBy($order, $direction);
    }

    /**
     * Includes a relationship model and selects the specified fields
     * if any provided.
     *
     * @param  string $relation the relationship model
     * @return null
     */
    private function includeRelation($relation)
    {
        if (array_key_exists($relation, $this->includeFieldSelects)) {
            $fields = $this->includeFieldSelects[$relation];
            $relation = [$relation => function ($qry) use ($fields) {
                return $qry->select($fields);
            }];
        }
        $this->query->with($relation);
    }

    /**
     * Checks if the specified fieldset belongs to the Query Model.
     *
     * @param  numeric|string $fieldset
     * @return null
     */
    private function fieldsetBelongsToModel($fieldset)
    {
        return  is_numeric($fieldset) ||
                $fieldset == strtolower(class_basename($this->model)) ||
                $fieldset == $this->model->getTable();
    }

    /**
     * Grabs the fields from the Input and makes sure, that the data is always
     * returned in form of an array.
     *
     * @return array Fieldsets
     */
    private function grabFields()
    {
        $fields = $this->request->input('fields', []);
        return (is_array($fields)) ? $fields : [$fields];
    }
}
