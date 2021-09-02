<?php
namespace App\Services;
use \App\Exceptions\AppException;

class Time
{
    protected $em;
    protected $offset; // time offset between PHP time and DB time (in seconds)
    protected $timezone;

    public function __construct($em) {
        $this->em = $em;
        $this->offset = 0;
        $this->timezone = $this->get_timezone();
    }

    public function __set($key, $value) {
        if($key == 'timezone') {
            $this->set_timezone($value);
        }

        if(property_exists($this, $key)) {
            $this->$key = $value;
        }
    }

    public function __get($key) {
        if($key == 'datetime') {
            return $this->get_datetime();

        } else {
            return property_exists($this, $key) ? $this->$key : null;
        }
    }

    private function get_timezone() {
        $stmt = $this->em->getConnection()->prepare("SELECT current_setting('TIMEZONE')");
        $stmt->execute();
        return $stmt->fetchOne();
    }

    private function set_timezone(string $timezone) {
        $stmt = $this->em->getConnection()->prepare("SET TIME ZONE '" . $timezone . "'");
        $stmt->execute();
    }

    private function get_offset() {

        // Database timestamp
        $stmt = $this->em->getConnection()->prepare("SELECT NOW()::timestamp(0)");
        $stmt->execute();
        $datetime = new \DateTime($stmt->fetchOne());
        $db_timestamp = $datetime->getTimestamp();

        // PHP timestamp
        $datetime = new \DateTime('now');
        $php_timestamp = $datetime->getTimestamp();

        // Time offset
        return $db_timestamp - $php_timestamp;
    }

    private function get_datetime() {
        $datetime = new \DateTime('now');
        $timestamp = $datetime->getTimestamp() + $this->offset;

        $datetime = new \DateTime();
        $datetime->setTimestamp($timestamp);
        $datetime->setTimezone(new \DateTimeZone($this->timezone));
        return $datetime;
    }
}
