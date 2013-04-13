<?php
namespace spec\rtens\lacarte;

use rtens\lacarte\core\Configuration;
use rtens\lacarte\core\Database;
use rtens\mockster\MockFactory;
use watoki\factory\Factory;
use watoki\stepper\Migrater;

/**
 * @property Test_Given given
 * @property Test_When when
 * @property Test_Then then
 */
abstract class Test extends \PHPUnit_Framework_TestCase {

    /**
     * @var Factory
     */
    public $factory;

    /**
     * @var MockFactory
     */
    public $mf;

    private $stateFile;

    protected function setUp() {
        parent::setUp();

        $this->stateFile = __DIR__ . '/migration';

        $this->mf = new MockFactory();

        $config = $this->mf->createMock(Configuration::Configuration);
        $config->__mock()->method('getPdoDataSourceName')->willReturn('sqlite::memory:');

        $this->factory = new Factory();
        $this->factory->setSingleton(Configuration::Configuration, $config);

        if (file_exists($this->stateFile)) unlink($this->stateFile);
        $this->migrate();

        $this->createSteps();
    }

    protected function tearDown() {
        unlink($this->stateFile);
        parent::tearDown();
    }

    private function migrate() {
        $migrater = new Migrater($this->factory, 'rtens\lacarte\model\migration', $this->stateFile);
        $migrater->migrate();
    }

    private function createSteps() {
        foreach (array('given', 'when', 'then') as $steps) {
            $class = get_class($this) . '_' . ucfirst($steps);
            if (class_exists($class)) {
                $this->$steps = new $class($this);
            } else {
                $class = 'spec\rtens\lacarte_' . ucfirst($steps);
                $this->$steps = new $class($this);
            }
        }
    }

}

/**
 * @property Test test
 */
class Test_Given {

    function __construct(Test $test) {
        $this->test = $test;
    }

}

/**
 * @property Test test
 */
class Test_When {

    function __construct(Test $test) {
        $this->test = $test;
    }

}

/**
 * @property Test test
 */
class Test_Then {

    function __construct(Test $test) {
        $this->test = $test;
    }

    protected function getFieldIn($string, $field) {
        foreach (explode('/', $string) as $key) {
            if (!array_key_exists($key, $field)) {
                throw new \Exception("Could not find '$key' in " . json_encode($field));
            }
            $field = $field[$key];
        }
        return $field;
    }

}