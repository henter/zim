<?php
/**
 * File routes.php
 * @henter
 * Time: 2018-11-25 00:04
 *
 */

//TODO, route sugar function

use \Zim\Routing\Route;

Route::get('/test_reg', 'Index@test');
Route::any('/xx/{x<\d+>?1}', 'Index@test');

Route::post('/test_route1/{page<\d+>?123}', function($page) {
    return 'test ok';
});

Route::match(['POST', 'PUT', 'GET'], '/put', function() {
    return 'test match';
});

return [
    '/'                   => 'Index@index',
    'closure' => function() {
        return 'closure ok';
    },
    '/test'               => 'Index@test',
    '/demo'               => 'Demo/Index@test',
    '/post/{page<\d+>?1}' => 'Index@post',
    '/foo'                => 'Foo@index',
    '/foo/test'           => 'Foo@foo',
    '/hello/{world}' => [
        'to' => 'Index@hello',
        'methods' => ['GET', 'POST'],
        'requirements' => [
            'world' => '\d+',
        ],
        'defaults' => ['world' => 2],
        'options' => [],
    ],
];

