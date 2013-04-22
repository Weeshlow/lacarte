<?php
namespace rtens\lacarte;
 
use rtens\lacarte\core\NotFoundException;
use rtens\lacarte\model\Group;
use rtens\lacarte\model\User;
use rtens\lacarte\model\stores\GroupStore;
use rtens\lacarte\model\stores\UserStore;
use rtens\lacarte\utils\KeyGenerator;

class UserInteractor {

    public static $CLASS = __CLASS__;

    private $groupStore;

    private $userStore;

    private $keyGenerator;

    function __construct(GroupStore $groupStore, UserStore $userStore, KeyGenerator $keyGenerator) {
        $this->groupStore = $groupStore;
        $this->userStore = $userStore;
        $this->keyGenerator = $keyGenerator;
    }

    public function authorizeAdmin($email, $password) {
        try {
            return $this->groupStore->readByEmailAndPassword($email, $password);
        } catch (NotFoundException $e) {
            return null;
        }
    }

    public function createUser($groupId, $name, $email) {
        if (!$name || !$email) {
            throw new \InvalidArgumentException('Please provide name and email.');
        }

        do {
            $key = $this->keyGenerator->generateUnique();
        } while ($this->userStore->isKeyExisting($key));

        $user = new User($groupId, $name, strtolower($email), $key);

        try {
            $this->userStore->create($user);
            return $user;
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException('Error while creating user. The email probably already exists: '
                . $e->getMessage());
        }
    }

    /**
     * @return array|User[]
     */
    public function readAll() {
        return $this->userStore->readAll();
    }

}
