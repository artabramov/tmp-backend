<?php
use \PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/Core/Echidna.php';
require_once __DIR__.'/../src/Core/Role.php';

class RoleTest extends TestCase
{
    private $pdo;
    private $role;

    protected function setUp() : void {
        $this->pdo = require __DIR__ . "/../src/init/pdo.php";
        $this->role = new \App\Core\Role( $this->pdo );
        $this->truncate();
    }

    protected function tearDown() : void {
        $this->truncate();
        $this->pdo = null;
        $this->role = null;
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
        $stmt = $this->pdo->query( "TRUNCATE TABLE roles;" );
    }

    protected function insert() {
        $stmt = $this->pdo->query( "INSERT INTO roles VALUES (1, '0001-01-01 00:00:00', '0001-01-01 00:00:00', 1, 1, 'admin');" );
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $data, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->role->set( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->role->error, $expected[1] );
    }

    public function addSet() {
        return [

            // correct cases
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [true, ''] ],

            // incorrect cases
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '1', 'user_id' => '1', 'user_role' => 'reader'], [false, 'user_role is occupied'] ],

            [ ['create_date' => null, 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is empty'] ],
            [ ['create_date' => '', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is empty'] ],
            [ ['create_date' => ' ', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is empty'] ],
            [ ['create_date' => 0, 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is empty'] ],
            [ ['create_date' => '0', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is empty'] ],
            [ ['create_date' => 1, 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '1', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => -1, 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '-1', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => 'A001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'create_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => null, 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => ' ', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 0, 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 1, 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '1', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => -1, 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '-1', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 'A001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'admin'], [false, 'update_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => null, 'user_id' => '1', 'user_role' => 'admin'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '', 'user_id' => '1', 'user_role' => 'admin'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => ' ', 'user_id' => '1', 'user_role' => 'admin'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => 0, 'user_id' => '1', 'user_role' => 'admin'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '0', 'user_id' => '1', 'user_role' => 'admin'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => -1, 'user_id' => '1', 'user_role' => 'admin'], [false, 'hub_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '-1', 'user_id' => '1', 'user_role' => 'admin'], [false, 'hub_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => 'A', 'user_id' => '1', 'user_role' => 'admin'], [false, 'hub_id is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => null, 'user_role' => 'admin'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '', 'user_role' => 'admin'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => ' ', 'user_role' => 'admin'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => 0, 'user_role' => 'admin'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '0', 'user_role' => 'admin'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => -1, 'user_role' => 'admin'], [false, 'user_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '-1', 'user_role' => 'admin'], [false, 'user_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => 'A', 'user_role' => 'admin'], [false, 'user_id is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => null], [false, 'user_role is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => ''], [false, 'user_role is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => ' '], [false, 'user_role is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 0], [false, 'user_role is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => '0'], [false, 'user_role is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 1], [false, 'user_role is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => -1], [false, 'user_role is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'hub_id' => '2', 'user_id' => '1', 'user_role' => 'Lorem ipsum dolor sit'], [false, 'user_role is incorrect'] ],
        ];
    }

    /**
     * @dataProvider addGet
     */
    public function testGet( $args, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->role->get( $args );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->role->error, $expected[1] );
    }

    public function addGet() {
        return [

            // correct cases
            [ [['id', '=', 1]], [ true, '' ] ],
            [ [['id', '=', '1']], [ true, '' ] ],
            [ [['create_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['update_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['hub_id', '=', 1]], [ true, '' ] ],
            [ [['hub_id', '=', '1']], [ true, '' ] ],
            [ [['user_id', '=', 1]], [ true, '' ] ],
            [ [['user_id', '=', '1']], [ true, '' ] ],
            [ [['user_role', '=', 'admin']], [ true, '' ] ],

            // incorrect cases
            [ [['id', '=', null]], [ false, 'role_id is empty' ] ],
            [ [['id', '=', '']], [ false, 'role_id is empty' ] ],
            [ [['id', '=', ' ' ]], [ false, 'role_id is empty' ] ],
            [ [['id', '=', 0]], [ false, 'role_id is empty' ] ],
            [ [['id', '=', '0']], [ false, 'role_id is empty' ] ],
            [ [['id', '=', -1]], [ false, 'role_id is incorrect' ] ],
            [ [['id', '=', '-1']], [ false, 'role_id is incorrect' ] ],
            [ [['id', '=', 'A']], [ false, 'role_id is incorrect' ] ],

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

            [ [['hub_id', '=', null]], [ false, 'hub_id is empty' ] ],
            [ [['hub_id', '=', '']], [ false, 'hub_id is empty' ] ],
            [ [['hub_id', '=', ' ']], [ false, 'hub_id is empty' ] ],
            [ [['hub_id', '=', '0']], [ false, 'hub_id is empty' ] ],
            [ [['hub_id', '=', 0]], [ false, 'hub_id is empty' ] ],
            [ [['hub_id', '=', -1]], [ false, 'hub_id is incorrect' ] ],
            [ [['hub_id', '=', '-1']], [ false, 'hub_id is incorrect' ] ],
            [ [['hub_id', '=', 'A']], [ false, 'hub_id is incorrect' ] ],

            [ [['user_id', '=', null]], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', '']], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', ' ']], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', '0']], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', 0]], [ false, 'user_id is empty' ] ],
            [ [['user_id', '=', -1]], [ false, 'user_id is incorrect' ] ],
            [ [['user_id', '=', '-1']], [ false, 'user_id is incorrect' ] ],
            [ [['user_id', '=', 'A']], [ false, 'user_id is incorrect' ] ],

            [ [['user_role', '=', null]], [ false, 'user_role is empty' ] ],
            [ [['user_role', '=', '']], [ false, 'user_role is empty' ] ],
            [ [['user_role', '=', ' ']], [ false, 'user_role is empty' ] ],
            [ [['user_role', '=', 0]], [ false, 'user_role is empty' ] ],
            [ [['user_role', '=', '0']], [ false, 'user_role is empty' ] ],
            [ [['user_role', '=', 1]], [ false, 'user_role is incorrect' ] ],
            [ [['user_role', '=', -1]], [ false, 'user_role is incorrect' ] ],
            [ [['user_role', '=', 'Lorem ipsum dolor sit']], [ false, 'user_role is incorrect' ] ],

            [ [['id', '=', 2]], [ false, 'role not found' ] ],
        ];
    }


    /**
     * @dataProvider addPut
     */
    public function testPut( $data, $expected ) {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->role, 'id', 1 );
        $result = $this->role->put( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->role->error, $expected[1] );
    }

    public function addPut() {
        return [

            // correct cases
            [ ['user_role' => 'reader'], [true, ''] ],

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

            [ ['hub_id' => null], [false, 'hub_id is empty'] ],
            [ ['hub_id' => ''], [false, 'hub_id is empty'] ],
            [ ['hub_id' => ' '], [false, 'hub_id is empty'] ],
            [ ['hub_id' => 0], [false, 'hub_id is empty'] ],
            [ ['hub_id' => '0'], [false, 'hub_id is empty'] ],
            [ ['hub_id' => -1], [false, 'hub_id is incorrect'] ],
            [ ['hub_id' => '-1'], [false, 'hub_id is incorrect'] ],
            [ ['hub_id' => 'A'], [false, 'hub_id is incorrect'] ],

            [ ['user_id' => null], [false, 'user_id is empty'] ],
            [ ['user_id' => ''], [false, 'user_id is empty'] ],
            [ ['user_id' => ' '], [false, 'user_id is empty'] ],
            [ ['user_id' => 0], [false, 'user_id is empty'] ],
            [ ['user_id' => '0'], [false, 'user_id is empty'] ],
            [ ['user_id' => -1], [false, 'user_id is incorrect'] ],
            [ ['user_id' => '-1'], [false, 'user_id is incorrect'] ],
            [ ['user_id' => 'A'], [false, 'user_id is incorrect'] ],

            [ ['user_role' => null], [false, 'user_role is empty'] ],
            [ ['user_role' => ''], [false, 'user_role is empty'] ],
            [ ['user_role' => ' '], [false, 'user_role is empty'] ],
            [ ['user_role' => 0], [false, 'user_role is empty'] ],
            [ ['user_role' => '0'], [false, 'user_role is empty'] ],
            [ ['user_role' => 1], [false, 'user_role is incorrect'] ],
            [ ['user_role' => -1], [false, 'user_role is incorrect'] ],
            [ ['user_role' => 'Lorem ipsum dolor sit'], [false, 'user_role is incorrect'] ],
        ];
    }

    // Delete
    public function testDel() {
        $this->truncate();
        $this->insert();
        $this->setProperty( $this->role, 'id', 1 );
        $result = $this->role->del();
        $this->assertEquals( $result, true );
    }

}