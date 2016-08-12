<?php

namespace RickTap\Qriteria\Filters;

use RickTap\Qriteria\Interfaces\Filter as FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use RickTap\Qriteria\Support\Parentheses;
use RickTap\Qriteria\Exceptions\UnbalancedParenthesesException;
use RickTap\Qriteria\Filters\Support\FilterContext;

/**
 * @implements RickTap\Qriteria\Filter
 */
class JsonApiFilter implements FilterInterface
{
    /**
     * Unprocessed list of filters.
     *
     * @var string
     */
    protected $filterRequest;

    protected $context;

    public function __construct($filterRequest)
    {
        $this->filterRequest = $filterRequest;
        if (!Parentheses::isBalanced($filterRequest)) {
            throw new UnbalancedParenthesesException();
        }
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
        $context = new FilterContext($query);
        $this->filterNested($context);
    }

    protected function filterNested(FilterContext $context)
    {
        $this->context = $context;

        // Iterate over each character of the filter string
        while ($this->context->position < strlen($this->filterRequest)) {
            $char = $this->getChar();
            switch ($char) {
                case '(':
                    $this->processNextLevel();
                    break;
                case ')':
                    $this->executeQueryAndResetCommand();
                    break 2;
                case ',':
                case '|':
                    $this->executeQueryAndResetCommand();
                    $this->setNextOperation();
                    break;
                default:
                    $this->appendCharToCommand($char);
            }
            $this->context->position++;
        }

        if (strlen($this->context->command) > 0) {
            $this->executeQueryAndResetCommand();
        }
    }

    protected function processNextLevel()
    {
        // make a backup of the current context
        $backup = $this->context;

        $this->context->query->where(function ($query) use ($backup) {
            $context = new FilterContext($query, $backup->position + 1);
            $this->filterNested($context);

            // restore the old context and get the new position
            $this->context = $backup;
            $this->context->position = $context->position + 1;

            return $context->query;
        });
    }

    protected function executeQueryAndResetCommand()
    {
        $parts = explode(":", $this->context->command);
        $this->context->query->where(
            $parts[0],
            $parts[1],
            $parts[2],
            $this->context->operation
        );
        $this->context->command = "";
    }

    public function getChar()
    {
        return $this->filterRequest[$this->context->position];
    }

    protected function appendCharToCommand($char)
    {
        $this->context->command .= $char;
    }

    protected function setNextOperation()
    {
        $nxtOp = ($this->getChar() === ',') ? "and" : "or";
        $this->context->operation = $nxtOp;
    }
}
