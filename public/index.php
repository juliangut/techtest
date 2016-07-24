<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/TechTest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

require __DIR__ . '/../vendor/autoload.php';

use Jgut\TechTest\App;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;

$DIContainer = require(__DIR__ . '/../config/dic.php');

$app = new App();

$app->get('/', function (Request $request, Response $response) {
    return $response->setRedirect('/login');
});

$app->map(['get', 'post'], '/login', $DIContainer->get('front.login'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\Session'));

$app->get('/logout', $DIContainer->get('front.logout'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\Session'));


$app->get('/page1', $DIContainer->get('front.page1'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\ACL'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\SessionAuth'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\Session'));

$app->get('/page2', $DIContainer->get('front.page2'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\ACL'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\SessionAuth'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\Session'));

$app->get('/page3', $DIContainer->get('front.page3'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\ACL'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\SessionAuth'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\Session'));


$app->get('/api/users', $DIContainer->get('endpoint.fetch'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\ACL'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\HttpAuth'));

$app->post('/api/user', $DIContainer->get('endpoint.create'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\ACL'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\HttpAuth'));

$app->map(['get', 'put', 'delete'], '/api/user/{userId}', $DIContainer->get('endpoint.edit'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\ACL'))
    ->add($DIContainer->get('Jgut\TechTest\Middleware\HttpAuth'));

$app->add($DIContainer->get('Jgut\TechTest\Middleware\Negotiation'));

$request = new Request($_SERVER);
$response = new Response();

$app->run($request, $response);
