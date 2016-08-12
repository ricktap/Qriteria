<?php

namespace RickTap\Qriteria\Support;

class Parentheses
{
    public static $open  = "(";
    public static $close = ")";

    /**
     * Checks a string for balanced parantheses.
     * It checks if there is an equal amount of open and closed parens,
     * and if they the are in the right order.
     * Nesting and other characters are allowed.
     * Examples:
     * true:  (() test (a))
     * false: ())()
     *
     * @param  string  $string String to test the balance on.
     * @return boolean (true: balanced | false: unbalanced)
     */
    public static function isBalanced($string)
    {
        // Keep track of number of open parens
        $open = 0;

        // Loop through each char
        for ($i = 0; $i < strlen($string); $i++) {
            $char = $string[$i];
            switch ($char) {
                case static::$open:
                    $open++;
                    break;
                case static::$close:
                    $open--;
                    if ($open < 0) {
                        return false;
                    }
                    break;
            }
        }

        // there must be an equal amount of opened and closed parens
        return ($open === 0);
    }
}
