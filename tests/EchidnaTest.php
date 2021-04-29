<?php
use \PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/Core/Echidna.php';
require_once __DIR__.'/../src/Core/Meta.php';

class EchidnaTest extends TestCase
{
    private $pdo;
    private $echidna;

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

    protected function setUp() : void {
        $this->pdo = require __DIR__ . "/../src/init/pdo.php";
        $this->echidna = new \App\Core\Echidna( $this->pdo );

        $this->pdo->query('
            CREATE TABLE IF NOT EXISTS project.tests (
                id         BIGINT(20)   NOT NULL AUTO_INCREMENT,
                test_key   VARCHAR(20)  NOT NULL,
                test_value VARCHAR(255) NOT NULL,
            
                PRIMARY KEY id         (id),
                        KEY test_key   (test_key),
                        KEY test_value (test_value)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');

    }

    protected function tearDown() : void {
        $this->pdo->query('DROP TABLE tests;');
        $this->pdo = null;
        $this->echidna = null;
    }

    protected function truncate() {
        $stmt = $this->pdo->query( "TRUNCATE TABLE tests;" );
    }

    protected function insert() {
        $stmt = $this->pdo->query( "INSERT INTO tests (id, test_key, test_value) VALUES (1, 'key', 'value');" );
    }

    /**
     * @dataProvider addInsert
     */
    public function testInsert( $table, $data, $expected ) {
        $this->truncate();
        $result = $this->callMethod( $this->echidna, 'insert', [ $table, $data ] );
        $this->assertEquals( $expected, $result );
    }

    public function addInsert() {

        return [ 

            // correct cases
            [ 'tests', [ 'test_key' => 'key', 'test_value' => 'value' ], 1 ],
            [ 'tests', [ 'test_key' => 'sadfsadfsafd', 'test_value' => 'sadfsadf' ], 1 ],

            // incorrect cases
            [ 'tests', [ '_test_key' => 'key 3', 'test_value' => 'value 3' ], 0 ],

        ];
    }

    /**
     * @dataProvider addUpdate
     */

    public function testUpdate( $table, $args, $data, $expected ) {
        $this->truncate();
        $this->insert();
        $result = $this->callMethod( $this->echidna, 'update', [ $table, $args, $data ] );
        $this->assertEquals( $expected, $result );
    }

    public function addUpdate() {

        return [ 

            // correct cases
            [ 'tests', [[ 'id', '=', 1 ]], [ 'test_key' => 'key 2' ], true ],
            [ 'tests', [[ 'id', '=', 1 ]], [ 'test_value' => 'value 2' ], true ],


            // incorrect cases
            [ 'tests', [[ 'id', '=', 1 ]], [ '_test_key' => 'key 2' ], false ],

        ];
    }

    /**
     * @dataProvider addSelect
     */
    public function testSelect( $fields, $table, $args, $limit, $offset, $expected ) {
        $this->truncate();
        $this->insert();
        $tmp = $this->callMethod( $this->echidna, 'select', [ $fields, $table, $args, $limit, $offset ] );
        $result = is_array( $tmp ) ? count( $tmp ) : $tmp;
        $this->assertEquals( $expected, $result );
    }

    public function addSelect() {

        return [ 

            // correct cases
            [ '*', 'tests', [ ['id', '=', 1], ], 10, 0, 1 ],
            [ '*', 'tests', [ ['id', '=', 2], ], 10, 0, 0 ],

            // incorrect cases
            [ '*', '_tests', [ ['id', '=', 2], ], 10, 0, 0 ],

        ];
    }

    /**
     * @dataProvider addDelete
     */
    public function testDelete( $table, $args, $expected ) {
        $this->truncate();
        $this->insert();
        $result = $this->callMethod( $this->echidna, 'delete', [ $table, $args ] );
        $this->assertEquals( $expected, $result );

    }

    public function addDelete() {

        return [ 

            // correct cases
            [ 'tests', [[ 'id', '=', 1 ]], true ],

            // incorrect cases

        ];

    }

    /**
     * @dataProvider addCount
     */
    public function testCount( $table, $args, $expected ) {
        $this->truncate();
        $this->insert();
        $result = $this->callMethod( $this->echidna, 'count', [ $table, $args ] );
        $this->assertEquals( $expected, $result );
    }

    public function addCount() {
        return [

            [ 'tests', [['id', '=', 1]], 1 ],
            [ 'tests', [['id', '=', 2]], 0 ],

        ];
    }

    /**
     * @dataProvider addExists
     */
    public function testExists( $table, $args, $expected ) {
        $this->truncate();
        $this->insert();
        $result = $this->callMethod( $this->echidna, 'exists', [ $table, $args ] );
        $this->assertEquals( $expected, $result );
    }

    public function addExists() {
        return [

            [ 'tests', [['id', '=', 1]], true ],
            [ 'tests', [['test_key', '=', 'key']], true ],
            [ 'tests', [['test_value', '=', 'value']], true ],
            
            [ 'tests', [['id', '=', 2]], false ],
            [ 'tests', [['test_key', '=', 'key 2']], false ],
            [ 'tests', [['test_value', '=', 'value 2']], false ],

        ];
    }

}