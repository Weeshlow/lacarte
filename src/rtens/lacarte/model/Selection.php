<?php
namespace rtens\lacarte\model;

class Selection {

    public $id;

    /** @var int */
    private $userId;

    /** @var int */
    private $menuId;

    /** @var int */
    private $dishId;

    /** @var boolean */
    private $yielded = false;

    function __construct($userId, $menuId, $dishId = 0) {
        $this->userId = $userId;
        $this->menuId = $menuId;
        $this->dishId = $dishId;
    }

    /**
     * @return int
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getMenuId() {
        return $this->menuId;
    }

    /**
     * @return int|null
     */
    public function getDishId() {
        return $this->dishId;
    }

    /**
     * @param int $dishId
     */
    public function setDishId($dishId) {
        $this->dishId = $dishId;
    }

    public function hasDish() {
        return $this->dishId != 0;
    }

    /**
     * @return boolean
     */
    public function isYielded() {
        return $this->yielded;
    }

    /**
     * @param boolean $yielded
     */
    public function setYielded($yielded = true) {
        $this->yielded = $yielded;
    }

}