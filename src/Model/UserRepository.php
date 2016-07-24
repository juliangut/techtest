<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Model;

class UserRepository
{
    /**
     * @var \PDO
     */
    protected $database;

    /**
     * User constructor.
     *
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        $this->database = $connection;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $usersData = $this->exec('SELECT * FROM user');

        if (is_array($usersData)) {
            return array_map(
                function ($userData) {
                    $user = new User();
                    $user->setUsername($userData['username']);

                    foreach (explode(';', $userData['roles']) as $role) {
                        $user->addRole($role);
                    }

                    return $user;
                },
                $usersData
            );
        }

        return [];
    }

    /**
     * @param string $username
     *
     * @return User|null
     */
    public function findByUsername($username)
    {
        $userData = $this->exec('SELECT * FROM user WHERE username = :username', ['username' => $username], true);

        if (is_array($userData)) {
            $user = new User();
            $user->setUsername($userData['username']);

            foreach (explode(';', $userData['roles']) as $role) {
                $user->addRole($role);
            }

            return $user;
        }

        return null;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return User|null
     */
    public function findByCredentials($username, $password)
    {
        $userData = $this->exec('SELECT * FROM user WHERE username = :username', ['username' => $username], true);

        if (is_array($userData)) {
            if (password_verify($password, $userData['password'])) {
                $user = new User();
                $user->setUsername($userData['username']);

                foreach (explode(';', $userData['roles']) as $role) {
                    $user->addRole($role);
                }

                return $user;
            }
        }

        return null;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function save(User $user)
    {
        return $this->exec(
            'INSERT INTO user (username, password, roles) VALUES (:username, :password, :roles)',
            [
                'username' => $user->getUsername(),
                'password' => password_hash($user->getPassword(), PASSWORD_DEFAULT),
                'roles' => implode(';', $user->getRoles())
            ]
        );
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        if ($user->getPassword()) {
            $query = 'UPDATE user SET password = :password, roles = :roles WHERE username = :username';

            $parameters = [
                'password' => password_hash($user->getPassword(), PASSWORD_DEFAULT),
                'roles' => implode(';', $user->getRoles()),
                'username' => $user->getUsername(),
            ];
        } else {
            $query = 'UPDATE user SET roles = :roles WHERE username = :username';
            $parameters = [
                'roles' => implode(';', $user->getRoles()),
                'username' => $user->getUsername(),
            ];
        }

        return $this->exec($query, $parameters);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $this->exec('DELETE FROM user WHERE username = :username', ['username' => $user->getUsername()]);
    }

    /**
     * @param string $query
     *
     * @return array|bool
     */
    public function query($query)
    {
        return $this->exec($query);
    }

    /**
     * @param string $query
     * @param array  $parameters
     * @param bool   $single
     *
     * @return array|bool
     */
    private function exec($query, array $parameters = [], $single = false)
    {
        $statement = $this->database->prepare($query);

        foreach ($parameters as $parameter => $value) {
            $parameter = ':' . ltrim($parameter, ':');

            $statement->bindValue($parameter, $value);
        }

        $status = $statement->execute();

        if (!$status) {
            return false;
        }

        $results = $statement->fetchAll();

        if (is_array($results) && count($results)) {
            return $single ? $results[0] : $results;
        }

        return $status;
    }
}
