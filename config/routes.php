<?php
/**
 * File routes.php
 * @henter
 * Time: 2018-11-25 00:04
 *
 */

//TODO, route sugar function

use \Zim\Routing\Registrar;

Registrar::get('/test_reg', 'Index@test');
Registrar::any('/xx/{x<\d+>?1}', 'Index@test');

return [
    '/'                   => 'Index@index',
    '/test'               => 'Index@test',
    '/post/{page<\d+>?1}' => 'Index@post',
    '/foo'                => 'Foo@index',
    '/foo/test'           => 'Foo@foo',
];

