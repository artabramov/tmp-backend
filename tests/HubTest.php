<?php
use \PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/Core/Echidna.php';
require_once __DIR__.'/../src/Core/Hub.php';

class HubTest extends TestCase
{
    private $pdo;
    private $hub;

    protected function setUp() : void {
        $this->pdo = require __DIR__ . "/../src/init/pdo.php";
        $this->hub = new \App\Core\Hub( $this->pdo );
        $this->truncate();
    }

    protected function tearDown() : void {
        $this->truncate();
        $this->pdo = null;
        $this->hub = null;
    }

    private function callMethod( $object, string $method , array $parameters = [] ) {

        try {
            $className = get_class($object);
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
           throw new \Exception($e->getMessage());
        }

        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function getProperty( $object, $property ) {
        $reflectedClass = new \ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($property);
        $reflection->setAccessible(true);
        return $reflection->getValue($object);
    }

    public function setProperty( $object, $property, $value ) {
        $reflectedClass = new \ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }

    protected function truncate() {
        $stmt = $this->pdo->query( "TRUNCATE TABLE hubs;" );
    }

    protected function insert() {
        $stmt = $this->pdo->query( "INSERT INTO hubs VALUES (1, '0001-01-01 00:00:00', '0001-01-01 00:00:00', 1, 'custom', 'hub name');" );
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $data, $expected ) {

        $this->truncate();
        $result = $this->hub->set( $data );
        $this->assertEquals( $result, $expected );
    }

    public function addSet() {
        return [

            // correct cases
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], true ],

            // incorrect cases


        ];
    }

    /**
     * @dataProvider addGet
     */
    public function testGet( $args, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->hub->get( $args );
        $this->assertEquals( $result, $expected );

    }

    public function addGet() {
        return [

            // correct cases
            [ [['id', '=', 1]], true ],

            // incorrect cases
            [ [['id', '=', 2]], false ],

        ];
    }

    /**
     * @dataProvider addPut
     */
    public function testPut( $data, $expected ) {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->hub, 'id', 1 );
        $result = $this->hub->put( $data );
        $this->assertEquals( $result, $expected );

    }

    public function addPut() {
        return [

            // correct cases
            [ ['hub_name' => 'hub name 2'], true ],

            // incorrect cases

        ];
    }

    // Delete
    public function testDel() {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->hub, 'id', 1 );
        $result = $this->hub->del();
        $this->assertEquals( $result, true );
    }


}