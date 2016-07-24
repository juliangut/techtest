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
use Jgut\TechTest\Model\UserRepository;

class UserRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testWrongQuery()
    {
        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::any())->method('execute')->will(self::returnValue(false));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())->method('prepare')->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        self::assertFalse($repository->query(''));
    }

    public function testEmptyQuery()
    {
        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue([]));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())->method('prepare')->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        self::assertTrue($repository->query(''));
    }

    public function testQuery()
    {
        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue([]));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())->method('prepare')->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        $user = new User;
        $user->setUsername('UserName');

        self::assertTrue($repository->delete($user));
    }

    public function testAllEmpty()
    {
        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue([]));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())->method('prepare')->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        self::assertCount(0, $repository->findAll());
    }

    public function testAll()
    {
        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue([
            [
                'username' => 'UserName1',
                'roles' => 'ROLE1',
            ],
            [
                'username' => 'UserName2',
                'roles' => 'ROLE2',
            ],
        ]));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())->method('prepare')->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        $users = $repository->findAll();

        self::assertCount(2, $users);
        self::assertInstanceOf(User::class, $users[0]);
        self::assertInstanceOf(User::class, $users[1]);
    }

    public function testFindByUsernameEmpty()
    {
        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue([]));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())->method('prepare')->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        self::assertNull($repository->findByUsername('UserName'));
    }

    public function testFindByUsername()
    {
        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue([[
            'username' => 'UserName',
            'roles' => 'ROLE1',
        ]]));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())->method('prepare')->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        self::assertInstanceOf(User::class, $repository->findByUsername('UserName'));
    }

    public function testFindByCredentialsWrong()
    {
        $expectedQuery = 'SELECT * FROM user WHERE username = :username';

        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::exactly(1))->method('bindValue');
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue([[
            'username' => 'UserName',
            'password' => '',
            'roles' => 'ROLE1',
        ]]));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())
            ->method('prepare')->with(self::equalTo($expectedQuery))->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        self::assertNull($repository->findByCredentials('UserName', 'UserPassword'));
    }

    public function testFindByCredentials()
    {
        $expectedQuery = 'SELECT * FROM user WHERE username = :username';

        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::exactly(1))->method('bindValue');
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue([[
            'username' => 'UserName',
            'password' => '$2y$10$SHWpL.oD12OtmNsvc.q5kulDMX7zL51cqfGkn1fidXd9qiRxTW2ES',
            'roles' => 'ROLE1',
        ]]));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())
            ->method('prepare')->with(self::equalTo($expectedQuery))->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        self::assertInstanceOf(User::class, $repository->findByCredentials('UserName', 'UserPassword'));
    }

    public function testSave()
    {
        $expectedQuery = 'INSERT INTO user (username, password, roles) VALUES (:username, :password, :roles)';

        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::exactly(3))->method('bindValue');
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue(true));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())
            ->method('prepare')->with(self::equalTo($expectedQuery))->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        $user = new User;
        $user->setUsername('UserName');
        $user->setPassword('UserPassword');
        $user->addRole('ROLE1');

        self::assertTrue($repository->save($user));
    }

    public function testUpdateWithPassword()
    {
        $expectedQuery = 'UPDATE user SET password = :password, roles = :roles WHERE username = :username';

        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::exactly(3))->method('bindValue');
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue(true));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())
            ->method('prepare')->with(self::equalTo($expectedQuery))->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        $user = new User;
        $user->setUsername('UserName');
        $user->setPassword('UserPassword');
        $user->addRole('ROLE1');

        self::assertTrue($repository->update($user));
    }

    public function testUpdateWithoutPassword()
    {
        $expectedQuery = 'UPDATE user SET roles = :roles WHERE username = :username';

        $statement = $this->getMockBuilder(\PDOStatement::class)->disableOriginalConstructor()->getMock();
        $statement->expects(self::exactly(2))->method('bindValue');
        $statement->expects(self::any())->method('execute')->will(self::returnValue(true));
        $statement->expects(self::any())->method('fetchAll')->will(self::returnValue(true));

        $connection  = $this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::any())
            ->method('prepare')->with(self::equalTo($expectedQuery))->will(self::returnValue($statement));

        $repository = new UserRepository($connection);

        $user = new User;
        $user->setUsername('UserName');
        $user->addRole('ROLE1');

        self::assertTrue($repository->update($user));
    }
}
