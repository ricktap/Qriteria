<?php

return [
    "filter" => [
        "keyword" => "filter",
        //"class"   => "RickTap\\Qriteria\\Filters\\FieldArrayFilter"
        "class" => "RickTap\\Qriteria\\Filters\\NestedStringFilter"
    ],
    "sort" => [
        "keyword" => "sort"
    ],
    "include" => [
        "keyword" => "include"
    ],
    "fields" => [
        "keyword" => "fields"
    ],
    "errorMessages" => [
        "unbalancedParantheses" => "You provided an unbalanced amount of parantheses in you filter query."
    ]
];
