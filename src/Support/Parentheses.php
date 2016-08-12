<?php

namespace RickTap\Qriteria\Support;

class Parentheses
{
    public static $open  = "(";
    public static $close = ")";

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

        return ($open === 0);
    }
}
