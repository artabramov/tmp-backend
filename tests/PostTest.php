<?php
use \PHPUnit\Framework\TestCase;

require_once __DIR__.'/../src/Core/Echidna.php';
require_once __DIR__.'/../src/Core/Post.php';

class PostTest extends TestCase
{
    private $pdo;
    private $post;

    protected function setUp() : void {
        $this->pdo = require __DIR__ . "/../src/init/pdo.php";
        $this->post = new \App\Core\Post( $this->pdo );
        $this->truncate();
    }

    protected function tearDown() : void {
        $this->truncate();
        $this->pdo = null;
        $this->post = null;
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
        $stmt = $this->pdo->query( "TRUNCATE TABLE posts;" );
    }

    protected function insert() {
        $stmt = $this->pdo->query( "INSERT INTO posts VALUES (1, '0001-01-01 00:00:00', '0001-01-01 00:00:00', 0, 1, 1, 'post type', 'post status', 'post content');" );
    }

    /**
     * @dataProvider addSet
     */
    public function testSet( $data, $expected ) {

        $this->truncate();
        $result = $this->post->set( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->post->error, $expected[1] );
    }

    public function addSet() {
        return [

            // correct cases
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [true, ''] ],

            // incorrect cases
            [ ['create_date' => null, 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is empty'] ],
            [ ['create_date' => '', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is empty'] ],
            [ ['create_date' => ' ', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is empty'] ],
            [ ['create_date' => 0, 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is empty'] ],
            [ ['create_date' => '0', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is empty'] ],
            [ ['create_date' => 1, 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '1', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => -1, 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => '-1', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is incorrect'] ],
            [ ['create_date' => 'A001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'create_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => null, 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => ' ', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 0, 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 1, 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '1', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => -1, 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '-1', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => 'A001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'update_date is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => -1, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'parent_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => '-1', 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'parent_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 'A', 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'parent_id is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => null, 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => ' ', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => 0, 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '0', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'user_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => -1, 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'user_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '-1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'user_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => 'A', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'user_id is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => null, 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => ' ', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => 0, 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '0', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'hub_id is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => -1, 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'hub_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '-1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'hub_id is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => 'A', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'hub_id is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => null, 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'post_type is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => '', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'post_type is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => ' ', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'post_type is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 0, 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'post_type is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => '0', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'post_type is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 1, 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'post_type is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => -1, 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'post_type is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'Lorem ipsum dolor sit', 'post_status' => 'draft', 'post_content' => 'post content'], [false, 'post_type is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => null, 'post_content' => 'post content'], [false, 'post_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => '', 'post_content' => 'post content'], [false, 'post_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => ' ', 'post_content' => 'post content'], [false, 'post_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 0, 'post_content' => 'post content'], [false, 'post_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => '0', 'post_content' => 'post content'], [false, 'post_status is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 1, 'post_content' => 'post content'], [false, 'post_status is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => -1, 'post_content' => 'post content'], [false, 'post_status is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'Lorem ipsum dolor sit', 'post_content' => 'post content'], [false, 'post_status is incorrect'] ],

            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => null], [false, 'post_content is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => ''], [false, 'post_content is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => ' '], [false, 'post_content is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 0], [false, 'post_content is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => '0'], [false, 'post_content is empty'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => 1], [false, 'post_content is incorrect'] ],
            [ ['create_date' => '0001-01-01 00:00:00', 'update_date' => '0001-01-01 00:00:00', 'parent_id' => 0, 'user_id' => '1', 'hub_id' => '1', 'post_type' => 'document', 'post_status' => 'draft', 'post_content' => -1], [false, 'post_content is incorrect'] ],
        ];
    }

    /**
     * @dataProvider addGet
     */
    public function testGet( $args, $expected ) {

        $this->truncate();
        $this->insert();
        $result = $this->post->get( $args );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->post->error, $expected[1] );
    }

    public function addGet() {
        return [

            // correct cases
            [ [['id', '=', 1]], [ true, '' ] ],
            [ [['id', '=', '1']], [ true, '' ] ],
            [ [['create_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['update_date', '=', '0001-01-01 00:00:00']], [ true, '' ] ],
            [ [['parent_id', '=', 0]], [ true, '' ] ],
            [ [['parent_id', '=', '0']], [ true, '' ] ],
            [ [['hub_id', '=', 1]], [ true, '' ] ],
            [ [['hub_id', '=', '1']], [ true, '' ] ],
            [ [['user_id', '=', 1]], [ true, '' ] ],
            [ [['user_id', '=', '1']], [ true, '' ] ],
            [ [['post_type', '=', 'post type']], [ true, '' ] ],
            [ [['post_status', '=', 'post status']], [ true, '' ] ],

            // incorrect cases
            [ [['id', '=', null]], [ false, 'post_id is empty' ] ],
            [ [['id', '=', '']], [ false, 'post_id is empty' ] ],
            [ [['id', '=', ' ' ]], [ false, 'post_id is empty' ] ],
            [ [['id', '=', 0]], [ false, 'post_id is empty' ] ],
            [ [['id', '=', '0']], [ false, 'post_id is empty' ] ],
            [ [['id', '=', -1]], [ false, 'post_id is incorrect' ] ],
            [ [['id', '=', '-1']], [ false, 'post_id is incorrect' ] ],
            [ [['id', '=', 'A']], [ false, 'post_id is incorrect' ] ],

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

            [ [['parent_id', '=', -1]], [ false, 'parent_id is incorrect' ] ],
            [ [['parent_id', '=', '-1']], [ false, 'parent_id is incorrect' ] ],
            [ [['parent_id', '=', 'A']], [ false, 'parent_id is incorrect' ] ],

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

            [ [['post_type', '=', null]], [ false, 'post_type is empty' ] ],
            [ [['post_type', '=', '']], [ false, 'post_type is empty' ] ],
            [ [['post_type', '=', ' ']], [ false, 'post_type is empty' ] ],
            [ [['post_type', '=', 0]], [ false, 'post_type is empty' ] ],
            [ [['post_type', '=', '0']], [ false, 'post_type is empty' ] ],
            [ [['post_type', '=', 1]], [ false, 'post_type is incorrect' ] ],
            [ [['post_type', '=', -1]], [ false, 'post_type is incorrect' ] ],
            [ [['post_type', '=', 'Lorem ipsum dolor sit']], [ false, 'post_type is incorrect' ] ],

            [ [['post_status', '=', null]], [ false, 'post_status is empty' ] ],
            [ [['post_status', '=', '']], [ false, 'post_status is empty' ] ],
            [ [['post_status', '=', ' ']], [ false, 'post_status is empty' ] ],
            [ [['post_status', '=', 0]], [ false, 'post_status is empty' ] ],
            [ [['post_status', '=', '0']], [ false, 'post_status is empty' ] ],
            [ [['post_status', '=', 1]], [ false, 'post_status is incorrect' ] ],
            [ [['post_status', '=', -1]], [ false, 'post_status is incorrect' ] ],
            [ [['post_status', '=', 'Lorem ipsum dolor sit']], [ false, 'post_status is incorrect' ] ],

        ];
    }

    /**
     * @dataProvider addPut
     */
    public function testPut( $data, $expected ) {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->post, 'id', 1 );
        $result = $this->post->put( $data );
        $this->assertEquals( $result, $expected[0] );
        $this->assertEquals( $this->post->error, $expected[1] );
    }

    public function addPut() {
        return [

            // correct cases
            [ ['post_status' => 'trash'], [true, ''] ],

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

            [ ['parent_id' => -1], [ false, 'parent_id is incorrect' ] ],
            [ ['parent_id' => '-1'], [ false, 'parent_id is incorrect' ] ],
            [ ['parent_id' => 'A'], [ false, 'parent_id is incorrect' ] ],

            [ ['hub_id' => null], [ false, 'hub_id is empty' ] ],
            [ ['hub_id' => ''], [ false, 'hub_id is empty' ] ],
            [ ['hub_id' => ' '], [ false, 'hub_id is empty' ] ],
            [ ['hub_id' => '0'], [ false, 'hub_id is empty' ] ],
            [ ['hub_id' => 0], [ false, 'hub_id is empty' ] ],
            [ ['hub_id' => -1], [ false, 'hub_id is incorrect' ] ],
            [ ['hub_id' => '-1'], [ false, 'hub_id is incorrect' ] ],
            [ ['hub_id' => 'A'], [ false, 'hub_id is incorrect' ] ],

            [ ['user_id' => null], [ false, 'user_id is empty' ] ],
            [ ['user_id' => ''], [ false, 'user_id is empty' ] ],
            [ ['user_id' => ' '], [ false, 'user_id is empty' ] ],
            [ ['user_id' => '0'], [ false, 'user_id is empty' ] ],
            [ ['user_id' => 0], [ false, 'user_id is empty' ] ],
            [ ['user_id' => -1], [ false, 'user_id is incorrect' ] ],
            [ ['user_id' => '-1'], [ false, 'user_id is incorrect' ] ],
            [ ['user_id' => 'A'], [ false, 'user_id is incorrect' ] ],

            [ ['post_type' => null], [ false, 'post_type is empty' ] ],
            [ ['post_type' => ''], [ false, 'post_type is empty' ] ],
            [ ['post_type' => ' '], [ false, 'post_type is empty' ] ],
            [ ['post_type' => 0], [ false, 'post_type is empty' ] ],
            [ ['post_type' => '0'], [ false, 'post_type is empty' ] ],
            [ ['post_type' => 1], [ false, 'post_type is incorrect' ] ],
            [ ['post_type' => -1], [ false, 'post_type is incorrect' ] ],
            [ ['post_type' => 'Lorem ipsum dolor sit'], [ false, 'post_type is incorrect' ] ],

            [ ['post_status' => null], [ false, 'post_status is empty' ] ],
            [ ['post_status' => ''], [ false, 'post_status is empty' ] ],
            [ ['post_status' => ' '], [ false, 'post_status is empty' ] ],
            [ ['post_status' => 0], [ false, 'post_status is empty' ] ],
            [ ['post_status' => '0'], [ false, 'post_status is empty' ] ],
            [ ['post_status' => 1], [ false, 'post_status is incorrect' ] ],
            [ ['post_status' => -1], [ false, 'post_status is incorrect' ] ],
            [ ['post_status' => 'Lorem ipsum dolor sit'], [ false, 'post_status is incorrect' ] ],
        ];
    }

    // Delete
    public function testDel() {

        $this->truncate();
        $this->insert();
        $this->setProperty( $this->post, 'id', 1 );
        $result = $this->post->del();
        $this->assertEquals( $result, true );
    }


}