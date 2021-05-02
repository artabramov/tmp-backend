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
     * @dataProvider addGet
     */
    public function testGet( $args, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->hub->get( $args );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->hub->error, $expected[1] );
    }

    public function addGet() {
        return [

            // correct cases
            [ [['id', '=', 1]], [ true, '' ] ],
            [ [['id', '=', '1']], [ true, '' ] ],
            [ [['create_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['update_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['user_id', '=', 1]], [ true, '' ] ],
            [ [['hub_status', '=', 'custom']], [ true, '' ] ],
            [ [['hub_name', '=', 'hub name']], [ true, '' ] ],

            // incorrect cases
            [ [['id', '=', null]], [ false, 'hub_id is empty' ] ],
            [ [['id', '=', '']], [ false, 'hub_id is empty' ] ],
            [ [['id', '=', ' ']], [ false, 'hub_id is empty' ] ],
            [ [['id', '=', 0]], [ false, 'hub_id is empty' ] ],
            [ [['id', '=', '0']], [ false, 'hub_id is empty' ] ],
            [ [['id', '=', -1]], [ false, 'hub_id is incorrect' ] ],
            [ [['id', '=', '-1']], [ false, 'hub_id is incorrect' ] ],
            [ [['id', '=', 'A']], [ false, 'hub_id is incorrect' ] ],

            [ [['create_date', '=', null]], [ false, 'create_date is empty' ] ],
            [ [['create_date', '=', 0]], [ false, 'create_date is empty' ] ],
            [ [['create_date', '=', '']], [ false, 'create_date is empty' ] ],
            [ [['create_date', '=', ' ']], [ false, 'create_date is empty' ] ],
            [ [['create_date', '=', 1]], [ false, 'create_date is incorrect' ] ],
            [ [['create_date', '=', '1']], [ false, 'create_date is incorrect' ] ],
            [ [['create_date', '=', -1]], [ false, 'create_date is incorrect' ] ],
            [ [['create_date', '=', '-1']], [ false, 'create_date is incorrect' ] ],
            [ [['create_date', '=', 'A001-01-01 00:00:00']], [ false, 'create_date is incorrect' ] ],

            [ [['update_date', '=', null]], [ false, 'update_date is empty' ] ],
            [ [['update_date', '=', '']], [ false, 'update_date is empty' ] ],
            [ [['update_date', '=', ' ']], [ false, 'update_date is empty' ] ],
            [ [['update_date', '=', 0]], [ false, 'update_date is empty' ] ],
            [ [['update_date', '=', '0']], [ false, 'update_date is empty' ] ],
            [ [['update_date', '=', 1]], [ false, 'update_date is incorrect' ] ],
            [ [['update_date', '=', '1']], [ false, 'update_date is incorrect' ] ],
            [ [['update_date', '=', -1]], [ false, 'update_date is incorrect' ] ],
            [ [['update_date', '=', '-1']], [ false, 'update_date is incorrect' ] ],
            [ [['update_date', '=', 'A001-01-01 00:00:00']], [ false, 'update_date is incorrect' ] ],

            [ [['user_id', '=', null]], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', '']], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', ' ']], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', '0']], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', 0]], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', -1]], [ false, 'user_id is incorrect' ] ],
            [ [['user_id', '=', '-1']], [ false, 'user_id is incorrect' ] ],
            [ [['user_id', '=', 'A']], [ false, 'user_id is incorrect' ] ],

            [ [['hub_status', '=', null]], [ false, 'hub_status is empty' ] ],
            [ [['hub_status', '=', '']], [ false, 'hub_status is empty' ] ],
            [ [['hub_status', '=', ' ']], [ false, 'hub_status is empty' ] ],
            [ [['hub_status', '=', 0]], [ false, 'hub_status is empty' ] ],
            [ [['hub_status', '=', '0']], [ false, 'hub_status is empty' ] ],
            [ [['hub_status', '=', -1]], [ false, 'hub_status is incorrect' ] ],
            [ [['hub_status', '=', 'Lorem ipsum dolor sit']], [ false, 'hub_status is incorrect' ] ],

            [ [['hub_name', '=', null]], [ false, 'hub_name is empty' ] ],
            [ [['hub_name', '=', '']], [ false, 'hub_name is empty' ] ],
            [ [['hub_name', '=', ' ']], [ false, 'hub_name is empty' ] ],
            [ [['hub_name', '=', 0]], [ false, 'hub_name is empty' ] ],
            [ [['hub_name', '=', '0']], [ false, 'hub_name is empty' ] ],
            [ [['hub_name', '=', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in']], [ false, 'hub_name is incorrect' ] ],

            [ [['id', '=', 2]], [ false, 'hub not found' ] ],
        ];
    }



    /**
     * @dataProvider addSet
     */
    public function testSet( $data, $expected ) {

        $this->truncate();
        $result = $this->hub->set( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->hub->error, $expected[1] );
    }

    public function addSet() {
        return [

            // correct cases
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [true, ''] ],

            // incorrect cases
            [ ['create_date' => null, 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'create_date is empty'] ],
            [ ['create_date' => '', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'create_date is empty'] ],
            [ ['create_date' => ' ', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'create_date is empty'] ],
            [ ['create_date' => '0', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'create_date is empty'] ],
            [ ['create_date' => 0, 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'create_date is empty'] ],
            [ ['create_date' => 'A001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'create_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => null, 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => ' ', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 0, 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 'A001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'update_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => null, 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => ' ', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '0', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => 0, 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '-1', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'user_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => -1, 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'user_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => 'A', 'hub_status' => 'private', 'hub_name' => 'hub name'], [false, 'user_id is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => null, 'hub_name' => 'hub name'], [false, 'hub_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => '', 'hub_name' => 'hub name'], [false, 'hub_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => ' ', 'hub_name' => 'hub name'], [false, 'hub_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => '0', 'hub_name' => 'hub name'], [false, 'hub_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 0, 'hub_name' => 'hub name'], [false, 'hub_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 1, 'hub_name' => 'hub name'], [false, 'hub_status is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => -1, 'hub_name' => 'hub name'], [false, 'hub_status is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'Lorem ipsum dolor sit', 'hub_name' => 'hub name'], [false, 'hub_status is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => null], [false, 'hub_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => ''], [false, 'hub_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => ' '], [false, 'hub_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 0], [false, 'hub_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => '0'], [false, 'hub_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 1], [false, 'hub_name is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => -1], [false, 'hub_name is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'hub_status' => 'private', 'hub_name' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in'], [false, 'hub_name is incorrect'] ],

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
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->hub->error, $expected[1] );

    }

    public function addPut() {
        return [

            // correct cases
            [ ['create_date' => '2021-01-01 00:00:00'], [true, ''] ],
            [ ['update_date' => '2021-01-01 00:00:00'], [true, ''] ],
            [ ['user_id' => 2], [true, ''] ],

            // incorrect cases
            [ ['create_date' => null], [false, 'create_date is empty'] ],
            [ ['create_date' => ''], [false, 'create_date is empty'] ],
            [ ['create_date' => ' '], [false, 'create_date is empty'] ],
            [ ['create_date' => 0], [false, 'create_date is empty'] ],
            [ ['create_date' => '0'], [false, 'create_date is empty'] ],
            [ ['create_date' => 1], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '1'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => 'A000-00-00 00:00:00'], [false, 'create_date is incorrect'] ],

            [ ['update_date' => null], [false, 'update_date is empty'] ],
            [ ['update_date' => ''], [false, 'update_date is empty'] ],
            [ ['update_date' => ' '], [false, 'update_date is empty'] ],
            [ ['update_date' => 0], [false, 'update_date is empty'] ],
            [ ['update_date' => '0'], [false, 'update_date is empty'] ],
            [ ['update_date' => 1], [false, 'update_date is incorrect'] ],
            [ ['update_date' => '1'], [false, 'update_date is incorrect'] ],
            [ ['update_date' => 'A000-00-00 00:00:00'], [false, 'update_date is incorrect'] ],

            [ ['user_id' => null], [false, 'user_id is empty'] ],
            [ ['user_id' => ''], [false, 'user_id is empty'] ],
            [ ['user_id' => ' '], [false, 'user_id is empty'] ],
            [ ['user_id' => 0], [false, 'user_id is empty'] ],
            [ ['user_id' => '0'], [false, 'user_id is empty'] ],
            [ ['user_id' => -1], [false, 'user_id is incorrect'] ],
            [ ['user_id' => '-1'], [false, 'user_id is incorrect'] ],
            [ ['user_id' => 'A'], [false, 'user_id is incorrect'] ],

            [ ['hub_status' => null], [false, 'hub_status is empty'] ],
            [ ['hub_status' => ''], [false, 'hub_status is empty'] ],
            [ ['hub_status' => ' '], [false, 'hub_status is empty'] ],
            [ ['hub_status' => 0], [false, 'hub_status is empty'] ],
            [ ['hub_status' => '0'], [false, 'hub_status is empty'] ],
            [ ['hub_status' => 1], [false, 'hub_status is incorrect'] ],
            [ ['hub_status' => -1], [false, 'hub_status is incorrect'] ],
            [ ['hub_status' => 'Lorem ipsum dolor sit'], [false, 'hub_status is incorrect'] ],

            [ ['hub_name' => null], [false, 'hub_name is empty'] ],
            [ ['hub_name' => ''], [false, 'hub_name is empty'] ],
            [ ['hub_name' => ' '], [false, 'hub_name is empty'] ],
            [ ['hub_name' => 0], [false, 'hub_name is empty'] ],
            [ ['hub_name' => '0'], [false, 'hub_name is empty'] ],
            [ ['hub_name' => 1], [false, 'hub_name is incorrect'] ],
            [ ['hub_name' => -1], [false, 'hub_name is incorrect'] ],
            [ ['hub_name' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in'], [false, 'hub_name is incorrect'] ],

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