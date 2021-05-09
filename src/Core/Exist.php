<?php
namespace App\Core;

class Exist extends \App\Core\Echidna
{
    protected $error;

    public function __construct( \PDO $pdo ) {
        parent::__construct( $pdo );
        $this->error = '';
    }

    public function has( string $table, array $args ) {
        return $this->is_exists( $table, $args );
    }

}
