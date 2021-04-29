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
        $stmt = $this->pdo->query( "TRUNCATE TABLE users;" );
    }

    protected function insert() {
        $stmt = $this->pdo->query( "INSERT INTO users VALUES (1, '0001-01-01 00:00:00', '0001-01-01 00:00:00', '0001-01-01 00:00:00', '0001-01-01 00:00:00', 'pending', 'token', 'noreply@noreply.no', '');" );
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $data, $expected ) {

        $this->truncate();
        $result = $this->user->set( $data );
        $this->assertEquals( $result, $expected );
    }

    public function addSet() {
        return [

            // correct cases
            [ ['register_date' => '0001-01-01 00:00:00', 'restore_date' => '0001-01-01 00:00:00', 'signin_date' => '0001-01-01 00:00:00', 'auth_date' => '0001-01-01 00:00:00', 'user_status' => 'pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_hash' => 'hash'], true ],

            // incorrect cases
            [ ['register_date' => '0001-01-01 00:00:00', 'restore_date' => '0001-01-01 00:00:00', 'signin_date' => '0001-01-01 00:00:00', 'auth_date' => '0001-01-01 00:00:00', 'user_status' => '_pending', 'user_token' => 'token', 'user_email' => 'noreply@noreply.no', 'user_hash' => 'hash'], false ],

        ];
    }

    /**
     * @dataProvider addGet
     */
    public function testGet( $args, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->user->get( $args );
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
        $this->setProperty( $this->user, 'id', 1 );
        $result = $this->user->put( $data );
        $this->assertEquals( $result, $expected );

    }

    public function addPut() {
        return [

            // correct cases
            [ ['user_hash' => 'hash'], true ],

            // incorrect cases
            [ ['user_hash' => 'hashhashhashhashhashhashhashhashhashhashh'], false ],

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