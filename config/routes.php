<?php
/**
 * File routes.php
 * @henter
 * Time: 2018-11-25 00:04
 *
 */

return [
    ['/', 'Index@index'],
    ['/test', 'Index@test'],
    ['/foo', 'Foo@index'],
    ['/foo/test', 'Foo@foo'],
];

