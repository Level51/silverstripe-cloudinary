# sminnee/callbacklist

[![Build Status](https://travis-ci.org/sminnee/callbacklist.svg?branch=master)](https://travis-ci.org/sminnee/callbacklist)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sminnee/callbacklist/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sminnee/callbacklist/?branch=master)
[![codecov.io](https://codecov.io/github/sminnee/callbacklist/coverage.svg?branch=master)](https://codecov.io/github/sminnee/callbacklist?branch=master)

[![Latest Stable Version](https://poser.pugx.org/sminnee/callbacklist/version)](https://packagist.org/packages/sminnee/callbacklist)
[![License](https://poser.pugx.org/sminnee/callbacklist/license)](https://packagist.org/packages/sminnee/callbacklist)
[![Monthly Downloads](https://poser.pugx.org/sminnee/callbacklist/d/monthly)](https://packagist.org/packages/sminnee/callbacklist)

[![GitHub Code Size](https://img.shields.io/github/languages/code-size/sminnee/callbacklist)](https://github.com/sminnee/callbacklist)
[![GitHub Last Commit](https://img.shields.io/github/last-commit/sminnee/callbacklist)](https://github.com/sminnee/callbacklist)
[![GitHub Activity](https://img.shields.io/github/commit-activity/m/sminnee/callbacklist)](https://github.com/sminnee/callbacklist)
[![GitHub Issues](https://img.shields.io/github/issues/sminnee/callbacklist)](https://github.com/sminnee/callbacklist/issues)

This micropackage provides a simple class for managing a list of callbacks.

## Usage

```
> composer require sminnee/callbacklist
```

```php
use Sminnee\CallbackList\CallbackList;

$list = new CallbackList;
$list->add(function() { "this will get called"; });
$list->add(function() { "so will this"; });
$list->call();

// Or you can use it as a callable if you prefer
$list();
```

Arguments can be passed:

```php
$list->add(function($greeting) { "$greeting, world!"; });
$list("Hello");
```

Return values are collated as an array

```php
use Sminnee\CallbackList\CallbackList;

$list = new CallbackList;
$list->add(function() { return "this will get returned"; });
$list->add(function() { return "so will this"; });

// ["this will get returned", "so will this"]
var_dump($list());
```

Existing callbacks can be manipulated:

```php
// Clear the list
$list->clear();

// Or add a callback with a name
$list->add(function($greeting) { "$greeting, world!"; }, 'greeter');

// And then remove by that name
$list->remove('greeter');
```

And you can inspect the content of the list:

```php
// Return a single named callback
$list->get('greeter');

// Return everything as an array
$list->getAll();
```
