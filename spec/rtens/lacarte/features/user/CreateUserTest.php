<?php
namespace spec\rtens\lacarte\features\user;

use rtens\lacarte\UserInteractor;
use rtens\lacarte\model\Group;
use rtens\lacarte\model\User;
use rtens\lacarte\model\stores\GroupStore;
use rtens\lacarte\model\stores\UserStore;
use rtens\lacarte\utils\KeyGenerator;
use spec\rtens\lacarte\Test;
use spec\rtens\lacarte\Test_Given;
use spec\rtens\lacarte\Test_Then;
use spec\rtens\lacarte\Test_When;

/**
 * @property CreateUserTest_Given given
 * @property CreateUserTest_When when
 * @property CreateUserTest_Then then
 */
class CreateUserTest extends Test {

    protected function setUp() {
        parent::setUp();
        $this->given->theGroup('test');
        $this->given->theNextGeneratedKeyIs('myKey');
    }

    function testSuccess() {
        $this->given->theName('Marina');
        $this->given->theEmail('m@gnz.es');

        $this->when->iCreateANewUserForTheGroup();

        $this->then->theUserShouldBeCreated();
        $this->then->thereShouldBeAUser('Marina', 'm@gnz.es');
        $this->then->theUserShouldHaveAKey();
    }

    function testAlreadyExistingKey() {
        $this->given->theExistingUser('Peter', 'peter@parker.com', 'myKey');
        $this->given->theName('John');
        $this->given->theEmail('john@wayne.com');

        $this->given->theNextGeneratedKeyIs('yourKey');
        $this->given->theNextGeneratedKeyIs('myKey');

        $this->when->iCreateANewUserForTheGroup();

        $this->then->theUserShouldBeCreated();
        $this->then->thereShouldBeAUser_WithKey('John', 'john@wayne.com', 'yourKey');
    }

}

/**
 * @property CreateUserTest test
 */
class CreateUserTest_Given extends Test_Given {

    public $name;

    public $email;

    /** @var Group */
    public $group;

    /** @var GroupStore */
    public $groupStore;

    /** @var UserStore */
    public $userStore;

    function __construct(Test $test) {
        parent::__construct($test);
        $this->groupStore = $this->test->factory->getInstance(GroupStore::$CLASS);
        $this->userStore = $this->test->factory->getInstance(UserStore::$CLASS);

        $this->keyGenerator = $this->test->mf->createMock(KeyGenerator::$CLASS);
        $this->test->factory->setSingleton(KeyGenerator::$CLASS, $this->keyGenerator);
    }

    public function theName($name) {
        $this->name = $name;
    }

    public function theEmail($email) {
        $this->email = $email;
    }

    public function theGroup($name) {
        $this->group = new Group($name, '', '');
        $this->groupStore->create($this->group);
    }

    public function theExistingUser($name, $email, $key) {
        $this->userStore->create(new User($this->group->id, $name, $email, $key));
    }

    public function theNextGeneratedKeyIs($key) {
        $this->keyGenerator->__mock()->method('generateUnique')->willReturn($key)->once();
    }
}

/**
 * @property CreateUserTest test
 */
class CreateUserTest_When extends Test_When {

    /**
     * @var User
     */
    public $user;

    /**
     * @var null|\Exception
     */
    public $caught;

    public function iCreateANewUserForTheGroup() {
        /** @var UserInteractor $interactor */
        $interactor = $this->test->factory->getInstance(UserInteractor::$CLASS);
        $this->user = $interactor->createUser($this->test->given->group,
            $this->test->given->name, $this->test->given->email);
    }
}

/**
 * @property CreateUserTest test
 */
class CreateUserTest_Then extends Test_Then {

    public function thereShouldBeAUser($name, $email) {
        /** @var UserStore $store */
        $store = $this->test->factory->getInstance(UserStore::$CLASS);
        $user = $store->readByEmail($email);
        $this->test->assertEquals($name, $user->getName());
        return $user;
    }

    public function thereShouldBeAUser_WithKey($name, $email, $key) {
        $user = $this->thereShouldBeAUser($name, $email);
        $this->test->assertEquals($key, $user->getKey());
    }

    public function theUserShouldBeCreated() {
        $this->test->assertNotNull($this->test->when->user);
    }

    public function theUserShouldHaveAKey() {
        $this->test->assertNotNull($this->test->when->user->getKey());
    }
}