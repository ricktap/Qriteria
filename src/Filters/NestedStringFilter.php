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
class NestedStringFilter implements FilterInterface
{
    /**
     * Unprocessed list of filters.
     *
     * @var string
     */
    protected $filterRequest;

    /**
     * Holds the context of recursively called filterNested.
     *
     * @var \RickTap\Qriteria\Filters\Support\FilterContext
     */
    protected $context;

    public function __construct($filterRequest)
    {
        if (is_string($filterRequest)) {
            $this->filterRequest = $filterRequest;
            if (!Parentheses::isBalanced($filterRequest)) {
                throw new UnbalancedParenthesesException();
            }
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
        $this->filterNested(new FilterContext($query));
    }

    /**
     * Recursive method, that is controlled by a context object.
     * It translates the filter request into nested commands.
     *
     * @param  \RickTap\Qriteria\Filters\Support\FilterContext $context
     * @return null
     */
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

    /**
     * Calls the filterNested method with a new context inside a nested query.
     *
     * @return null
     */
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

    /**
     * Executes the query defined inside the context command and
     * then resets the context commands.
     *
     * @return null
     */
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

    /**
     * Grabs a single character from the filterRequest at
     * the context position.
     *
     * @return char Single character from filterRequest
     */
    public function getChar()
    {
        return $this->filterRequest[$this->context->position];
    }

    /**
     * Builds up the command string char by char.
     *
     * @param  char $char Single Character
     * @return null
     */
    protected function appendCharToCommand($char)
    {
        $this->context->command .= $char;
    }

    /**
     * Sets the next operation glue to 'and' or 'or'.
     *
     * @return null
     */
    protected function setNextOperation()
    {
        $nxtOp = ($this->getChar() === ',') ? "and" : "or";
        $this->context->operation = $nxtOp;
    }
}
