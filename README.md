# [WIP] The Zim Framework

### About

Zim is a simple framework inspired by Yaf, Laravel, Symfony, based on Zephir Language.

Delivered as a C extension for the PHP language via [zim](https://github.com/henter/zim) , or you can choose this pure php implementation.

The demo project [zim-demo](https://github.com/henter/zim-demo)


### Requirements

0. PHP >= 7.0
1. Composer

### Install

`composer require henter/zim`

### Usage

simple demo with php build-in server

index.php
```php
<?php
require __DIR__.'/vendor/autoload.php';

use \Zim\Zim;
use \Zim\Routing\Route;

Route::get('/', function() {
    return 'hello zim';
});

Zim::run();

```

start simple server:

`php -S localhost:8888`

open [http://localhost:8888](http://localhost:8888)

more usage at [zim-demo](https://github.com/henter/zim-demo)

### Tests

see php version [zim-php](https://github.com/henter/zim-php)

### Documentation

TODO

### Contributing

Welcome !

### Licence

MIT
