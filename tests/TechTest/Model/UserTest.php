<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Tests\Model;

use Jgut\TechTest\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->user = new User;
    }

    public function testUsername()
    {
        self::assertEmpty($this->user->getUsername());

        $this->user->setUsername('UserName');
        self::assertEquals('UserName', $this->user->getUsername());
    }

    public function testPassword()
    {
        self::assertEmpty($this->user->getPassword());

        $this->user->setPassword('UserPassword');
        self::assertEquals('UserPassword', $this->user->getPassword());
    }

    public function testRoles()
    {
        self::assertEmpty($this->user->getRoles());

        $this->user->addRole('ROLE1');
        self::assertCount(1, $this->user->getRoles());
        self::assertEquals('ROLE1', $this->user->getRoles()[0]);

        $this->user->setRoles(['ROLE2', 'ROLE3']);
        self::assertCount(2, $this->user->getRoles());
        self::assertEquals('ROLE2', $this->user->getRoles()[0]);
        self::assertEquals('ROLE3', $this->user->getRoles()[1]);
    }

    public function testSerialization()
    {
        $this->user->setUsername('UserName');
        $this->user->setPassword('UserPassword');
        $this->user->setRoles(['ROLE1', 'ROLE2']);

        $expected = [
            'username' => 'UserName',
            'roles' => [
                'ROLE1',
                'ROLE2',
            ]
        ];

        self::assertEquals(json_encode($expected), json_encode($this->user));
    }
}
