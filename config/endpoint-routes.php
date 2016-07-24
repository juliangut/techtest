<?php

use Interop\Container\ContainerInterface;
use Jgut\TechTest\Exception\BadRequest;
use Jgut\TechTest\Exception\NotFound;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;
use Jgut\TechTest\Model\User;
use Jgut\TechTest\Model\UserRepository;

return [
    'endpoint.fetch' => function (ContainerInterface $container) {
        return function (Request $request, Response $response) use ($container) {
            $users = $container->get(UserRepository::class)->findAll();

            return $response->setBody($users);
        };
    },
    'endpoint.create' => function (ContainerInterface $container) {
        return function (Request $request, Response $response) use ($container) {
            $username = $request->getParam('username');
            $password = $request->getParam('password');
            $roles = $request->getParam('roles');

            if (empty($username) || empty($password) || empty($roles)) {
                throw new BadRequest($request, $response, 'Missing fields');
            }

            $user = new User;
            $user->setUsername($username);
            $user->setPassword($password);
            foreach (explode(',', $roles) as $role) {
                $user->addRole(trim($role));
            }

            $container->get(UserRepository::class)->save($user);

            return $response->setBody($user);
        };
    },
    'endpoint.edit' => function (ContainerInterface $container) {
        return function (Request $request, Response $response, array $arguments = []) use ($container) {
            $userRepository = $container->get(UserRepository::class);

            $user = $userRepository->findByUsername($arguments['userId']);
            if (!$user instanceof User) {
                throw new NotFound($request, $response);
            }

            switch ($request->getMethod()) {
                case 'GET':
                    return $response->setBody($user);
                    break;

                case 'PUT':
                    $password = $request->getParam('password');
                    $roles = $request->getParam('roles');

                    if (empty($password) || empty($roles)) {
                        throw new BadRequest($request, $response, 'Missing fields');
                    }

                    $user->setPassword($password);
                    $user->setRoles(explode(',', $roles));

                    $container->get(UserRepository::class)->update($user);

                    return $response->setBody($user);
                    break;

                case 'DELETE':
                    $container->get(UserRepository::class)->delete($user);

                    return $response->setBody($user);
                    break;
            }

            return $response->setBody(['action' => 'request/update/delete']);
        };
    },
];
