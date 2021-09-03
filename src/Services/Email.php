<?php
namespace App\Services;

class Email
{
    const EMAIL_DEBUG = 2; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
    const EMAIL_HOST = 'echidna.io';
    const EMAIL_PORT = 587;
    const EMAIL_SECURE = 'tls';
    const EMAIL_AUTH = true;
    const EMAIL_USER = 'noreply@echidna.io';
    const EMAIL_PASS = 'GxTE4nU8YInsWJRM';
    const EMAIL_FROM = 'noreply@echidna.io';
    const EMAIL_NAME = 'Echidna';

    public static function send(string $user_email, string $user_name, string $email_subject, string $email_body) {

        $phpmailer = new \PHPMailer\PHPMailer\PHPMailer( true );
        $phpmailer->isSMTP();
        $phpmailer->SMTPDebug = self::EMAIL_DEBUG;
        $phpmailer->Host = self::EMAIL_HOST;
        $phpmailer->Port = self::EMAIL_PORT;
        $phpmailer->SMTPSecure = self::EMAIL_SECURE;
        $phpmailer->SMTPAuth = self::EMAIL_AUTH;
        $phpmailer->Username = self::EMAIL_USER;
        $phpmailer->Password = self::EMAIL_PASS;
        $phpmailer->isHTML(true);
        $phpmailer->setFrom(self::EMAIL_FROM, self::EMAIL_NAME);

        $phpmailer->addAddress($user_email, $user_name);
        $phpmailer->Subject = $email_subject;
        $phpmailer->Body = $email_body;
        $phpmailer->send();
    }

}
