# Qriteria

Home of Qriteria:
[https://github.com/ricktap/Qriteria](https://github.com/ricktap/Qriteria)

Qriteria is a frankenword containing the terms Query and Criteria.
It is intended to take the $_GET Parameters of a request and maps the words
*filter*, *fields*, *sort*, *include* and *limit* as described in the [JSON API
Specification](http://jsonapi.org/format/#fetching) to the query of an eloquent
model.

Qriterias *fluent Api* allows for three query hooks:
- class based pre-hooks, that are run before every query it sends on every instance of your model
- on-call queries, that are called before the translation but after your pre-hook and only for this one instance object
- method-based queries, those are called after the translation and can be used like a scope method in eloquent

## Installation

```shell
composer require ricktap/qriteria
```

add the following to your config/app.php inside the providers array

```php
RickTap\Qriteria\QriteriaServiceProvider::class,
```

## Configuration

To configure Qriteria, you can generate the config/qriteria.php config file by 
running the command:

```shell
php artisan vendor:publish
``` 
## Filtering

### Syntax: 
```
filter=name:like:John%,(age:gt:60|age:lt:20),status:eq:occupied
```

### Result: 
```php
$query->where("name","LIKE","John%")->where(function ($q) { 
  return $q->where("age",">",60)->orWhere("age","<",20);
})->where("status","=","occupied");
```

### Description:
Every filter statement consists of three parts, separated by colons (:). The first
part represents the field to query, the second part is the operation, that is
available as a symbol or a word (i.e. you can use = or eq for the same thing)
the third part is the value to compare to. the filter statements are separated
by a comma (,) or a pipe (|). The comma means **and** the pipe means **or**.
Statements can be wrapped into parenthesis like in a mathematical function, to
sort their execution sequence.

## Todo

- Write a more exhaustive documentation and maybe even a wiki
- Write more tests
- Have more checks in place
- Map eq to =, lt to <, etc.
- Split the searchable trait into two separate traits. one for filter and one for qriteria itself
- further validate the user input, right now the only i'm doing is on the parentheses to see if they are balanced and in order

