<?php
namespace App\Services;
use \App\Exceptions\AppException;

class Halt
{
    const ERRORS = [
        // app
        1000 => 'app error',
        1001 => 'wait a bit',

        // user
        1100 => 'user error',
        1101 => 'user not found',
        1102 => 'user limit exceeded',
        1103 => 'create_date is empty',
        1104 => 'create_date is incorrect',
        1105 => 'update_date is empty',
        1106 => 'update_date is incorrect',
        1107 => 'remind_date is empty',
        1108 => 'remind_date is incorrect',
        1109 => 'user_status is empty',
        1110 => 'user_status is incorrect',
        1111 => 'user_status is trash',
        1112 => 'user_token is empty',
        1113 => 'user_token is incorrect',
        1114 => 'user_token is occupied',
        1115 => 'user_hash is incorrect',
        1116 => 'user_hash expired',
        1117 => 'user_email is empty',
        1118 => 'user_email is incorrect',
        1119 => 'user_email is occupied',
        1120 => 'user_name is empty',
        1121 => 'user_name is incorrect',
        1122 => 'user_timezone is empty',
        1123 => 'user_timezone is incorrect',
    ];

    public static function throw(int $code) {
        $message = array_key_exists($code, self::ERRORS) ? self::ERRORS[$code] : self::ERRORS[1000];
        throw new AppException($message, $code);
    }

}
