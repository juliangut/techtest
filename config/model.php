<?php

use function DI\value;
use Jgut\TechTest\Model\User;
use Jgut\TechTest\Model\UserRepository;

return [
    'db.file' => value(__DIR__ . '/db.sqlite3'),
    UserRepository::class => function (\Interop\Container\ContainerInterface $container) {
        $databaseFile = $container->get('db.file');
        $firstRun = !file_exists($databaseFile);

        $connection = new \PDO('sqlite:' . $databaseFile);
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        $repository = new UserRepository($connection);

        if ($firstRun) {
            $repository->query(
                'CREATE TABLE user (username TEXT, password TEXT, roles TEXT NOT NULL, PRIMARY KEY(username))'
            );

            // God
            // Authorization: Basic UmljaGFyZDpTdGFsbG1hbg==
            $stallman = new User();
            $stallman->setUsername('Richard');
            $stallman->setPassword('Stallman');
            $stallman->addRole('ADMIN');
            $repository->save($stallman);

            // C
            // Authorization: Basic RGVubmlzOlJpdGNoaWU=
            $ritchie = new User();
            $ritchie->setUsername('Dennis');
            $ritchie->setPassword('Ritchie');
            $ritchie->addRole('PAGE_1');
            $repository->save($ritchie);

            // PHP
            // Authorization: Basic UmFzbXVzOkxlcmRvcmY=
            $lerdorf = new User();
            $lerdorf->setUsername('Rasmus');
            $lerdorf->setPassword('Lerdorf');
            $lerdorf->addRole('PAGE_2');
            $repository->save($lerdorf);

            // JS
            // Authorization: Basic QnJlbmRhbjpFaWNo
            $eich = new User();
            $eich->setUsername('Brendan');
            $eich->setPassword('Eich');
            $eich->addRole('PAGE_3');
            $repository->save($eich);
        }

        return $repository;
    },
];
