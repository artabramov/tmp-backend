<?php
use \PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/Core/Echidna.php';
require_once __DIR__.'/../src/Core/Tag.php';

class TagTest extends TestCase
{
    private $pdo;
    private $tag;

    protected function setUp() : void {
        $this->pdo = require __DIR__ . "/../src/init/pdo.php";
        $this->tag = new \App\Core\Tag( $this->pdo );
        $this->truncate();
    }

    protected function tearDown() : void {
        $this->truncate();
        $this->pdo = null;
        $this->tag = null;
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
        $stmt = $this->pdo->query( "TRUNCATE TABLE tags;" );
    }

    protected function insert() {
        $stmt = $this->pdo->query( "INSERT INTO tags VALUES (1, '0001-01-01 00:00:00', '0001-01-01 00:00:00', 1, 'key', 'value');" );
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $data, $expected ) {

        $this->truncate();
        $result = $this->tag->set( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->tag->error, $expected[1] );
    }

    public function addSet() {
        return [

            // correct cases
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [true, ''] ],

            // incorrect cases
            [ ['create_date' => null, 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => '', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => ' ', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => 0, 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => '0', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is empty'] ],
            [ ['create_date' => 1, 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '1', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => -1, 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '-1', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => 'A001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'create_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => null, 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => ' ', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 0, 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 1, 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '1', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => -1, 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '-1', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 'A001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'update_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => null, 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => ' ', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => 0, 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '0', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'post_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => -1, 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'post_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '-1', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'post_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => 'A', 'tag_key' => 'key', 'tag_value' => 'value'], [false, 'post_id is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => null, 'tag_value' => 'value'], [false, 'tag_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => '', 'tag_value' => 'value'], [false, 'tag_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => ' ', 'tag_value' => 'value'], [false, 'tag_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 0, 'tag_value' => 'value'], [false, 'tag_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => '0', 'tag_value' => 'value'], [false, 'tag_key is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 1, 'tag_value' => 'value'], [false, 'tag_key is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => -1, 'tag_value' => 'value'], [false, 'tag_key is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'Lorem ipsum dolor sit', 'tag_value' => 'value'], [false, 'tag_key is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => null], [false, 'tag_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => ''], [false, 'tag_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => ' '], [false, 'tag_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 0], [false, 'tag_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => '0'], [false, 'tag_value is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 1], [false, 'tag_value is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => -1], [false, 'tag_value is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'post_id' => '1', 'tag_key' => 'key', 'tag_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in'], [false, 'tag_value is incorrect'] ],
        ];
    }

    /**
     * @dataProvider addGet
     */
    public function testGet( $args, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->tag->get( $args );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->tag->error, $expected[1] );
    }

    public function addGet() {
        return [

            // correct cases
            [ [['id', '=', 1]], [ true, '' ] ],
            [ [['id', '=', '1']], [ true, '' ] ],
            [ [['create_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['update_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['post_id', '=', 1]], [ true, '' ] ],
            [ [['post_id', '=', '1']], [ true, '' ] ],
            [ [['tag_key', '=', 'key']], [ true, '' ] ],
            [ [['tag_value', '=', 'value']], [ true, '' ] ],

            // incorrect cases
            [ [['id', '=', null]], [ false, 'tag_id is empty' ] ],
            [ [['id', '=', '']], [ false, 'tag_id is empty' ] ],
            [ [['id', '=', ' ' ]], [ false, 'tag_id is empty' ] ],
            [ [['id', '=', 0]], [ false, 'tag_id is empty' ] ],
            [ [['id', '=', '0']], [ false, 'tag_id is empty' ] ],
            [ [['id', '=', -1]], [ false, 'tag_id is incorrect' ] ],
            [ [['id', '=', '-1']], [ false, 'tag_id is incorrect' ] ],
            [ [['id', '=', 'A']], [ false, 'tag_id is incorrect' ] ],

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

            [ [['post_id', '=', null]], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', '']], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', ' ']], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', '0']], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', 0]], [ false, 'post_id is empty' ] ],
            [ [['post_id', '=', -1]], [ false, 'post_id is incorrect' ] ],
            [ [['post_id', '=', '-1']], [ false, 'post_id is incorrect' ] ],
            [ [['post_id', '=', 'A']], [ false, 'post_id is incorrect' ] ],

            [ [['tag_key', '=', null]], [ false, 'tag_key is empty' ] ],
            [ [['tag_key', '=', '']], [ false, 'tag_key is empty' ] ],
            [ [['tag_key', '=', ' ']], [ false, 'tag_key is empty' ] ],
            [ [['tag_key', '=', 0]], [ false, 'tag_key is empty' ] ],
            [ [['tag_key', '=', '0']], [ false, 'tag_key is empty' ] ],
            [ [['tag_key', '=', 1]], [ false, 'tag_key is incorrect' ] ],
            [ [['tag_key', '=', -1]], [ false, 'tag_key is incorrect' ] ],
            [ [['tag_key', '=', 'Lorem ipsum dolor sit']], [ false, 'tag_key is incorrect' ] ],

            [ [['tag_value', '=', null]], [ false, 'tag_value is empty' ] ],
            [ [['tag_value', '=', '']], [ false, 'tag_value is empty' ] ],
            [ [['tag_value', '=', ' ']], [ false, 'tag_value is empty' ] ],
            [ [['tag_value', '=', 0]], [ false, 'tag_value is empty' ] ],
            [ [['tag_value', '=', '0']], [ false, 'tag_value is empty' ] ],
            [ [['tag_value', '=', 1]], [ false, 'tag_value is incorrect' ] ],
            [ [['tag_value', '=', -1]], [ false, 'tag_value is incorrect' ] ],
            [ [['tag_value', '=', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in']], [ false, 'tag_value is incorrect' ] ],

            [ [['id', '=', 2]], [ false, 'tag not found' ] ],
        ];
    }

    /**
     * @dataProvider addPut
     */
    public function testPut( $data, $expected ) {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->tag, 'id', 1 );
        $result = $this->tag->put( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->tag->error, $expected[1] );
    }

    public function addPut() {
        return [

            // correct cases
            [ ['tag_value' => 'value 2'], [true, ''] ],

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

            [ ['tag_key' => null], [false, 'tag_key is empty'] ],
            [ ['tag_key' => ''], [false, 'tag_key is empty'] ],
            [ ['tag_key' => ' '], [false, 'tag_key is empty'] ],
            [ ['tag_key' => 0], [false, 'tag_key is empty'] ],
            [ ['tag_key' => '0'], [false, 'tag_key is empty'] ],
            [ ['tag_key' => 1], [false, 'tag_key is incorrect'] ],
            [ ['tag_key' => -1], [false, 'tag_key is incorrect'] ],
            [ ['tag_key' => 'Lorem ipsum dolor sit'], [false, 'tag_key is incorrect'] ],

            [ ['tag_value' => null], [false, 'tag_value is empty'] ],
            [ ['tag_value' => ''], [false, 'tag_value is empty'] ],
            [ ['tag_value' => ' '], [false, 'tag_value is empty'] ],
            [ ['tag_value' => 0], [false, 'tag_value is empty'] ],
            [ ['tag_value' => '0'], [false, 'tag_value is empty'] ],
            [ ['tag_value' => 1], [false, 'tag_value is incorrect'] ],
            [ ['tag_value' => -1], [false, 'tag_value is incorrect'] ],
            [ ['tag_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in'], [false, 'tag_value is incorrect'] ],
        ];
    }

    // Delete
    public function testDel() {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->tag, 'id', 1 );
        $result = $this->tag->del();
        $this->assertEquals( $result, true );
    }


}