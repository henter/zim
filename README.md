# The Zim Framework

### About

Zim is a simple framework inspired(copy/paste 😄) by Yaf, Laravel, Symfony, based on Zephir Language.

Delivered as a C extension for the PHP language via [zim-ext](https://github.com/henter/zim-ext) , or you can choose the pure php implementation [zim-php](https://github.com/henter/zim-php)

### Requirements

0. PHP >= 7.0
1. Composer

### Install

build php extension from [zim-ext](https://github.com/henter/zim-ext)

or

install php implementation with composer

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

### Tests

see php version [zim-php](https://github.com/henter/zim-php)

### Documentation

https://github.com/henter/zim/wiki

### Contributing

Welcome !

### Licence

MIT
