<?php
/**
 * File routes.php
 * @henter
 * Time: 2018-11-25 00:04
 *
 */

//TODO, route sugar function

return [
    ['/', 'Index@index'],
    ['/test', 'Index@test'],
    ['/post/{page<\d+>?1}', 'Index@post'],
    ['/foo', 'Foo@index'],
    ['/foo/test', 'Foo@foo'],
];

