<?php
use \PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/Core/Echidna.php';
require_once __DIR__.'/../src/Core/User.php';

class UserTest extends TestCase
{
    private $pdo;
    private $user;

    protected function setUp() : void {
        $this->pdo = require __DIR__ . "/../src/init/pdo.php";
        $this->user = new \App\Core\User( $this->pdo );
        $this->truncate();
    }

    protected function tearDown() : void {
        $this->truncate();
        //$this->pdo->query('KILL CONNECTION_ID()');
        $this->pdo = null;
        $this->user = null;
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
        $stmt = $this->pdo->query( "TRUNCATE TABLE users;" );
    }

    protected function insert() {
        $stmt = $this->pdo->query( "INSERT INTO users VALUES (1, '0001-01-01 00:00:00', '0001-01-01 00:00:00', 'pending', 'tokentoken', 'noreply.noreply@noreply.no', 'username', 'hashhash');" );
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $data, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->user->set( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->user->error, $expected[1] );
    }

    public function addSet() {
        return [

            // correct cases
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => ''], [true, ''] ],

            // incorrect cases
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'tokentoken', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => ''], [false, 'user_token is occupied'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply.noreply@noreply.no', 'user_name' => 'name', 'user_hash' => ''], [false, 'user_email is occupied'] ],

            [ ['create_date' => null, 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is empty'] ],
            [ ['create_date' => '', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is empty'] ],
            [ ['create_date' => ' ', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is empty'] ],
            [ ['create_date' => 0, 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is empty'] ],
            [ ['create_date' => '0', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is empty'] ],
            [ ['create_date' => 1, 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '1', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => -1, 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '-1', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => 'A001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'create_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => null, 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => ' ', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 0, 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 1, 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '1', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => -1, 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '-1', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 'A001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'update_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => null, 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => '', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => ' ', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 0, 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => '0', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 1, 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_status is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => -1, 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_status is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'Lorem ipsum dolor sit', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_status is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => null, 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_token is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => '', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_token is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => ' ', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_token is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 0, 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_token is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => '0', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_token is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 1, 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_token is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => -1, 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_token is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor in', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_token is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => null, 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => '', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => ' ', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 0, 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => '0', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 1, 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => -1, 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in', 'user_name' => 'name', 'user_hash' => 'hash'], [false, 'user_email is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => null, 'user_hash' => ''], [false, 'user_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => '', 'user_hash' => ''], [false, 'user_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => ' ', 'user_hash' => ''], [false, 'user_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 0, 'user_hash' => ''], [false, 'user_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => '0', 'user_hash' => ''], [false, 'user_name is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 1, 'user_hash' => ''], [false, 'user_name is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => -1, 'user_hash' => ''], [false, 'user_name is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut en', 'user_hash' => ''], [false, 'user_name is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 1], [false, 'user_hash is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => -1], [false, 'user_hash is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_name' => 'name', 'user_hash' => 'Lorem ipsum dolor sit amet, consectetur a'], [false, 'user_hash is incorrect'] ],
        ];
    }

    /**
     * @dataProvider addGet
     */
    public function testGet( $args, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->user->get( $args );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->user->error, $expected[1] );
    }

    public function addGet() {
        return [

            // correct cases
            [ [['id', '=', 1]], [ true, '' ] ],
            [ [['id', '=', '1']], [ true, '' ] ],
            [ [['create_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['update_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['user_status', '=', 'pending']], [ true, '' ] ],
            [ [['user_token', '=', 'tokentoken']], [ true, '' ] ],
            [ [['user_email', '=', 'noreply.noreply@noreply.no']], [ true, '' ] ],
            [ [['user_hash', '=', 'hashhash']], [ true, '' ] ],

            // incorrect cases
            [ [['id', '=', null]], [ false, 'user_id is empty' ] ],
            [ [['id', '=', '']], [ false, 'user_id is empty' ] ],
            [ [['id', '=', ' ' ]], [ false, 'user_id is empty' ] ],
            [ [['id', '=', 0]], [ false, 'user_id is empty' ] ],
            [ [['id', '=', '0']], [ false, 'user_id is empty' ] ],
            [ [['id', '=', -1]], [ false, 'user_id is incorrect' ] ],
            [ [['id', '=', '-1']], [ false, 'user_id is incorrect' ] ],
            [ [['id', '=', 'A']], [ false, 'user_id is incorrect' ] ],

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
            [ [['update_date', '=', 0]], [ false, 'update_date is empty' ] ],
            [ [['update_date', '=', '']], [ false, 'update_date is empty' ] ],
            [ [['update_date', '=', ' ']], [ false, 'update_date is empty' ] ],
            [ [['update_date', '=', 1]], [ false, 'update_date is incorrect' ] ],
            [ [['update_date', '=', '1']], [ false, 'update_date is incorrect' ] ],
            [ [['update_date', '=', -1]], [ false, 'update_date is incorrect' ] ],
            [ [['update_date', '=', '-1']], [ false, 'update_date is incorrect' ] ],
            [ [['update_date', '=', 'A001-01-01 00:00:00']], [ false, 'update_date is incorrect' ] ],

            [ [['user_status', '=', null]], [ false, 'user_status is empty' ] ],
            [ [['user_status', '=', '']], [ false, 'user_status is empty' ] ],
            [ [['user_status', '=', ' ']], [ false, 'user_status is empty' ] ],
            [ [['user_status', '=', 0]], [ false, 'user_status is empty' ] ],
            [ [['user_status', '=', '0']], [ false, 'user_status is empty' ] ],
            [ [['user_status', '=', 1]], [ false, 'user_status is incorrect' ] ],
            [ [['user_status', '=', -1]], [ false, 'user_status is incorrect' ] ],
            [ [['user_status', '=', 'Lorem ipsum dolor sit']], [ false, 'user_status is incorrect' ] ],

            [ [['user_token', '=', null]], [ false, 'user_token is empty' ] ],
            [ [['user_token', '=', '']], [ false, 'user_token is empty' ] ],
            [ [['user_token', '=', ' ']], [ false, 'user_token is empty' ] ],
            [ [['user_token', '=', 0]], [ false, 'user_token is empty' ] ],
            [ [['user_token', '=', '0']], [ false, 'user_token is empty' ] ],
            [ [['user_token', '=', 1]], [ false, 'user_token is incorrect' ] ],
            [ [['user_token', '=', -1]], [ false, 'user_token is incorrect' ] ],
            [ [['user_token', '=', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor in']], [ false, 'user_token is incorrect' ] ],

            [ [['user_email', '=', null]], [ false, 'user_email is empty' ] ],
            [ [['user_email', '=', '']], [ false, 'user_email is empty' ] ],
            [ [['user_email', '=', ' ']], [ false, 'user_email is empty' ] ],
            [ [['user_email', '=', 0]], [ false, 'user_email is empty' ] ],
            [ [['user_email', '=', '0']], [ false, 'user_email is empty' ] ],
            [ [['user_email', '=', 1]], [ false, 'user_email is incorrect' ] ],
            [ [['user_email', '=', -1]], [ false, 'user_email is incorrect' ] ],
            [ [['user_email', '=', 'noreply@noreply']], [ false, 'user_email is incorrect' ] ],
            [ [['user_email', '=', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in']], [ false, 'user_email is incorrect' ] ],

            [ [['user_name', '=', null]], [ false, 'user_name is empty' ] ],
            [ [['user_name', '=', '']], [ false, 'user_name is empty' ] ],
            [ [['user_name', '=', ' ']], [ false, 'user_name is empty' ] ],
            [ [['user_name', '=', 0]], [ false, 'user_name is empty' ] ],
            [ [['user_name', '=', '0']], [ false, 'user_name is empty' ] ],
            [ [['user_name', '=', 1]], [ false, 'user_name is incorrect' ] ],
            [ [['user_name', '=', -1]], [ false, 'user_name is incorrect' ] ],
            [ [['user_name', '=', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut en']], [ false, 'user_name is incorrect' ] ],

            [ [['user_hash', '=', null]], [ false, 'user_hash is empty' ] ],
            [ [['user_hash', '=', '']], [ false, 'user_hash is empty' ] ],
            [ [['user_hash', '=', ' ']], [ false, 'user_hash is empty' ] ],
            [ [['user_hash', '=', 0]], [ false, 'user_hash is empty' ] ],
            [ [['user_hash', '=', '0']], [ false, 'user_hash is empty' ] ],
            [ [['user_hash', '=', 1]], [ false, 'user_hash is incorrect' ] ],
            [ [['user_hash', '=', -1]], [ false, 'user_hash is incorrect' ] ],
            [ [['user_hash', '=', 'Lorem ipsum dolor sit amet, consectetur a']], [ false, 'user_hash is incorrect' ] ],

        ];
    }


    /**
     * @dataProvider addPut
     */
    public function testPut( $data, $expected ) {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->user, 'id', 1 );
        $result = $this->user->put( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->user->error, $expected[1] );
    }

    public function addPut() {
        return [

            // correct cases
            [ ['user_hash' => 'hash'], [true, '']],

            // incorrect cases
            [ ['create_date' => null], [false, 'create_date is empty'] ],
            [ ['create_date' => ''], [false, 'create_date is empty'] ],
            [ ['create_date' => ' '], [false, 'create_date is empty'] ],
            [ ['create_date' => 0], [false, 'create_date is empty'] ],
            [ ['create_date' => '0'], [false, 'create_date is empty'] ],
            [ ['create_date' => 1], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '1'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => -1], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '-1'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => 'A000-00-00 00:00:00'], [false, 'create_date is incorrect'] ],

            [ ['update_date' => null], [false, 'update_date is empty'] ],
            [ ['update_date' => ''], [false, 'update_date is empty'] ],
            [ ['update_date' => ' '], [false, 'update_date is empty'] ],
            [ ['update_date' => 0], [false, 'update_date is empty'] ],
            [ ['update_date' => '0'], [false, 'update_date is empty'] ],
            [ ['update_date' => 1], [false, 'update_date is incorrect'] ],
            [ ['update_date' => '1'], [false, 'update_date is incorrect'] ],
            [ ['update_date' => -1], [false, 'update_date is incorrect'] ],
            [ ['update_date' => '-1'], [false, 'update_date is incorrect'] ],
            [ ['update_date' => 'A000-00-00 00:00:00'], [false, 'update_date is incorrect'] ],

            [ ['user_status' => null], [false, 'user_status is empty'] ],
            [ ['user_status' => ''], [false, 'user_status is empty'] ],
            [ ['user_status' => ' '], [false, 'user_status is empty'] ],
            [ ['user_status' => 0], [false, 'user_status is empty'] ],
            [ ['user_status' => '0'], [false, 'user_status is empty'] ],
            [ ['user_status' => 1], [false, 'user_status is incorrect'] ],
            [ ['user_status' => -1], [false, 'user_status is incorrect'] ],
            [ ['user_status' => 'Lorem ipsum dolor sit'], [false, 'user_status is incorrect'] ],

            [ ['user_email' => null], [ false, 'user_email is empty' ] ],
            [ ['user_email' => ''], [ false, 'user_email is empty' ] ],
            [ ['user_email' => ' '], [ false, 'user_email is empty' ] ],
            [ ['user_email' => 0], [ false, 'user_email is empty' ] ],
            [ ['user_email' => '0'], [ false, 'user_email is empty' ] ],
            [ ['user_email' => 1], [ false, 'user_email is incorrect' ] ],
            [ ['user_email' => -1], [ false, 'user_email is incorrect' ] ],
            [ ['user_email' => 'noreply@noreply'], [ false, 'user_email is incorrect' ] ],
            [ ['user_email' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in'], [ false, 'user_email is incorrect' ] ],

            [ ['user_name' => null], [false, 'user_name is empty'] ],
            [ ['user_name' => ''], [false, 'user_name is empty'] ],
            [ ['user_name' => ' '], [false, 'user_name is empty'] ],
            [ ['user_name' => 0], [false, 'user_name is empty'] ],
            [ ['user_name' => '0'], [false, 'user_name is empty'] ],
            [ ['user_name' => 1], [false, 'user_name is incorrect'] ],
            [ ['user_name' => -1], [false, 'user_name is incorrect'] ],
            [ ['user_name' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut en'], [false, 'user_name is incorrect'] ],

            //[ ['user_hash' => null], [ false, 'user_hash is empty' ] ],
            //[ ['user_hash' => ''], [ false, 'user_hash is empty' ] ],
            //[ ['user_hash' => ' '], [ false, 'user_hash is empty' ] ],
            //[ ['user_hash' => 0], [ false, 'user_hash is empty' ] ],
            //[ ['user_hash' => '0'], [ false, 'user_hash is empty' ] ],
            [ ['user_hash' => 1], [ false, 'user_hash is incorrect' ] ],
            [ ['user_hash' => -1], [ false, 'user_hash is incorrect' ] ],
            [ ['user_hash' => 'Lorem ipsum dolor sit amet, consectetur a'], [ false, 'user_hash is incorrect' ] ],
        ];
    }

    // Delete
    public function testDel() {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->user, 'id', 1 );
        $result = $this->user->del();
        $this->assertEquals( $result, true );
    }

}
