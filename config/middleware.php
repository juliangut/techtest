<?php

use function DI\get;
use function DI\object;
use function DI\value;
use Jgut\TechTest\Middleware\ACL;
use Jgut\TechTest\Middleware\HttpAuth;
use Jgut\TechTest\Middleware\Negotiation;
use Jgut\TechTest\Middleware\Session;
use Jgut\TechTest\Middleware\SessionAuth;
use Jgut\TechTest\Model\UserRepository;

return [
    'acl.roles' => value(['PAGE_1', 'PAGE_2', 'PAGE_3', 'ADMIN']),
    'acl.resources' => value(['/page1', '/page2', '/page3', '/api/users', '/api/user', '/api/user/{userId}']),
    'acl.rules' => value([
        ['role' => 'PAGE_1', 'resource' => '/page1'],
        ['role' => 'PAGE_2', 'resource' => '/page2'],
        ['role' => 'PAGE_3', 'resource' => '/page3'],
        ['role' => 'ADMIN',  'resource' => '/page1'],
        ['role' => 'ADMIN',  'resource' => '/page2'],
        ['role' => 'ADMIN',  'resource' => '/page3'],

        ['role' => 'PAGE_1', 'resource' => '/api/users'],
        ['role' => 'PAGE_2', 'resource' => '/api/users'],
        ['role' => 'PAGE_3', 'resource' => '/api/users'],
        ['role' => 'ADMIN',  'resource' => '/api/users'],

        ['role' => 'ADMIN',  'resource' => '/api/user'],

        ['role' => 'PAGE_1', 'resource' => '/api/user/{userId}', 'privilege' => 'get'],
        ['role' => 'PAGE_2', 'resource' => '/api/user/{userId}', 'privilege' => 'get'],
        ['role' => 'PAGE_3', 'resource' => '/api/user/{userId}', 'privilege' => 'get'],
        ['role' => 'ADMIN',  'resource' => '/api/user/{userId}'],
    ]),
    ACL::class => object()->constructor(get('acl.roles'), get('acl.resources'), get('acl.rules')),
    Session::class => object(),
    SessionAuth::class => object()->constructor(get(UserRepository::class)),
    HttpAuth::class => object()->constructor(get(UserRepository::class)),
    Negotiation::class => object(),
];
