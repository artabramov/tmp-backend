<?php
namespace App\Wrappers;
use \DateTime;

class Wrapper
{
    protected $em;
    protected $time;
    protected $timezone;
    protected $json;

    public function __construct($em) {
        $this->em = $em;
        $this->time = $this->time();
        $this->timezone = $this->timezone();
        $this->json = [];
    }

    public function __set($key, $value) {
        if(property_exists($this, $key)) {
            $this->$key = $value;
        }
    }

    public function __get($key) {
        return property_exists($this, $key) ? $this->$key : null;
    }

    public function __isset( $key ) {
        return property_exists($this, $key) ? !empty($this->$key) : false;
    }

    public function time() {
        $stmt = $this->em->getConnection()->prepare("SELECT NOW()::timestamp(0)");
        $stmt->execute();
        return new DateTime($stmt->fetchOne());
    }

    protected function timezone() {
        $stmt = $this->em->getConnection()->prepare("SELECT current_setting('TIMEZONE')");
        $stmt->execute();
        return $stmt->fetchOne();
    }

}
