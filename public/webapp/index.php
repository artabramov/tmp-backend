<?php
$uri = sprintf("%s://%s", isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $_SERVER['SERVER_NAME'] );
$title = 'Echidna Docs';
?>

<!-- header -->
<?php require_once(__DIR__ . '/header.php'); ?>

<!-- navbar -->
<?php require_once(__DIR__ . '/navbar.php'); ?>

<!-- modals -->
<?php require_once(__DIR__ . '/modals/register.php'); ?>
<?php require_once(__DIR__ . '/modals/registered.php'); ?>
<?php require_once(__DIR__ . '/modals/restore.php'); ?>
<?php require_once(__DIR__ . '/modals/restored.php'); ?>
<?php require_once(__DIR__ . '/modals/signin.php'); ?>

<!-- events -->
<?php require_once(__DIR__ . '/events/user_register.php'); ?>
<?php require_once(__DIR__ . '/events/user_restore.php'); ?>
<?php require_once(__DIR__ . '/events/user_signin.php'); ?>
<?php require_once(__DIR__ . '/events/user_auth.php'); ?>
<?php require_once(__DIR__ . '/events/user_signout.php'); ?>

<!-- body -->
<?php 
if( file_exists( __DIR__ . '/pages/' . $page . '.php' )) {
    require_once( __DIR__ . '/pages/' . $page . '.php' );
} else {
    require_once( __DIR__ . '/pages/default.php' );
}
?>

<!-- footer -->
<?php require_once(__DIR__ . '/footer.php'); ?>
