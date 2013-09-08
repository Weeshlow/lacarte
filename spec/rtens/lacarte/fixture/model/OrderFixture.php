<?php
namespace spec\rtens\lacarte\fixture\model;

use rtens\lacarte\model\Dish;
use rtens\lacarte\model\Menu;
use rtens\lacarte\model\Order;
use rtens\lacarte\model\stores\DishStore;
use rtens\lacarte\model\stores\MenuStore;
use rtens\lacarte\model\stores\OrderStore;
use rtens\lacarte\OrderInteractor;
use rtens\mockster\MockFactory;
use spec\rtens\lacarte\fixture\Fixture;
use spec\rtens\lacarte\TestCase;
use watoki\factory\Factory;

class OrderFixture extends Fixture {

    public static $CLASS = __CLASS__;

    /** @var null|Order */
    private $currentOrder;

    /** @var array|Dish[] */
    private $dishes = array();

    /** @var array|Order[] */
    private $orders = array();

    public function __construct(TestCase $test, Factory $factory, OrderStore $store, UserFixture $user,
                                MenuStore $menuStore, DishStore $dishStore) {
        parent::__construct($test, $factory);
        $this->orderStore = $store;
        $this->user = $user;
        $this->menuStore = $menuStore;
        $this->dishStore = $dishStore;
    }

    public function givenTheOrder_WithDeadline($name, $deadline) {
        $order = new Order($this->user->getGroup()->id, $name, new \DateTime($deadline));
        $this->orderStore->create($order);
        $this->orders[$name] = $order;
        $this->currentOrder = $order;
    }

    public function givenAnOrder_With_MenusEach_Dishes($name, $numMenus, $numDishes) {
        $this->givenAnOrder_With_MenusEach_DishesStartingOn($name, $numMenus, $numDishes, 'today');
    }

    public function givenAnOrder_With_MenusEach_DishesStartingOn($name, $numMenus, $numDishes, $firstDay) {
        $day = new \DateTime($firstDay);

        $this->givenTheOrder_WithDeadline($name, 'now');
        for ($i = 0; $i < $numMenus; $i++ ) {
            $menu = new Menu($this->orders[$name]->id, clone $day);
            $day->add(new \DateInterval('P1D'));
            $this->menuStore->create($menu);

            for ($j = 0; $j < $numDishes; $j++) {
                $dish = new Dish($menu->id, "Dish $i $j");
                $this->dishStore->create($dish);
            }
        }
    }

    public function thenThereShouldBe_Orders($int) {
        $this->test->assertCount($int, $this->orderStore->readAll());
    }

    public function thenThereShouldBeAnOrderWithTheName($string) {
        foreach ($this->orderStore->readAll() as $order) {
            if ($order->getName() == $string) {
                $this->currentOrder = $order;
                return;
            }
        }
        $this->test->fail("Order with name $string not found");
    }

    public function thenThisOrderShouldHaveTheDeadline($string) {
        $this->test->assertEquals(new \DateTime($string), $this->currentOrder->getDeadline());
    }

    public function thenThisOrderShouldHave_Menus($int) {
        $this->test->assertCount($int, $this->menuStore->readAllByOrderId($this->currentOrder->id));
    }

    public function thenTheDateOfMenu_OfThisOrderShouldBe($int, $string) {
        $this->test->assertEquals(new \DateTime($string), $this->getMenuOfCurrentOrder($int)->getDate());
    }

    /**
     * @param $index
     * @return Menu
     */
    private function getMenuOfCurrentOrder($index) {
        $menus = $this->menuStore->readAllByOrderId($this->currentOrder->id);
        return $menus[$index - 1];
    }

    public function thenMenu_OfThisOrderShouldHave_Dishes($menuIndex, $numDishes) {
        $menu = $this->getMenuOfCurrentOrder($menuIndex);
        $this->test->assertCount($numDishes, $this->dishStore->readAllByMenuId($menu->id));
    }

    public function getOrder($orderName) {
        return $this->orders[$orderName];
    }

    public function givenDish_OfMenu_OfThisOrderIs($dishNum, $menuNum, $dishText) {
        $menu = $this->getMenuOfCurrentOrder($menuNum);
        $dishes = $this->dishStore->readAllByMenuId($menu->id);
        $dish = $dishes[$dishNum - 1];
        $dish->setText($dishText);

        $this->dishes[$dishText] = $dish;

        $this->dishStore->update($dish);
    }

    public function getDish($text) {
        return $this->dishes[$text];
    }

    public function thenThereShouldBe_Dishes($int) {
        $this->test->assertCount($int, $this->dishStore->readAll());
    }

    public function thenThereShouldBeADish($string) {
        foreach ($this->dishStore->readAll() as $dish) {
            if ($dish->getText() == $string) {
                return;
            }
        }
        $this->test->fail("Dish $string not found");
    }

    public function thereShouldBe_Menus($int) {
        $this->test->assertCount($int, $this->menuStore->readAll());
    }
}

