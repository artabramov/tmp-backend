<?php
use \PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/Core/Echidna.php';
require_once __DIR__.'/../src/Core/Meta.php';

class MetaTest extends TestCase
{
    private $pdo;
    private $meta;

    protected function setUp() : void {
        $this->pdo = require __DIR__ . "/../src/init/pdo.php";
        $this->meta = new \App\Core\Meta( $this->pdo );
        $this->truncate();
    }

    protected function tearDown() : void {
        $this->truncate();
        $this->pdo = null;
        $this->meta = null;
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
        $stmt = $this->pdo->query( "TRUNCATE TABLE post_meta;" );
    }

    protected function insert() {
        $stmt = $this->pdo->query( "INSERT INTO post_meta VALUES (1, '0001-01-01 00:00:00', '0001-01-01 00:00:00', 1, 1, 'key', 'value');" );
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $data, $expected ) {

        $this->truncate();
        $result = $this->meta->set( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->meta->error, $expected[1] );
    }

    public function addSet() {
        return [

            // correct cases
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [true, ''] ],

            // incorrect cases
            [ ['create_date' => null, 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => '', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => ' ', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => 0, 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => '0', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => 1, 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '1', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => -1, 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '-1', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => 'A001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'create_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => null, 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => ' ', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 0, 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 1, 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '1', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => -1, 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '-1', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 'A001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'update_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => null, 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => ' ', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => 0, 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '0', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => -1, 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'user_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '-1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'user_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => 'A', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'user_id is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => null, 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => ' ', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => 0, 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '0', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => -1, 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'post_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '-1', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'post_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => 'A', 'meta_key' => 'key', 'meta_value' => 'value'], [false, 'post_id is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => null, 'meta_value' => 'value'], [false, 'meta_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => '', 'meta_value' => 'value'], [false, 'meta_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => ' ', 'meta_value' => 'value'], [false, 'meta_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 0, 'meta_value' => 'value'], [false, 'meta_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => '0', 'meta_value' => 'value'], [false, 'meta_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 1, 'meta_value' => 'value'], [false, 'meta_key is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => -1, 'meta_value' => 'value'], [false, 'meta_key is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'Lorem ipsum dolor sit', 'meta_value' => 'value'], [false, 'meta_key is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => null], [false, 'meta_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => ''], [false, 'meta_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => ' '], [false, 'meta_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 0], [false, 'meta_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => '0'], [false, 'meta_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 1], [false, 'meta_value is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => -1], [false, 'meta_value is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_id' => '1', 'post_id' => '1', 'meta_key' => 'key', 'meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in'], [false, 'meta_value is incorrect'] ],
        ];
    }

    /**
     * @dataProvider addGet
     */
    public function testGet( $args, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->meta->get( $args );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->meta->error, $expected[1] );
    }

    public function addGet() {
        return [

            // correct cases
            [ [['id', '=', 1]], [ true, '' ] ],
            [ [['id', '=', '1']], [ true, '' ] ],
            [ [['create_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['update_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['user_id', '=', 1]], [ true, '' ] ],
            [ [['user_id', '=', '1']], [ true, '' ] ],
            [ [['meta_key', '=', 'key']], [ true, '' ] ],
            [ [['meta_value', '=', 'value']], [ true, '' ] ],

            // incorrect cases
            [ [['id', '=', null]], [ false, 'meta_id is empty' ] ],
            [ [['id', '=', '']], [ false, 'meta_id is empty' ] ],
            [ [['id', '=', ' ' ]], [ false, 'meta_id is empty' ] ],
            [ [['id', '=', 0]], [ false, 'meta_id is empty' ] ],
            [ [['id', '=', '0']], [ false, 'meta_id is empty' ] ],
            [ [['id', '=', -1]], [ false, 'meta_id is incorrect' ] ],
            [ [['id', '=', '-1']], [ false, 'meta_id is incorrect' ] ],
            [ [['id', '=', 'A']], [ false, 'meta_id is incorrect' ] ],

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

            [ [['post_id', '=', null]], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', '']], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', ' ']], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', '0']], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', 0]], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', -1]], [ false, 'post_id is incorrect' ] ],
            [ [['post_id', '=', '-1']], [ false, 'post_id is incorrect' ] ],
            [ [['post_id', '=', 'A']], [ false, 'post_id is incorrect' ] ],

            [ [['meta_key', '=', null]], [ false, 'meta_key is empty' ] ],
            [ [['meta_key', '=', '']], [ false, 'meta_key is empty' ] ],
            [ [['meta_key', '=', ' ']], [ false, 'meta_key is empty' ] ],
            [ [['meta_key', '=', 0]], [ false, 'meta_key is empty' ] ],
            [ [['meta_key', '=', '0']], [ false, 'meta_key is empty' ] ],
            [ [['meta_key', '=', 1]], [ false, 'meta_key is incorrect' ] ],
            [ [['meta_key', '=', -1]], [ false, 'meta_key is incorrect' ] ],
            [ [['meta_key', '=', 'Lorem ipsum dolor sit']], [ false, 'meta_key is incorrect' ] ],

            [ [['meta_value', '=', null]], [ false, 'meta_value is empty' ] ],
            [ [['meta_value', '=', '']], [ false, 'meta_value is empty' ] ],
            [ [['meta_value', '=', ' ']], [ false, 'meta_value is empty' ] ],
            [ [['meta_value', '=', 0]], [ false, 'meta_value is empty' ] ],
            [ [['meta_value', '=', '0']], [ false, 'meta_value is empty' ] ],
            [ [['meta_value', '=', 1]], [ false, 'meta_value is incorrect' ] ],
            [ [['meta_value', '=', -1]], [ false, 'meta_value is incorrect' ] ],
            [ [['meta_value', '=', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in']], [ false, 'meta_value is incorrect' ] ],

            [ [['id', '=', 2]], [ false, 'meta not found' ] ],
        ];
    }

    /**
     * @dataProvider addPut
     */
    public function testPut( $data, $expected ) {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->meta, 'id', 1 );
        $result = $this->meta->put( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->meta->error, $expected[1] );
    }

    public function addPut() {
        return [

            // correct cases
            [ ['meta_value' => 'value 2'], [true, ''] ],

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

            [ ['meta_key' => null], [false, 'meta_key is empty'] ],
            [ ['meta_key' => ''], [false, 'meta_key is empty'] ],
            [ ['meta_key' => ' '], [false, 'meta_key is empty'] ],
            [ ['meta_key' => 0], [false, 'meta_key is empty'] ],
            [ ['meta_key' => '0'], [false, 'meta_key is empty'] ],
            [ ['meta_key' => 1], [false, 'meta_key is incorrect'] ],
            [ ['meta_key' => -1], [false, 'meta_key is incorrect'] ],
            [ ['meta_key' => 'Lorem ipsum dolor sit'], [false, 'meta_key is incorrect'] ],

            [ ['meta_value' => null], [false, 'meta_value is empty'] ],
            [ ['meta_value' => ''], [false, 'meta_value is empty'] ],
            [ ['meta_value' => ' '], [false, 'meta_value is empty'] ],
            [ ['meta_value' => 0], [false, 'meta_value is empty'] ],
            [ ['meta_value' => '0'], [false, 'meta_value is empty'] ],
            [ ['meta_value' => 1], [false, 'meta_value is incorrect'] ],
            [ ['meta_value' => -1], [false, 'meta_value is incorrect'] ],
            [ ['meta_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in'], [false, 'meta_value is incorrect'] ],
        ];
    }

    // Delete
    public function testDel() {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->meta, 'id', 1 );
        $result = $this->meta->del();
        $this->assertEquals( $result, true );
    }


}