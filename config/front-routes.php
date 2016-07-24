<?php

use Interop\Container\ContainerInterface;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;
use Jgut\TechTest\Model\User;
use Jgut\TechTest\Model\UserRepository;

return [
    'front.login' => function (ContainerInterface $container) {
        return function (Request $request, Response $response) use ($container) {
            $username = '';

            if ($request->getMethod() === 'POST') {
                $username = $request->getParam('username');
                $password = $request->getParam('password');

                $user = $container->get(UserRepository::class)->findByCredentials($username, $password);

                if ($user instanceof User) {
                    $_SESSION['user'] = $username;

                    // Map roles to pages
                    $rolePathMap = [
                        'ADMIN' => '/page1',
                        'PAGE_1' => '/page1',
                        'PAGE_2' => '/page2',
                        'PAGE_3' => '/page3',
                    ];

                    return $response->setRedirect($rolePathMap[$user->getRoles()[0]]);
                }
            }

            $body = <<<EOL
    <html>
        <head>
            <title>Login</title>
            <link href="/vendor/bootstrap/dist/css/bootstrap.min.css" media="all" rel="stylesheet" />
        </head>
        <body>
            <div class="container"><div class="row"><div class="col-xs-4 col-xs-offset-4 text-center">
                <h1>Login</h1>
                <form method="post">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="user" value="$username">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" placeholder="password" value="">
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Access">
                    </div>
                </form>
            </div></div></div>
        </body>
    </html>
EOL;

            return $response->setBody($body);
        };
    },
    'front.logout' => function () {
        return function (Request $request, Response $response) {
            unset($_SESSION['user']);
            session_reset();
            session_destroy();

            return $response->setRedirect('/login');
        };
    },

    'front.template' => function () {
        return function ($title, $username) {
            $content = <<<EOL
<html>
    <head>
        <title>$title</title>
        <link href="/vendor/bootstrap/dist/css/bootstrap.min.css" media="all" rel="stylesheet" />
    </head>
    <body>
        <div class="container"><div class="row"><div class="col-xs-4 col-xs-offset-4 text-center">
            <h4>$title</h4>
            <h1>Hello $username</h1>
            <a class="btn btn-primary" href="/logout">Logout</a>
        </div></div></div>
    </body>
</html>
EOL;

            return $content;
        };
    },

    'front.page1' => function (ContainerInterface $container) {
        return function (Request $request, Response $response) use ($container) {
            $template = $container->get('front.template');

            return $response->setBody($template('Page 1', $request->getAttribute('user')->getUsername()));
        };
    },
    'front.page2' => function (ContainerInterface $container) {
        return function (Request $request, Response $response) use ($container) {
            $template = $container->get('front.template');

            return $response->setBody($template('Page 2', $request->getAttribute('user')->getUsername()));
        };
    },
    'front.page3' => function (ContainerInterface $container) {
        return function (Request $request, Response $response) use ($container) {
            $template = $container->get('front.template');

            return $response->setBody($template('Page 3', $request->getAttribute('user')->getUsername()));
        };
    },
];
