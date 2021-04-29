<?php

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;

$phpmailer = new PHPMailer( true );
$phpmailer->isSMTP(); 
$phpmailer->SMTPDebug  = 2; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
$phpmailer->Host       = 'echidna.io';
$phpmailer->Port       = 587;
$phpmailer->SMTPSecure = 'tls';
$phpmailer->SMTPAuth   = true;
$phpmailer->Username   = 'noreply@echidna.io';
$phpmailer->Password   = 'GxTE4nU8YInsWJRM';
$phpmailer->isHTML( true );
$phpmailer->setFrom( 'noreply@echidna.io', 'Echidna' );

return $phpmailer;
