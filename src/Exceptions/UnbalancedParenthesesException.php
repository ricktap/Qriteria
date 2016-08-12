<?php

namespace RickTap\Qriteria\Exceptions;

class UnbalancedParenthesesException extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        $message = (empty($message)) ?
            config("qriteria.errorMessages.unbalancedParantheses") :
            $message;

        parent::__construct($message, $code, $previous);
    }
}
